<?php

declare(strict_types=1);

namespace App\Models;

class BankMutation
{
  /**
   * Add new BankMutation.
   */
  public static function add(array $data)
  {
    $data['reference'] = OrderRef::getReference('mutation');

    if (isset($data['bankfrom_id'])) {
      $data['from_bank_id'] = $data['bankfrom_id'];
    }

    if (isset($data['bankto_id'])) {
      $data['to_bank_id'] = $data['bankto_id'];
    }

    $data = setCreatedBy($data);

    DB::table('bank_mutations')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('mutation');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete BankMutation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_mutations')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get BankMutation collections.
   */
  public static function get($where = [])
  {
    return DB::table('bank_mutations')->get($where);
  }

  /**
   * Get BankMutation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select BankMutation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('bank_mutations')->select($columns, $escape);
  }

  /**
   * Sync BankMutation.
   */
  public static function sync(array $where)
  {
    $mutations = self::get($where);
    $updated = 0;

    foreach ($mutations as $mutation) {
      $payIn = 0;
      $payOut = 0;
      $status = $mutation->status;

      $payments = Payment::get(['mutation_id' => $mutation->id]);

      foreach ($payments as $payment) {
        if ($payment->type == 'sent') {
          $payOut += $payment->amount;
        } else if ($payment->type == 'received') {
          $payIn += $payment->amount;
        }
      }

      if ($payIn != $payOut) {
        setLastError("Payment In ({$payIn}) and Payment Out ({$payOut}) is not same.");

        return false;
      }

      if ($mutation->amount == $payIn) {
        $status = 'paid';
      } else if ($payIn > 0 && $mutation->amount > $payIn) {
        $status = 'partial';
      } else if ($payIn <= 0) {
        $status = 'pending';
      }

      $paymentValidation = PaymentValidation::select('*')
        ->where('mutation_id', $mutation->id)
        ->orderBy('date', 'DESC')
        ->getRow();

      if ($paymentValidation) {
        if ($paymentValidation->status == 'pending') {
          $status = 'waiting_transfer';
        } else if ($paymentValidation->status == 'expired') {
          $status = 'expired';
        }
      }

      if (self::update((int)$mutation->id, ['paid' => $payIn, 'status' => $status])) {
        $updated++;
      }
    }

    return $updated;
  }

  /**
   * Update BankMutation.
   */
  public static function update(int $id, array $data)
  {
    if (isset($data['bankfrom_id'])) {
      $data['from_bank_id'] = $data['bankfrom_id'];
    }

    if (isset($data['bankto_id'])) {
      $data['to_bank_id'] = $data['bankto_id'];
    }

    DB::table('bank_mutations')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
