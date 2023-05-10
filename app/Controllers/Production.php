<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{DB, Sale, SaleItem, StockAdjustment, TrackingPOD, WarehouseProduct};

class Production extends BaseController
{
  public function getSaleItems()
  {
    checkPermission('Sale.Complete');

    $billers        = getPost('biller');
    $warehouses     = getPost('warehouse');
    $status         = getPost('status');
    $paymentStatus  = getPost('payment_status');
    $operatorBy     = getPost('operator_by');
    $startDate      = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('sale_items');
    $dt
      ->select("sale_items.id AS id, sale_items.date,
        sales.reference, operator.fullname AS operator_name,
        biller.name AS biller_name, warehouse.name AS warehouse_name,
        CONCAT(customers.name, ' (', customers.phone, ')') AS customer_name,
        sale_items.product_name, sale_items.status, sales.payment_status")
      ->join('sales', 'sales.id = sale_items.sale_id', 'left')
      ->join('biller', 'biller.code = sales.biller', 'left')
      ->join('customers', 'customers.phone = sales.customer', 'left')
      ->join('users operator', "operator.id = sale_items.json->>'$.operator_id'", 'left')
      ->join('warehouse', 'warehouse.code = sales.warehouse', 'left')
      ->whereIn('sale_items.status', ['completed', 'completed_partial', 'waiting_production'])
      ->where("sales.date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->editColumn('id', function ($data) {
        return '<input class="checkbox" type="checkbox" value="' . $data['id'] . '">';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
      });

    $userJS = getJSON(session('login')?->json);

    if (isset($userJS->billers) && !empty($userJS->billers)) {
      if ($billers) {
        $billers = array_merge($billers, $userJS->billers);
      } else {
        $billers = $userJS->billers;
      }
    }

    if (session('login')->biller_id) {
      if ($billers) {
        $billers[] = session('login')->biller_id;
      } else {
        $billers = [session('login')->biller_id];
      }
    }

    if ($billers) {
      $dt->whereIn('sales.biller_id', $billers);
    }

    if ($warehouses) {
      $dt->whereIn('sales.warehouse_id', $warehouses);
    }

    if ($status) {
      $dt->whereIn('sale_items.status', $status);
    }

    if ($paymentStatus) {
      $dt->whereIn('sales.payment_status', $paymentStatus);
    }

    if ($operatorBy) {
      $dt->whereIn("sale_items.json->>'$.operator_id'", $operatorBy);
    }

    $dt->generate();
  }

  public function getTrackingPODs()
  {
    checkPermission('Sale.Complete');

    $warehouses = getPost('warehouse');
    $startDate  = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate    = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('trackingpod');
    $dt
      ->select("trackingpod.id AS id, trackingpod.created_at, products.code AS category,
        start_click, end_click, usage_click, (mc_reject + op_reject) AS total_reject, erp_click, balance,
        warehouse.name AS warehouse_name, creator.fullname, attachment")
      ->join('products', 'products.id = trackingpod.pod_id', 'left')
      ->join('warehouse', 'warehouse.id = trackingpod.warehouse_id', 'left')
      ->join('users creator', 'creator.id = trackingpod.created_by', 'left')
      ->where("trackingpod.created_at BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('production/trackingpod/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('production/trackingpod/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('production/trackingpod/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('start_click', function ($data) {
        return formatNumber($data['start_click']);
      })
      ->editColumn('end_click', function ($data) {
        return formatNumber($data['end_click']);
      })
      ->editColumn('usage_click', function ($data) {
        return formatNumber($data['usage_click']);
      })
      ->editColumn('total_reject', function ($data) {
        return formatNumber($data['total_reject']);
      })
      ->editColumn('erp_click', function ($data) {
        return formatNumber($data['erp_click']);
      })
      ->editColumn('balance', function ($data) {
        return formatNumber($data['balance']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      });

    $userJS = getJSON(session('login')?->json);

    if (isset($userJS->warehouses) && !empty($userJS->warehouses)) {
      if ($warehouses) {
        $warehouses = array_merge($warehouses, $userJS->warehouses);
      } else {
        $warehouses = $userJS->warehouses;
      }
    }

    if (session('login')->warehouse_id) {
      if ($warehouses) {
        $warehouses[] = session('login')->warehouse_id;
      } else {
        $warehouses = [session('login')->warehouse_id];
      }
    }

    if ($warehouses) {
      $dt->whereIn('trackingpod.warehouse_id', $warehouses);
    }

    $dt->generate();
  }

  public function index()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Sale.Complete');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.production'), 'slug' => 'production', 'url' => '#'],
        ['name' => lang('App.saleitem'), 'slug' => 'saleitem', 'url' => '#']
      ],
      'content' => 'Production/index',
      'title' => lang('App.saleitem')
    ];

    return $this->buildPage($this->data);
  }

  public function complete()
  {
    checkPermission('Sale.Complete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $_dbg         = (getPost('_dbg') == 1);
      $items        = getPost('item');
      $operatorId   = getPost('operator');
      $completeDate = dateTimePHP(getPost('completedate'));

      if (!$items) {
        $this->response(400, ['message' => 'No sale items are selected.']);
      }

      $isCompleteOverTime = false;

      DB::transStart();

      for ($a = 0; $a < count($items['id']); $a++) {
        $itemId       = intval($items['id'][$a]);
        $itemCode     = $items['code'][$a];
        $finishedQty  = floatval($items['finished_qty'][$a]);
        $quantity     = floatval($items['quantity'][$a]);
        $saleId       = intval($items['sale_id'][$a]);
        $totalQty     = floatval($items['total_qty'][$a]);

        $sale = Sale::getRow(['id' => $saleId]);

        if (!$sale) {
          $this->response(404, ['message' => 'Invoice is missing.']);
        }

        $saleJS = getJSON($sale->json);

        if (isset($saleJS->approved) && $saleJS->approved != 1) {
          $this->response(400, ['message' => "Sale item {$itemCode} is not approved yet."]);
        }

        if (($finishedQty + $quantity) > $totalQty) {
          $this->response(400, ['message' => "Sale item {$itemCode} cannot over-complete."]);
        }

        if ($quantity <= 0) {
          $this->response(400, ['message' => "Sale item {$itemCode} quantity cannot be zero or less."]);
        }

        if (time() > strtotime($sale->due_date)) {
          $isCompleteOverTime = true;
        }

        if ($isCompleteOverTime && $_dbg) {
          $minutes      = rand(10, (60 * 5)); // 10 minute to 5 hours
          $completeDate = date('Y-m-d H:i:s', strtotime("-{$minutes} minute", strtotime($sale->due_date)));
        }

        $res = SaleItem::complete($itemId, [
          'completed_at'  => $completeDate,
          'completed_by'  => $operatorId,
          'quantity'      => $quantity,
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        Sale::sync(['id' => $sale->id]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Sale items have been completed.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.completeitem');

    $this->response(200, ['content' => view('Production/complete', $this->data)]);
  }

  public function trackingpod()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('TrackingPOD.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.production'), 'slug' => 'production', 'url' => '#'],
        ['name' => lang('App.trackingpod'), 'slug' => 'trackingpod', 'url' => '#']
      ],
      'content' => 'Production/TrackingPOD/index',
      'title' => lang('App.trackingpod')
    ];

    return $this->buildPage($this->data);
  }

  protected function trackingpod_add()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $date       = dateTimePHP(getPost('date'));
      $category   = getPost('category');
      $warehouse  = getPost('warehouse');
      $note       = stripTags(getPost('note'));
      $endClick = 0;
      $mcReject = 0;

      $endClicks = getPost('endclick');
      $mcRejects = getPost('rejectmachine');

      for ($a = 0; $a < count($endClicks); $a++) {
        $endClick += filterDecimal($endClicks[$a]);
        $mcReject += filterDecimal($mcRejects[$a]);
      }

      $data = [
        'pod_id'        => $category,
        'warehouse_id'  => $warehouse,
        'end_click'     => $endClick,
        'mc_reject'     => $mcReject,
        'note'          => $note,
        'created_at'    => $date,
      ];

      DB::transStart();

      $data = $this->useAttachment($data, null, function ($upload) use ($endClick) {
        if (!$upload->has('attachment')) {
          $this->response(400, ['message' => 'Attachment berupa foto display mesin POD dibutuhkan.']);
        }

        $ocr = ocr($upload->getTempName());
        $fullColor = 0;

        if ($ocr) {
          // Multi Full Color Counter in one image. Must be Vertical Ordered.
          for ($x = 0; $x < count($ocr); $x++) {
            if (strcasecmp($ocr[$x], 'Full Color Counter') === 0) {
              $fullColor += filterDecimal($ocr[$x + 1]);
            }
          }
        }

        if ($endClick != $fullColor) {
          $this->response(400, [
            'message' => "End Click ($endClick) tidak sesuai attachment Full Color Counter ($fullColor)."
          ]);
        }
      });

      $insertId = TrackingPOD::add($data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'TrackingPOD has been added.']);
      }

      $this->response(400, ['message' => 'Failed to add TrackingPOD.']);
    }

    $this->data['title'] = lang('App.addtrackingpod');

    $this->response(200, ['content' => view('Production/TrackingPOD/add', $this->data)]);
  }

  protected function trackingpod_delete($id = null)
  {
    $tpod = TrackingPOD::getRow(['id' => $id]);

    if (!$tpod) {
      $this->response(404, ['message' => 'TrackingPOD is not found.']);
    }

    DB::transStart();

    if ($tpod->adjustment_id) {
      if (!StockAdjustment::delete(['id' => $tpod->adjustment_id])) {
        $this->response(400, ['message' => getLastError()]);
      }
    }

    if (!TrackingPOD::delete(['id' => $id])) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'TrackingPOD has been deleted.']);
    }

    $this->response(400, ['message' => 'Failed to delete TrackingPOD']);
  }

  protected function trackingpod_edit($id = null)
  {
  }

  protected function trackingpod_view($id = null)
  {
    $tpod = TrackingPOD::getRow(['id' => $id]);

    if (!$tpod) {
      $this->response(404, ['message' => 'TrackingPOD is not found.']);
    }

    $whp = WarehouseProduct::getRow(['product_id' => $tpod->pod_id, 'warehouse_id' => $tpod->warehouse_id]);

    $this->data['tpod'] = $tpod;
    $this->data['currentClick'] = floatval($whp->quantity);
    $this->data['title'] = lang('App.viewtrackingpod');

    $this->response(200, ['content' => view('Production/TrackingPOD/view', $this->data)]);
  }
}
