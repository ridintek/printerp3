<?php

declare(strict_types=1);

namespace App\Models;

class TrackingPOD
{
  /**
   * Add new TrackingPOD.
   * 
   * @param array $data [ *pod_id, *warehouse_id, *end_click, *mc_reject, note ]
   */
  public static function add(array $data)
  {
    if (empty($data['pod_id'])) {
      setLastError('POD is not set.');

      return false;
    }

    $product = Product::getRow(['id' => $data['pod_id']]);

    if (!$product) {
      setLastError('Product KLIKPOD is not found.');

      return false;
    }

    if (empty($data['warehouse_id'])) {
      setLastError('Warehouse is not set.');

      return false;
    }

    if ($data['mc_reject'] > 0) {
      // Adjust klikpod reject to add.
      $adjustmentData = [
        'date'          => ($data['created_at'] ?? date('Y-m-d H:i:s')),
        'warehouse_id'  => $data['warehouse_id'],
        'mode'         => 'formula',
        'note'         => 'Tracking POD Rejected' . (empty($data['note']) ? '.' : ': ' . $data['note'])
      ];

      $items[] = [
        'id'        => $data['pod_id'],
        'quantity'  => $data['mc_reject']
      ];

      if (!StockAdjustment::add($adjustmentData, $items)) {
        return false;
      }
    }

    $data = setCreatedBy($data);

    DB::table('trackingpod')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      self::sync((int)$insertId);

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete TrackingPOD.
   */
  public static function delete(array $where)
  {
    DB::table('trackingpod')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get total click of KlikPOD.
   * @param array $where [ warehouse_id, start_date, end_date ]
   */
  public static function getTotalKlikPOD($where = [])
  {
    $q = Stock::select('SUM(quantity) AS total')->where('product_id', 633);

    if (!empty($where['warehouse_id'])) {
      $q->where('warehouse_id', $where['warehouse_id']);
    }

    if (!empty($where['start_date'])) {
      $q->where("date >= '{$where['start_date']} 00:00:00'");
    }

    if (!empty($where['end_date'])) {
      $q->where("date <= '{$where['end_date']} 23:59:59'");
    }

    return floatval($q->getRow()?->total);
  }

  /**
   * Get TrackingPOD collections.
   */
  public static function get($where = [])
  {
    return DB::table('trackingpod')->get($where);
  }

  /**
   * Get TrackingPOD row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select TrackingPOD.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('trackingpod')->select($columns, $escape);
  }

  public static function sync(int $id)
  {
    $tpod = self::getRow(['id' => $id]);

    if (!$tpod) {
      return false;
    }

    $date = date('Y-m-d', strtotime($tpod->date));

    $todayClick = self::getTotalKlikPOD([
      'warehouse_id'  => $tpod->warehouse_id,
      'start_date'    => $date,
      'end_date'      => $date
    ]);

    $product = Product::getRow(['id' => $tpod->pod_id]);

    if (!$product) {
      setLastError('Sync: Tracking POD is not found.');
      return false;
    }

    $lastPOD = self::select('end_click')->orderBy('date', 'DESC')
      ->where('pod_id', $tpod->pod_id)
      ->where('warehouse_id', $tpod->warehouse_id)
      ->where("date < '{$tpod->date}'")
      ->getRow();

    if ($lastPOD) {
      $startClick  = ceil((float)$lastPOD->end_click);
    } else {
      $startClick  = ceil((float)$tpod->start_click);
    }

    $endClick       = floatval($tpod->end_click);
    $mcReject       = floatval($tpod->mc_reject) * -1; // Make minus.
    $costClick      = ($product->code == 'KLIKPOD' ? 1000 : 300); // Else 300 for KLIKPODBW.
    $tolerance      = ($product->code == 'KLIKPOD' ? 10 : 10); // Else 10% for KLIKPODBW.
    $usageClick     = $endClick - $startClick;
    $opReject       = ($todayClick - $mcReject - $usageClick); // Minus if not balance.
    $totalReject    = $mcReject + $opReject;
    $toleranceClick = $totalReject * $tolerance * 0.01;
    $balance        = $totalReject - $toleranceClick;
    $totalPenalty   = ($balance < 0 ? $balance * $costClick : 0);

    $data['start_click']  = $startClick;
    $data['end_click']    = $endClick;
    $data['usage_click']  = $usageClick;
    $data['today_click']  = $todayClick;
    $data['op_reject']    = $opReject;

    $data['cost_click']       = $costClick;
    $data['tolerance']        = $tolerance;
    $data['tolerance_click']  = $toleranceClick;

    $data['balance']        = $balance;
    $data['total_penalty']  = $totalPenalty;

    if (self::update($id, $data)) {
      return true;
    }

    return false;
  }

  /**
   * Update TrackingPOD.
   */
  public static function update(int $id, array $data)
  {
    DB::table('trackingpod')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
