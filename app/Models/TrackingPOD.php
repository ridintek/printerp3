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

    $klikpod = WarehouseProduct::getRow(['product_id' => $data['pod_id'], 'warehouse_id' => $data['warehouse_id']]);

    if (!$klikpod) {
      setLastError('Klikpod is not found.');

      return false;
    }

    $data['erp_click'] = ceil(floatval($klikpod->quantity));

    $lastPOD = self::getRow(['pod_id' => $data['pod_id'], 'warehouse_id' => $data['warehouse_id']]);

    if ($lastPOD) {
      $data['start_click']  = ceil(floatval($lastPOD->end_click));
    } else {
      $data['start_click']  = $data['erp_click'];
    }

    // Convert to minus.
    $data['mc_reject']  = floatval($data['mc_reject'] > 0 ? $data['mc_reject'] * -1 : $data['mc_reject']);
    $data['op_reject']  = floatval($data['erp_click'] - $data['end_click'] - $data['mc_reject']);
    $data['op_reject']  = floatval($data['op_reject'] < 0 ? $data['op_reject'] : 0);

    $data['cost_click'] = ($product->code == 'KLIKPOD' ? 1000 : 300); // Else 300 for KLIKPODBW.
    $data['tolerance']  = ($product->code == 'KLIKPOD' ? 10 : 10); // Else 10% for KLIKPODBW.

    $data['tolerance_click']  = round(($data['mc_reject'] + $data['op_reject']) * 0.01 * $data['tolerance']); // 0.01 == 100%
    $data['usage_click']      = $data['end_click'] - $data['start_click'];
    $data['balance']          = ($data['mc_reject'] + $data['op_reject']) - $data['tolerance_click'];
    $data['total_penalty']    = ($data['balance'] < 0 ? $data['balance'] * $data['cost_click'] : 0);

    if ($data['end_click'] != $data['erp_click']) {
      // Adjust klikpod reject to add.

      $adjustmentData = [
        'date'          => ($data['created_at'] ?? date('Y-m-d H:i:s')),
        'warehouse_id'  => $data['warehouse_id'],
        'mode'         => 'formula',
        'note'         => 'Tracking POD Rejected' . (empty($data['note']) ? '.' : ': ' . $data['note'])
      ];

      $items[] = [
        'id'        => $data['pod_id'],
        'quantity'  => $data['mc_reject'] * -1
      ];

      if (!StockAdjustment::add($adjustmentData, $items)) {
        return false;
      }
    }

    $data = setCreatedBy($data);

    DB::table('trackingpod')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
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
