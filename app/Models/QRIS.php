<?php

declare(strict_types=1);

namespace App\Models;

class QRIS
{
  protected static $api = '139139211228911';
  protected static $mID = '195255685725';

  /**
   * Add new QRIS.
   */
  public static function add(array $data)
  {
    if (empty($data['sale_id'])) {
      setLastError('Sale id is not set.');
      return false;
    }

    $sale = Sale::getRow(['id' => $data['sale_id']]);

    if (!$sale) {
      setLastError('Sale is not found.');
      return false;
    }

    $amount = intval($sale->grand_total);

    $response = self::createInvoice($sale->reference, $amount);

    if ($response === false) {
      return false;
    }

    $data['reference']  = $sale->reference;
    $data['content']    = $response->data->qris_content;
    $data['invoice_id'] = $response->data->qris_invoiceid;
    $data['nm_id']      = $response->data->qris_nmid;
    $data['amount']     = $amount;
    $data['request_at'] = $response->data->qris_request_date;
    $data['status']     = 'pending';
    $data['expired_at'] = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime($data['request_at'])));

    $data = setCreatedBy($data);

    DB::table('qris')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  public static function checkStatus(int $qrId)
  {
    $qris = self::getRow(['id' => $qrId]);

    $query = http_build_query([
      'do'            => 'checkStatus',
      'apikey'        => self::$api,
      'mID'           => self::$mID,
      'invid'         => $qris->invoice_id,
      'trxvalue'      => intval($qris->amount),
      'trxdate'       => date('Y-m-d', strtotime($qris->request_at))
    ]);

    $curl = curl_init("https://qris.id/restapi/qris/checkpaid_qris.php?{$query}");

    curl_setopt_array($curl, [
      CURLOPT_CUSTOMREQUEST   => 'GET',
      CURLOPT_RETURNTRANSFER  => true
    ]);

    $res = curl_exec($curl);

    curl_close($curl);

    $response = getJSON($res);

    if ($response && $response->status == 'success') {
      if (!self::update($qrId, ['status' => 'paid', 'check_at' => date('Y-m-d H:i:s')])) {
        return false;
      }

      return $response;
    }

    if ($response) {
      setLastError($response->data->qris_status);
    } else {
      setLastError('Failed to check QRIS status.');
    }

    return false;
  }

  public static function createInvoice(string $reference, int $amount)
  {
    $query = http_build_query([
      'do'            => 'create-invoice',
      'apikey'        => self::$api,
      'mID'           => self::$mID,
      'cliTrxNumber' => $reference,
      'cliTrxAmount' => $amount
    ]);

    $curl = curl_init("https://qris.id/restapi/qris/show_qris.php?{$query}");

    curl_setopt_array($curl, [
      CURLOPT_CUSTOMREQUEST   => 'GET',
      CURLOPT_RETURNTRANSFER  => true
    ]);

    $res = curl_exec($curl);

    curl_close($curl);

    $response = getJSON($res);

    if ($response && $response->status == 'success') {
      return $response;
    }

    if ($response) {
      setLastError($response->data->qris_status);
    } else {
      setLastError('Failed to check QRIS status.');
    }

    return false;
  }

  /**
   * Delete QRIS.
   */
  public static function delete(array $where)
  {
    DB::table('qris')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get QRIS collections.
   */
  public static function get($where = [])
  {
    return DB::table('qris')->get($where);
  }

  /**
   * Get QRIS row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select QRIS.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('qris')->select($columns, $escape);
  }

  /**
   * Sync QRIS.
   */
  public static function sync($where = [])
  {
    $qrs = self::get($where);
    $synced = 0;

    foreach ($qrs as $qr) {
      $status = $qr->status;

      if (strtotime(date('Y-m-d H:i:s')) > strtotime($qr->expired_at) && $status == 'pending') {
        $status = 'expired';
      }

      if (!self::update((int)$qr->id, ['status' => $status])) {
        return false;
      }

      $synced++;
    }

    return $synced;
  }

  /**
   * Update QRIS.
   */
  public static function update(int $id, array $data)
  {
    DB::table('qris')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
