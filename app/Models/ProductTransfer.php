<?php

declare(strict_types=1);

namespace App\Models;

class ProductTransfer
{
  /**
   * Add new ProductTransfer.
   */
  public static function add(array $data, array $items)
  {
    $data['reference'] = OrderRef::getReference('transfer');
    $data = setCreatedBy($data);

    $data['status'] = 'packing'; // Default status for new transfer
    $data['payment_status'] = 'pending'; // Default payment status for new transfer

    if ($items) {
      $data['items'] = '';
      $data['grand_total'] = 0;

      foreach ($items as $item) {
        $product = Product::getRow(['id' => $item['id']]);

        if ($product) {
          $data['items'] .= "- ({$product->code}) " . getExcerpt($product->name) . '<br>';

          $data['grand_total'] += $item['markon_price'] * $item['quantity'];
        }
      }
    }

    DB::table('product_transfer')->insert($data);

    if (DB::affectedRows()) {
      $insertId = DB::insertID();

      OrderRef::updateReference('transfer');

      if ($items) {
        foreach ($items as $item) {
          $product = Product::getRow(['id' => $item['id']]);

          $item['transfer_id']  = $insertId;
          $item['product_id']   = $product->id;
          $item['product_code'] = $product->code;
          $item['status']       = 'packing';
          unset($item['id']);

          ProductTransferItem::add($item);
        }
      }

      return $insertId;
    }

    return false;
  }

  public static function addByWarehouseId($warehouseId)
  {
    $whFrom = Warehouse::getRow(['code' => 'LUC']); // Default warehouse from.
    $whTo   = Warehouse::getRow(['id' => $warehouseId]);

    $settingsJSON = getJSON(Setting::getRow(['setting_id' => 1])->settings_json);
    // Return [start_date, end_date, days]
    $opt  = getPastMonthPeriod($settingsJSON->safety_stock_period);
    // Remove unnecessary 'days'
    unset($opt['days']);
    // Get sold items by warehouse id.
    $whStocks = Sale::getSoldItems((int)$warehouseId, $opt);

    if ($whStocks && $whTo) {
      $grand_total    = 0;
      $transferItems = [];
      $transferQty   = 0;

      foreach ($whStocks as $stock) {
        $item = Product::getRow(['id' => $stock->product_id]);
        // No transfer item if safety_stock is 0 or not valid integer > 0
        // If safety stock = 0 or
        if ($item->safety_stock <= 0 || !$item->safety_stock) continue;
        // Get warehouse products.
        $whpFrom = WarehouseProduct::getRow(['product_id' => $item->id, 'warehouse_id' => $whFrom->id]);
        $whpTo   = WarehouseProduct::getRow(['product_id' => $item->id, 'warehouse_id' => $whTo->id]);

        if ($whpFrom->quantity <= 0) continue; // Ignore if no stock available from source.

        // Calculate formula to get quantity of transfer.
        $transferQty = getOrderStock($whpTo->quantity, $item->min_order_qty, $whpTo->safety_stock);

        if ($transferQty <= 0) continue; // If transfer qty is 0 or less then ignore.

        // if ($item->code == 'POCT15') {
        //   sendJSON(['error' => 1, 'msg' => [
        //       'product_code' => $item->code,
        //       'whp_quantity' => $whp->quantity,
        //       'min_order' => $item->min_order_qty,
        //       'safety_stock' => $whp->safety_stock,
        //       'transfer_qty' => $transfer_qty
        //     ]
        //   ]);
        // }

        $transferItem = [
          'id'            => $item->id, // Required.
          'markon_price'  => roundDecimal($item->markon_price),
          'quantity'      => $transferQty,
          'spec'          => null
        ];

        $transferItems[] = $transferItem;
        $grand_total += ($transferQty * $item->markon_price);
      }

      $transferData = [
        'warehouse_id_from' => $whFrom->id,
        'warehouse_id_to'   => $whTo->id,
        'note'              => '',
      ];

      if (self::add($transferData, $transferItems)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Add product transfer payment.
   * @param array $data [ *transfer_id, *bank_id_from, *bank_id_to, *amount, created_at, created_by ]
   */
  public static function addPayment($data)
  {
    $bankFrom = Bank::getRow(['id' => $data['bank_id_from']]);
    $bankTo   = Bank::getRow(['id' => $data['bank_id_to']]);
    $pt       = self::getRow(['id' => $data['transfer_id']]);

    if (!$pt) {
      setLastError("Product Transfer ID:{$data['transfer_id']} not found.");
      return false;
    }

    $data = setCreatedBy($data); // created_at, created_by

    $paymentDataFrom = [
      'transfer_id' => $data['transfer_id'],
      'reference'   => $pt->reference,
      'bank_id'     => $bankFrom->id,
      'method'      => $bankFrom->type,
      'amount'      => floatval($data['amount']),
      'type'        => 'sent',
      'note'        => ($data['note'] ?? ''),
      'created_at'  => $data['created_at'],
      'created_by'  => $data['created_by']
    ];

    $paymentDataTo = [
      'transfer_id' => $data['transfer_id'],
      'reference'   => $pt->reference,
      'bank_id'     => $bankTo->id,
      'method'      => $bankTo->type,
      'amount'      => $data['amount'],
      'type'        => 'received',
      'note'        => ($data['note'] ?? ''),
      'created_at'  => $data['created_at'],
      'created_by'  => $data['created_by']
    ];

    if (Payment::add($paymentDataFrom) && Payment::add($paymentDataTo)) {
      return true;
    }

    setLastError("Failed to add payment.");
    return false;
  }

  /**
   * Delete ProductTransfer.
   */
  public static function delete(array $where)
  {
    $pts = self::get($where);
    $deleted = 0;

    foreach ($pts as $pt) {
      DB::table('product_transfer')->delete(['id' => $pt->id]);

      if (DB::affectedRows()) {
        $ptitems = ProductTransferItem::get(['transfer_id' => $pt->id]);

        ProductTransferItem::delete(['transfer_id' => $pt->id]);
        Stock::delete(['transfer_id' => $pt->id]);

        foreach ($ptitems as $ptitem) {
          Product::sync((int)$ptitem->product_id);
        }

        Attachment::delete(['hashname' => $pt->attachment]);

        $deleted++;
      }
    }

    return $deleted;
  }

  /**
   * Get ProductTransfer collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_transfer')->get($where);
  }

  /**
   * Get ProductTransfer row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductTransfer.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('product_transfer')->select($columns, $escape);
  }

  /**
   * Sync product transfer payment.
   */
  public static function syncPayment($ptId)
  {
    $pt = self::getRow(['id' => $ptId]);
    $payments = Payment::get(['transfer_id' => $ptId]);
    $amount = 0;

    foreach ($payments as $payment) {
      // Since ProductTransfer using same transfer_id in payments. We filtered it.
      if ($payment->reference != $pt->reference) continue;
      if ($payment->type == 'received')
        $amount += $payment->amount;
    }

    $data['paid'] = $amount;
    $data['payment_status'] = $pt->payment_status;

    if ($amount == $pt->grand_total) {
      $data['payment_status'] = 'paid';
    } else if ($amount > 0 && $amount < $pt->grand_total) {
      $data['payment_status'] = 'partial';
    } else {
      $data['payment_status'] = 'pending';
    }

    if (self::update((int)$ptId, $data)) {
      return true;
    }

    return false;
  }

  /**
   * Update ProductTransfer.
   */
  public static function update(int $id, array $data, $items = [])
  {
    $pt = self::getRow(['id' => $id]);

    $data = setUpdatedBy($data);

    $json = (json_decode($pt->json ?? '') ?? (object)[]);

    if (isset($data['send_date']))     $json->send_date     = $data['send_date'];
    if (isset($data['received_date'])) $json->received_date = $data['received_date'];

    $data['json'] = json_encode($json);

    if ($items) {
      $data['items'] = '';
      $data['grand_total'] = 0;

      foreach ($items as $item) {
        $product = Product::getRow(['id' => $item['id']]);

        if ($product) {
          $data['items'] .= "- ({$product->code}) " . getExcerpt($product->name) . '<br>';

          $data['grand_total'] += $item['markon_price'] * $item['quantity'];
        }
      }
    }

    DB::table('product_transfer')->update($data, ['id' => $id]);

    if (DB::affectedRows()) {
      if ($items) {
        $newPt  = self::getRow(['id' => $id]);
        ProductTransferItem::delete(['transfer_id' => $id]);
        Stock::delete(['transfer_id' => $id]);

        $receivedTotal = 0;
        $receivedPartialTotal = 0;

        foreach ($items as $item) {
          $product = Product::getRow(['id' => $item['id']]);
          unset($item['id']);

          if ($product) {
            $item['transfer_id']  = $newPt->id;
            $item['product_id']   = $product->id;
            $item['product_code'] = $product->code;
            $item['status']       = ($data['status'] ?? $newPt->status);

            if (inStatus($item['status'], ['received', 'received_partial'])) {
              $balance = ($item['quantity'] - $item['received_qty']);

              // Change item status.
              $item['status'] = ($balance == 0 ? 'received' : 'received_partial');

              if ($item['status'] == 'received_partial') {
                $receivedPartialTotal++;
              } else if ($item['status'] == 'received') {
                $receivedTotal++;
              }
            }

            if (ProductTransferItem::add($item)) {
              if ($item['status'] == 'sent') {
                $res = Stock::decrease([
                  'transfer_id'  => $id,
                  'product_id'   => $product->id,
                  'quantity'     => $item['quantity'],
                  'warehouse_id' => $newPt->warehouse_id_from,
                  'created_at'   => $newPt->created_at
                ]);

                if (!$res) {
                  return false;
                }
              }

              if ($item['status'] == 'received' || $item['status'] == 'received_partial') {
                $res = Stock::decrease([
                  'transfer_id'  => $id,
                  'product_id'   => $product->id,
                  'quantity'     => $item['quantity'],
                  'warehouse_id' => $newPt->warehouse_id_from,
                  'created_at'   => $newPt->created_at
                ]);

                if (!$res) {
                  return false;
                }

                $res = Stock::increase([
                  'transfer_id'  => $id,
                  'product_id'   => $product->id,
                  'quantity'     => $item['quantity'],
                  'warehouse_id' => $newPt->warehouse_id_to,
                  'created_at'   => $newPt->created_at
                ]);

                if (!$res) {
                  return false;
                }
              }
            }

            Product::sync((int)$product->id);
          }
        }

        if ($receivedTotal == count($items)) {
          self::update((int)$id, ['status' => 'received']);
        } else if ($receivedPartialTotal > 0) {
          self::update((int)$id, ['status' => 'received_partial']);
        }
      }

      return true;
    }

    return false;
  }
}
