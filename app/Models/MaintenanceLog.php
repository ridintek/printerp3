<?php

declare(strict_types=1);

namespace App\Models;

class MaintenanceLog
{
  /**
   * Add new MaintenanceLog.
   */
  public static function add(array $data)
  {
    DB::table('maintenance_logs')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete MaintenanceLog.
   */
  public static function delete(array $where)
  {
    DB::table('maintenance_logs')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get MaintenanceLog collections.
   */
  public static function get($where = [])
  {
    return DB::table('maintenance_logs')->get($where);
  }

  /**
   * Get MaintenanceLog row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select MaintenanceLog.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('maintenance_logs')->select($columns, $escape);
  }

  /**
   * Update MaintenanceLog.
   */
  public static function update(int $id, array $data)
  {
    DB::table('maintenance_logs')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
