<?php

declare(strict_types=1);

namespace App\Models;

class Payment
{
  /**
   * Add new Payment.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    DB::table('payment')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Payment.
   */
  public static function delete(array $where)
  {
    DB::table('payment')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Payment collections.
   */
  public static function get($where = [])
  {
    return DB::table('payment')->get($where);
  }

  /**
   * Get Payment row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Payment.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('payment')->select($columns, $escape);
  }

  /**
   * Update Payment.
   */
  public static function update(int $id, array $data)
  {
    DB::table('payment')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}