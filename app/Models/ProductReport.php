<?php

declare(strict_types=1);

namespace App\Models;

class ProductReport
{
  /**
   * Add new ProductReport.
   */
  public static function add(array $data)
  {
    DB::table('product_report')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductReport.
   */
  public static function delete(array $where)
  {
    DB::table('product_report')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductReport collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_report')->get($where);
  }

  /**
   * Get ProductReport row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductReport.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('product_report')->select($columns, $escape);
  }

  /**
   * Update ProductReport.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_report')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
