<?php

declare(strict_types=1);

namespace App\Models;

class Customer
{
  /**
   * Add new Customer.
   */
  public static function add(array $data)
  {
    if (empty($data['customer_group_id'])) {
      setLastError('Customer Group ID is not set.');
      return false;
    }

    $csGroup = CustomerGroup::getRow(['id' => $data['customer_group_id']]);

    if ($csGroup) {
      $data['customer_group_name'] = $csGroup->name;
    }

    $data = nulling($data, ['address', 'city', 'email']);

    DB::table('customers')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Customer.
   */
  public static function delete(array $where)
  {
    DB::table('customers')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Customer collections.
   */
  public static function get($where = [])
  {
    return DB::table('customers')->get($where);
  }

  /**
   * Get Customer row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Customer.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('customers')->select($columns, $escape);
  }

  /**
   * Update Customer.
   */
  public static function update(int $id, array $data)
  {
    $data = nulling($data, ['address', 'city', 'email']);

    DB::table('customers')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
