<?php

declare(strict_types=1);

namespace App\Models;

class CashOnHand
{
  /**
   * Add new CashOnHand.
   */
  public static function add(array $data)
  {
    if (empty($data['biller_id'])) {
      setLastError('Biller is not set.');
      return false;
    }

    $cashBank = Bank::getRow(['biller_id' => $data['biller_id'], 'type' => 'Cash']);

    $data['bank_id'] = $cashBank->id;

    $data = setCreatedBy($data);

    // setLastError(json_encode($data)); return false;

    DB::table('cashonhand')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      BankReconciliation::sync();

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete CashOnHand.
   */
  public static function delete(array $where)
  {
    DB::table('cashonhand')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get CashOnHand collections.
   */
  public static function get($where = [])
  {
    return DB::table('cashonhand')->get($where);
  }

  /**
   * Get CashOnHand row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select CashOnHand.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('cashonhand')->select($columns, $escape);
  }

  /**
   * Update CashOnHand.
   */
  public static function update(int $id, array $data)
  {
    DB::table('cashonhand')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
