<?php

declare(strict_types=1);

namespace App\Models;

class PriceGroup
{
  /**
   * Add new PriceGroup.
   */
  public static function add(array $data)
  {
    DB::table('pricegroup')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete PriceGroup.
   */
  public static function delete(array $where)
  {
    DB::table('pricegroup')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get PriceGroup collections.
   */
  public static function get($where = [])
  {
    return DB::table('pricegroup')->get($where);
  }

  /**
   * Get PriceGroup row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select PriceGroup.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('pricegroup')->select($columns, $escape);
  }

  /**
   * Update PriceGroup.
   */
  public static function update(int $id, array $data)
  {
    DB::table('pricegroup')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}