<?php

declare(strict_types=1);

namespace App\Models;

class Setting
{
  /**
   * Add new Setting.
   */
  public static function add(array $data)
  {
    DB::table('settings')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Setting.
   */
  public static function delete(array $where)
  {
    DB::table('settings')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Setting collections.
   */
  public static function get($where = [])
  {
    return DB::table('settings')->get($where);
  }

  /**
   * Get Setting row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Setting.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('settings')->select($columns, $escape);
  }

  /**
   * Update Setting.
   */
  public static function update(int $id, array $data)
  {
    DB::table('settings')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
