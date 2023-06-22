<?php

declare(strict_types=1);

namespace App\Models;

class Product
{
  /**
   * Add new Product.
   */
  public static function add(array $data)
  {
    DB::table('products')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Product.
   */
  public static function delete(array $where)
  {
    DB::table('products')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Product collections.
   */
  public static function get($where = [])
  {
    return DB::table('products')->get($where);
  }

  /**
   * Get Product row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Product.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('products')->select($columns, $escape);
  }

  /**
   * Sync product quantity.
   */
  public static function sync($where = [])
  {
    $synced = 0;
    $whIds = [];

    foreach (Warehouse::get(['active' => 1]) as $warehouse) {
      $whIds[] = $warehouse->id;
    }

    $totalQty = 0;

    foreach (self::get($where) as $product) {
      $productJS = getJSON($product->json);

      foreach ($whIds as $whId) {
        if (Stock::sync((int)$product->id, (int)$whId)) {
          $totalQty += Stock::totalQuantity((int)$product->id, (int)$whId);
        } else {
          setLastError("Failed sync Product: {$product->id}, Warehouse: {$whId}");
          return false;
        }
      }

      // Sync Last Report.
      $lastReport = ProductReport::select('*')
        ->orderBy('created_at', 'DESC')
        ->getRow(['product_id' => $product->id]);

      if ($lastReport && $lastReport->condition == 'good') {
        $productJS->condition = $lastReport->condition;
        $productJS->assigned_at = '';
        $productJS->assigned_by = 0;
        $productJS->note = '';
        $productJS->pic_id = 0;
        $productJS->pic_note = '';
        $productJS->updated_at = $lastReport->created_at;
      } else {
        $productJS->condition = $lastReport->condition;
        $productJS->updated_at = $lastReport->created_at;
      }

      $json = json_encode($productJS);

      if (Product::update((int)$product->id, ['quantity' => $totalQty, 'json' => $json, 'json_data' => $json])) {
        $synced++;
      }
    }

    return $synced;
  }

  /**
   * Update Product.
   */
  public static function update(int $id, array $data)
  {
    DB::table('products')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
