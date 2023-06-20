<?php

declare(strict_types=1);

namespace App\Models;

class ProductPurchase
{
  /**
   * Add new ProductPurchase.
   */
  public static function add(array $data, array $items)
  {
    $data['reference'] = OrderRef::getReference('purchase');
    $data = setCreatedBy($data);

    $data['status'] = 'need_approval'; // Default status.
    $data['payment_status'] = 'pending'; // Default payment status.

    if (!isset($data['biller_id'])) {
      setLastError('Biller id is not set.');
      return false;
    }

    if (isset($data['warehouse_id'])) {
      $wh = Warehouse::getRow(['id' => $data['warehouse_id']]);

      $data['warehouse_code'] = $wh->code;
      $data['warehouse_name'] = $wh->name;
    } else {
      setLastError('Warehouse id is not set.');
      return false;
    }

    if (isset($data['supplier_id'])) {
      $supplier = Supplier::getRow(['id' => $data['supplier_id']]);

      $data['supplier_name'] = $supplier->name;
    }

    $data['grand_total'] = 0;

    foreach ($items as $item) {
      $data['grand_total'] += floatval($item['cost'] * $item['purchased_qty']);
    }

    DB::table('purchases')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('purchase');

      foreach ($items as $item) {
        $res = Stock::add([
          'date'          => $data['created_at'],
          'purchase_id'   => $insertId,
          'product_id'    => $item['id'],
          'cost'          => $item['cost'],
          'purchased_qty' => $item['purchased_qty'],
          'quantity'      => 0, // On new add purchase, quantity must be zero.
          'warehouse_id'  => $data['warehouse_id'],
          'status'        => 'need_approval',
          'spec'          => ($item['spec'] ?? null)
        ]);

        if (!$res) {
          return false;
        }
      }

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  public static function addBySupplierId(int $supplierId)
  {
    $supplier = Supplier::getRow(['id' => $supplierId]);

    if (!$supplier) {
      setLastError('Supplier is not found.');
      return false;
    }

    $supplierItems = Product::select('*')->where('supplier_id', $supplierId)->get();

    $date = date('Y-m-d H:i:s');
    $purchaseItems = [];
    $purchaseQty = 0;

    $billerLuc = Biller::getRow(['code' => 'LUC']);
    $warehouseLuc = Warehouse::getRow(['code' => 'LUC']);

    foreach ($supplierItems as $item) {
      if ($item->safety_stock <= 0 || !$item->safety_stock) {
        continue;
      }

      $whp = WarehouseProduct::getRow(['product_id' => $item->id, 'warehouse_id' => $warehouseLuc->id]);

      if (!$whp) {
        continue;
      }

      $purchaseQty = getOrderStock((float)$whp->quantity, (float)$item->min_order_qty, (float)$item->safety_stock);

      if ($purchaseQty <= 0) {
        continue;
      }

      $purchaseItems[] = [
        'code'          => $item->code, // Debugging purpose only. Ignored by addStockPurchase.
        'date'          => $date,
        'id'            => $item->id,
        'biller_id'     => $billerLuc->id,
        'cost'          => $item->cost,
        'purchased_qty' => $purchaseQty,
        'warehouse_id'  => $warehouseLuc->id,
        'unit_id'       => $item->purchase_unit,
        'spec'          => null
      ];
    }

    $data = [
      'date'            => $date,
      'biller_id'       => $billerLuc->id,
      'warehouse_id'    => $warehouseLuc->id,
      'payment_term'    => $supplier->payment_term,
      'supplier_id'     => $supplierId,
    ];

    if (self::add($data, $purchaseItems)) {
      return true;
    }

    return false;
  }

  /**
   * Delete ProductPurchase.
   */
  public static function delete(array $where)
  {
    $purchases = self::get($where);
    $deleted = 0;

    foreach ($purchases as $purchase) {
      DB::table('purchases')->delete(['id' => $purchase->id]);

      if (DB::affectedRows()) {
        $items = Stock::get(['purchase_id' => $purchase->id]);

        Stock::delete(['purchase_id' => $purchase->id]);

        foreach ($items as $item) {
          Product::sync(['id' => $item->product_id]);
        }

        Attachment::delete(['hashname' => $purchase->attachment]);

        $deleted++;
      }
    }

    return $deleted;
  }

  /**
   * Get ProductPurchase collections.
   */
  public static function get($where = [])
  {
    return DB::table('purchases')->get($where);
  }

  /**
   * Get ProductPurchase row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  public static function sync($where = [])
  {
    $purchases = self::get($where);
    $hasPayment = false;
    $synced = 0;

    foreach ($purchases as $purchase) {
      $paid = 0;
      $payments = Payment::get(['purchase_id' => $purchase->id]);
      $status = $purchase->payment_status;

      foreach ($payments as $payment) {
        if ($payment->status == 'paid') {
          $paid += $payment->amount;
        }

        $hasPayment = true;
      }

      if ($purchase->grand_total == $paid) {
        $status = 'paid';
      } else if ($paid > 0 && $purchase->grand_total > $paid) {
        $status = 'partial';
      } else if (!$hasPayment) {
        $status = 'pending';
      }

      if (!self::update((int)$purchase->id, ['payment_status' => $status])) {
        return false;
      }

      $synced++;
    }

    return $synced;
  }

  /**
   * Select ProductPurchase.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('purchases')->select($columns, $escape);
  }

  /**
   * Update ProductPurchase.
   */
  public static function update(int $id, array $data, array $items = [])
  {
    $purchase = self::getRow(['id' => $id]);

    DB::table('purchases')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      if ($items) {
        $newPurchase = self::getRow(['id' => $id]);

        Stock::delete(['purchase_id' => $id]);

        foreach ($items as $item) {
          $res = Stock::add([
            'date'          => $purchase->date,
            'purchase_id'   => $id,
            'product_id'    => $item['id'],
            'cost'          => $item['cost'],
            'purchased_qty' => $item['purchased_qty'],
            'quantity'      => $item['quantity'],
            'warehouse_id'  => $newPurchase->warehouse_id,
            'status'        => ($item['quantity'] > 0 ? 'received' : $newPurchase->status),
            'spec'          => ($item['spec'] ?? null)
          ]);

          if (!$res) {
            return false;
          }
        }
      }

      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
