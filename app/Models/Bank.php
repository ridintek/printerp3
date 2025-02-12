<?php

declare(strict_types=1);

namespace App\Models;

class Bank
{
  /**
   * Add new Bank.
   */
  public static function add(array $data)
  {
    DB::table('banks')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Decrease amount.
   * @param int $bankId Bank ID.
   * @param float $amount Amount (positive) to decrease.
   */
  public static function amountDecrease(int $bankId, float $amount)
  {
    return self::update($bankId, ['amount' => self::getRow(['id' => $bankId])->amount - $amount]);
  }

  /**
   * Increase amount.
   * @param int $bankId Bank ID.
   * @param float $amount Amount (positive) to increase.
   */
  public static function amountIncrease(int $bankId, float $amount)
  {
    return self::update($bankId, ['amount' => self::getRow(['id' => $bankId])->amount + $amount]);
  }

  /**
   * Get bank balance.
   */
  public static function balance(int $bankId)
  {
    $res = Bank::select('(COALESCE(recv.total, 0) - COALESCE(sent.total, 0)) AS balance')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payments WHERE type LIKE 'received' GROUP BY bank_id) recv", 'recv.bank_id = banks.id', 'left')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payments WHERE type LIKE 'sent' GROUP BY bank_id) sent", 'sent.bank_id = banks.id', 'left')
      ->where('banks.id', $bankId)
      ->getRow();

    if ($res) {
      return $res->balance;
    }

    return false;
  }

  /**
   * Delete Bank.
   */
  public static function delete(array $where)
  {
    DB::table('banks')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Bank collections.
   */
  public static function get($where = [])
  {
    return DB::table('banks')->get($where);
  }

  /**
   * Get Bank row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Bank.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('banks')->select($columns, $escape);
  }

  /**
   * Sync bank balance.
   */
  public static function sync(int $bankId = null)
  {
    $banks = [];

    if ($bankId) {
      $banks[] = self::getRow(['id' => $bankId]);
    } else {
      $banks = self::get();
    }

    if ($banks) {
      foreach ($banks as $bank) {
        self::update((int)$bank->id, ['amount' => self::balance((int)$bank->id)]);
      }

      return true;
    }

    return false;
  }

  /**
   * Update Bank.
   */
  public static function update(int $id, array $data)
  {
    DB::table('banks')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
