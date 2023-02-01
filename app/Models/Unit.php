<?php

declare(strict_types=1);

namespace App\Models;

class Unit
{
  /**
   * Add new Unit.
   */
  public static function add(array $data)
  {
    DB::table('units')->insert($data);

    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Unit.
   */
  public static function delete(array $where)
  {
    DB::table('units')->delete($where);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Unit collections.
   */
  public static function get($where = [])
  {
    return DB::table('units')->get($where);
  }

  /**
   * Get Unit row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Unit.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('units')->select($columns, $escape);
  }

  /**
   * Update Unit.
   */
  public static function update(int $id, array $data)
  {
    DB::table('units')->update($data, ['id' => $id]);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
