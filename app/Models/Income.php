<?php

declare(strict_types=1);

namespace App\Models;

class Income
{
  /**
   * Add new Income.
   */
  public static function add(array $data)
  {
    if (isset($data['bank_id'])) {
      $bank = Bank::getRow(['id' => $data['bank_id']]);
      $data['bank'] = $bank->code;
    }

    if (isset($data['biller_id'])) {
      $biller = Biller::getRow(['id' => $data['biller_id']]);
      $data['biller'] = $biller->code;
    }

    if (isset($data['category_id'])) {
      $category = IncomeCategory::getRow(['id' => $data['category_id']]);
      $data['category'] = $category->code;
    }

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('income');

    DB::table('incomes')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('income');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return FALSE;
  }

  /**
   * Delete Income.
   */
  public static function delete(array $where)
  {
    DB::table('incomes')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Income collections.
   */
  public static function get($where = [])
  {
    return DB::table('incomes')->get($where);
  }

  /**
   * Get Income row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Income.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('incomes')->select($columns, $escape);
  }

  /**
   * Update Income.
   */
  public static function update(int $id, array $data)
  {
    if (isset($data['bank_id'])) {
      $bank = Bank::getRow(['id' => $data['bank_id']]);
      $data['bank'] = $bank->code;
    }

    if (isset($data['biller_id'])) {
      $biller = Biller::getRow(['id' => $data['biller_id']]);
      $data['biller'] = $biller->code;
    }

    if (isset($data['category_id'])) {
      $category = IncomeCategory::getRow(['id' => $data['category_id']]);
      $data['category'] = $category->code;
    }

    $data = setUpdatedBy($data);

    DB::table('incomes')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
