<?php

declare(strict_types=1);

namespace App\Models;

class Expense
{
  /**
   * Add new Expense.
   */
  public static function add(array $data)
  {
    if (isset($data['bank'])) { // Compatibility.
      $bank = Bank::getRow(['code' => $data['bank']]);
      $data['bank_id'] = $bank->id;
    }

    if (isset($data['biller'])) { // Compatibility.
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

    if (isset($data['category'])) { // Compatibility.
      $category = ExpenseCategory::getRow(['code' => $data['category']]);
      $data['category_id'] = $category->id;
    }

    if (isset($data['supplier'])) { // Compatibility.
      $supplier = Supplier::getRow(['id' => $data['supplier']]);
      $data['supplier_id'] = $supplier->id;
      unset($data['supplier']);
    }

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('expense');
    $data['status'] = 'pending';
    $data['payment_status'] = 'need_approval';

    DB::table('expenses')->insert($data);
    $insertID = DB::insertID();

    if ($insertID) {
      OrderRef::updateReference('expense');
    }

    return $insertID;
  }

  /**
   * Delete Expense.
   */
  public static function delete(array $where)
  {
    DB::table('expenses')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Expense collections.
   */
  public static function get($where = [])
  {
    return DB::table('expenses')->get($where);
  }

  /**
   * Get Expense row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Expense.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('expenses')->select($columns, $escape);
  }

  /**
   * Update Expense.
   */
  public static function update(int $id, array $data)
  {
    DB::table('expenses')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
