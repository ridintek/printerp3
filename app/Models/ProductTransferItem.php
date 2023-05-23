<?php

declare(strict_types=1);

namespace App\Models;

class ProductTransferItem
{
  /**
   * Add new ProductTransferItem.
   */
  public static function add(array $data)
  {
    DB::table('product_transfer_item')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductTransferItem.
   */
  public static function delete(array $where)
  {
    DB::table('product_transfer_item')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductTransferItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_transfer_item')->get($where);
  }

  /**
   * Get ProductTransferItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductTransferItem.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('product_transfer_item')->select($columns, $escape);
  }

  /**
   * Update ProductTransferItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_transfer_item')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
