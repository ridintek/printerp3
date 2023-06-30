<?php

declare(strict_types=1);

namespace App\Models;

class BankReconciliation
{
  /**
   * Add new BankReconciliation.
   */
  public static function add(array $data)
  {
    DB::table('bank_reconciliations')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete BankReconciliation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_reconciliations')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get BankReconciliation collections.
   */
  public static function get($where = [])
  {
    return DB::table('bank_reconciliations')->get($where);
  }

  /**
   * Get BankReconciliation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select BankReconciliation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('bank_reconciliations')->select($columns, $escape);
  }

  /**
   * Sync BankReconciliation.
   */
  public static function sync()
  {
    $curl = curl_init(base_url('api/v2/mutasibank/accounts'));

    curl_setopt_array($curl, [
      CURLOPT_HEADER => FALSE,
      CURLOPT_RETURNTRANSFER => TRUE
    ]);

    $data = curl_exec($curl);

    if (!$data) {
      return FALSE;
    }

    $res = getJSON($data);

    if (!$res) {
      setLastError('Failed get data from api mutasibank accounts.');
      return FALSE;
    }

    // EDC/Transfer Group
    $bankGroups = Bank::select('number, name, holder, type')
      ->where('active', 1)
      ->where("number <> '2222004005'")
      ->whereIn('type', ['EDC', 'Transfer'])
      ->groupBy('number')
      ->get();

    // Add Cash
    $cashGroups = Bank::select('biller_id, number, name, holder, type')
      ->where('active', 1)
      ->whereIn('type', ['Cash'])
      ->get();

    $accGroups = array_merge($bankGroups, $cashGroups);

    $banks = Bank::get(['active' => 1]);

    foreach ($accGroups as $row) { // Grouped by bank number.
      $mutasiBank   = null;
      $lastCash     = 0;
      $totalBalance = 0;

      foreach ($banks as $bank) { // Collect balance.
        if ($bank->type != 'Cash' && strcmp(strval($row->number), strval($bank->number)) === 0) {
          $totalBalance += $bank->amount;
        }

        if ($row->name == $bank->name && $bank->type == 'Cash') { // For cash.
          $totalBalance += $bank->amount;
        }
      }

      if ($row->type == 'Cash') { // Recon CashOnHand.
        $date = date('Y-m-d');

        $coh = CashOnHand::select('amount')
          ->orderBy('date', 'DESC')
          ->getRow(['biller_id' => $row->biller_id]);

        if ($coh) {
          $lastCash = (int)$coh->amount;
        }
      }

      foreach ($res->data as $mb) {
        if ($row->type != 'Cash' && strcmp(strval($mb->account_no), strval($row->number)) === 0) {
          $mutasiBank = $mb;
          break;
        }
      }

      if ($row->number) {
        $recon = self::getRow(['account_no' => $row->number]);
      } else {
        $recon = self::getRow(['erp_acc_name' => $row->name]);
      }

      if ($recon) { // If exist, then update.
        $reconData = [
          'erp_acc_name'  => ($row->holder ?? $row->name),
          'account_no'    => $row->number,
          'amount_erp'    => $totalBalance
        ];

        if ($row->type == 'Cash') {
          $reconData['amount_mb'] = $lastCash;
        }

        if ($mutasiBank) {
          $reconData['mb_acc_name']     = $mutasiBank->account_name;
          $reconData['mb_bank_name']    = $mutasiBank->bank;
          $reconData['amount_mb']       = $mutasiBank->balance;
          $reconData['last_sync_date']  = $mutasiBank->last_bot_activity;
        }

        if (!self::update((int)$recon->id, $reconData)) {
          return false;
        }
      } else { // If not exist, insert new.
        $reconData = [
          'erp_acc_name' => ($row->holder ?? $row->name),
          'account_no'   => $row->number,
          'amount_erp'   => $totalBalance
        ];

        if ($row->type == 'Cash') {
          $reconData['amount_mb'] = $lastCash;
        }

        if ($mutasiBank) {
          $reconData['mb_acc_name']    = $mutasiBank->account_name;
          $reconData['mb_bank_name']   = $mutasiBank->bank;
          $reconData['amount_mb']      = $mutasiBank->balance;
          $reconData['last_sync_date'] = $mutasiBank->last_bot_activity;
        }

        if (!self::add($reconData)) {
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Update BankReconciliation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('bank_reconciliations')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
