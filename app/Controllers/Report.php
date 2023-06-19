<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, Spreadsheet};
use App\Models\{
  Biller,
  DB,
  Jobs,
  MaintenanceLog,
  Notification,
  Product,
  ProductReport,
  Stock,
  User,
  UserGroup,
  WAJob,
  Warehouse
};

class Report extends BaseController
{
  public function dailyperformance()
  {
    checkPermission();

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.dailyperformance'), 'slug' => 'dailyperformance', 'url' => '#']
      ],
      'content' => 'Report/DailyPerformance/index',
      'title' => lang('App.dailyperformance')
    ];

    return $this->buildPage($this->data);
  }

  /**
   * Get daily performance report.
   */
  public function getDailyPerformanceReport()
  {
    $period = getGET('period'); // 2022-11
    $xls    = (getGET('xls') == 1 ? true : false);

    $opt = [];

    $opt['period'] = ($period ?? date('Y-m')); // Default current year and month.

    if (!$xls) { // Send to DataTables.
      $this->response(200, [
        'data' => getDailyPerformanceReport($opt) // Helper
      ]);
    } else { // Save as Excel
      $ddGrid = [
        ['F', 'G', 'H'], ['I', 'J', 'K'], ['L', 'M', 'N'], ['O', 'P', 'Q'], ['R', 'S', 'T'],
        ['U', 'V', 'W'], ['X', 'Y', 'Z'], ['AA', 'AB', 'AC'], ['AD', 'AE', 'AF'], ['AG', 'AH', 'AI'],
        ['AJ', 'AK', 'AL'], ['AM', 'AN', 'AO'], ['AP', 'AQ', 'AR'], ['AS', 'AT', 'AU'], ['AV', 'AW', 'AX'],
        ['AY', 'AZ', 'BA'], ['BB', 'BC', 'BD'], ['BE', 'BF', 'BG'], ['BH', 'BI', 'BJ'], ['BK', 'BL', 'BM'],
        ['BN', 'BO', 'BP'], ['BQ', 'BR', 'BS'], ['BT', 'BU', 'BV'], ['BW', 'BX', 'BY'], ['BZ', 'CA', 'CB'],
        ['CC', 'CD', 'CE'], ['CF', 'CG', 'CH'], ['CI', 'CJ', 'CK'], ['CL', 'CM', 'CN'], ['CO', 'CP', 'CQ'],
        ['CR', 'CS', 'CT']
      ];

      $dailyPerfData = getDailyPerformanceReport($opt);

      $sheet = new Spreadsheet();
      $sheet->loadFile(FCPATH . 'files/templates/DailyPerformance_Report.xlsx');

      $sheet->setTitle('Period ' . $opt['period']);

      $r1 = 3; // 3rd row.

      foreach ($dailyPerfData as $dp) {
        $sheet->setCellValue('A' . $r1, $dp['biller']);
        $sheet->setCellValue('B' . $r1, $dp['target']);
        $sheet->setCellValue('C' . $r1, $dp['revenue']);
        $sheet->setCellValue('D' . $r1, $dp['avg_revenue']);
        $sheet->setCellValue('E' . $r1, $dp['forecast']);

        $r2 = 0;
        foreach ($dp['daily_data'] as $dd) {
          $sheet->setCellValue($ddGrid[$r2][0] . $r1, $dd['revenue']);
          $sheet->setCellValue($ddGrid[$r2][1] . $r1, $dd['stock_value']);
          $sheet->setCellValue($ddGrid[$r2][2] . $r1, $dd['piutang']);

          $r2++;
        }

        $r1++;
      }

      $last = $r1 - 1;

      $sheet->setCellValue('A' . $r1, 'GRAND TOTAL');
      $sheet->setCellValue('B' . $r1, "=SUM(B3:B{$last})");
      $sheet->setCellValue('C' . $r1, "=SUM(C3:C{$last})");
      $sheet->setCellValue('D' . $r1, "=SUM(D3:D{$last})");
      $sheet->setCellValue('E' . $r1, "=SUM(E3:E{$last})");

      $sheet->setBold('A' . $r1);

      $name = session('login')->fullname;

      $sheet->export('PrintERP-DailyPerformance-' . date('Ymd_His') . "-($name)");
    }
  }

  public function getDebts()
  {
    checkPermission('Report.Debt');

    $createdBy      = getPostGet('created_by');
    $status         = getPostGet('status');
    $paymentStatus  = getPostGet('payment_status');
    $supplier       = getPostGet('supplier');

    $startDate  = getPostGet('start_date');
    $endDate    = getPostGet('end_date');

    $dt = new DataTables('purchases');
    $dt->select("purchases.id, purchases.date, purchases.reference,
        (CASE
          WHEN suppliers.company IS NULL THEN suppliers.name
          ELSE CONCAT(suppliers.name, ' (', suppliers.company, ')')
        END) AS supplier_name,
        purchases.status, purchases.payment_status,
        purchases.grand_total, purchases.paid, purchases.balance, purchases.due_date,
        purchases.created_at, creator.fullname AS creator_name, purchases.attachment")
      ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
      ->join('users creator', 'creator.id = purchases.created_by', 'left')
      ->where('purchases.balance < 0')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/purchase/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('paid', function ($data) {
        return '<div class="float-right">' . formatNumber($data['paid']) . '</div>';
      })
      ->editColumn('balance', function ($data) {
        return '<div class="float-right">' . formatNumber($data['balance']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      });

    if ($createdBy) {
      $dt->whereIn('purchases.created_by', $createdBy);
    }

    if ($status) {
      $dt->whereIn('purchases.status', $status);
    }

    if ($paymentStatus) {
      $dt->whereIn('purchases.payment_status', $paymentStatus);
    }

    if ($supplier) {
      $dt->whereIn('purchases.supplier_id', $supplier);
    }

    if ($startDate) {
      $dt->where("purchases.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("purchases.date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  public function getIncomeStatements()
  {
    $billers    = getPostGet('biller');
    $startDate  = getPostGet('start_date');
    $endDate    = getPostGet('end_date');

    if (!$billers) { // Default biller to NOT Lucretai.
      $billers = [];
      $bills = Biller::select('*')->whereNotIn('code', ['LUC'])->where('active', 1)->get();

      foreach ($bills as $bill) {
        $billers[] = $bill->id;
      }
    }

    $opt = [
      'biller_id'   => $billers,
      'start_date'  => $startDate,
      'end_date'    => $endDate
    ];

    $is = getIncomeStatementReport($opt);

    if (!$is) {
      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(200, ['data' => $is]);
  }

  public function getInventoryBalances()
  {
    checkPermission('Report.InventoryBalance');

    $clausesBegin = '';
    $clauses = '';

    $categoryId  = getPostGet('category');
    $itemName    = getPostGet('item_name');
    $startDate   = getPostGet('start_date') ?? date('Y-m-') . '01';
    $endDate     = getPostGet('end_date') ?? date('Y-m-d');
    $warehouseId = getPostGet('warehouse');

    $lucretaiMode = false;
    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if ($warehouse && $warehouse->code == 'LUC') {
      $lucretaiMode = true;
    }

    if ($startDate) {
      $endDate = ($endDate ?? date('Y-m-d'));

      $clausesBegin .= "AND date < '{$startDate} 00:00:00'";
      $clauses .= "AND date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'";
    }

    if ($warehouseId) {
      if ($startDate) {
        $clausesBegin .= " AND warehouse_id = {$warehouseId}";
      }
      $clauses .= " AND warehouse_id = {$warehouseId}";
    } else { // Except Lucretia
      $clausesBegin .= " AND warehouse_code <> 'LUC'";
      $clauses .= " AND warehouse_code <> 'LUC'";
    }

    if ($categoryId) {
      if ($startDate) {
        $clausesBegin .= " AND category_id = {$categoryId}";
      }
      $clauses .= " AND category_id = {$categoryId}";
    }

    //* QUERIES
    $query = "products.id AS id,
      products.code AS product_code,
      products.name AS product_name,
      units.code AS product_unit,";

    //* QUERY BEGINNING
    if ($startDate) {
      $query .= "(COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0)) AS beginning,";
    } else {
      $query .= "'0' AS beginning,";
    }

    //* QUERY INCREASE
    $query .= "COALESCE(stock_recv.total, 0) AS increase,";

    //* QUERY DECREASE
    $query .= "COALESCE(stock_sent.total, 0) AS decrease,";

    //* QUERY BALANCE
    if ($startDate) {
      $query .= "(COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0) + COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS balance,";
    } else {
      $query .= "(COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS balance,";
    }

    //* QUERY COST / MARK-ON PRICE
    if ($lucretaiMode) { // If Lucretai mode.
      $query .= "products.cost AS cost,";
    } else {
      $query .= "products.markon_price AS cost,"; // All outlet except Lucretai.
    }

    //* QUERY STOCK VALUE
    $cost = ($lucretaiMode ? 'products.cost' : 'products.markon_price');
    if ($startDate) {
      $query .= "{$cost} * (COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0) + COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS stock_value";
    } else {
      $query .= "{$cost} * (COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS stock_value";
    }

    /* EXECUTE QUERIES */
    $dt = new DataTables('products');
    $dt->select($query);

    // JOIN BEGINNING
    if ($startDate) {
      $dt
        ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'received' {$clausesBegin} GROUP BY product_id) stock_begin_recv", 'stock_begin_recv.product_id = products.id', 'left')
        ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'sent' {$clausesBegin} GROUP BY product_id) stock_begin_sent", 'stock_begin_sent.product_id = products.id', 'left');
    }

    // JOIN INCREASE OR BALANCE
    $dt
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'received' {$clauses} GROUP BY product_id) stock_recv", 'stock_recv.product_id = products.id', 'left');

    // JOIN DECREASE OR BALANCE
    $dt
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'sent' {$clauses} GROUP BY product_id) stock_sent", 'stock_sent.product_id = products.id', 'left');

    // JOIN UNIT
    $dt
      ->join('units', 'units.id=products.unit', 'left');

    if ($itemName) {
      $dt
        ->groupStart()
        ->like("products.code", $itemName, 'both')
        ->orLike("products.name", $itemName, 'both')
        ->groupEnd();
    }

    if ($categoryId) {
      $dt->where("products.category_id", $categoryId);
    }

    $dt
      ->whereIn('products.type', ['standard']) // Standard only
      ->whereNotIn('products.category_id', [2, 14, 16, 17, 18]); // Not Assets and Sub-Assets.

    $dt->editColumn('id', function ($data) use ($warehouseId, $startDate, $endDate) {
      $p = '';

      if ($warehouseId) {
        $p .= '&warehouse=' . $warehouseId;
      }

      if ($startDate) {
        $p .= '&start_date=' . $startDate;
      }

      if ($endDate) {
        $p .= '&end_date=' . $endDate;
      }

      if (!empty($p)) {
        $p = '?' . $p;
      }

      return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url("inventory/product/history/{$data['id']}{$p}") . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
            </div>
          </div>';
    })->editColumn('beginning', function ($data) {
      return '<div class="float-right">' . formatNumber(round((float)$data['beginning'], 2)) . '</div>';
    })->editColumn('increase', function ($data) {
      return '<div class="float-right">' . formatNumber(round((float)$data['increase'], 2)) . '</div>';
    })->editColumn('decrease', function ($data) {
      return '<div class="float-right">' . formatNumber(round((float)$data['decrease'], 2)) . '</div>';
    })->editColumn('balance', function ($data) {
      return '<div class="float-right">' . formatNumber(round((float)$data['balance'], 2)) . '</div>';
    })->editColumn('cost', function ($data) {
      return '<div class="float-right">' . formatNumber($data['cost']) . '</div>';
    })->editColumn('stock_value', function ($data) {
      return '<div class="float-right">' . formatNumber(round((float)$data['stock_value'])) . '</div>';
    });

    $dt->generate();
  }

  public function getPayment()
  {
    checkPermission('Report.Payment');

    $bank       = getPostGet('bank');
    $biller     = getPostGet('biller');
    $createdBy  = getPostGet('created_by');
    $customer   = getPostGet('customer');
    $status     = getPostGet('status');

    $startDate  = getPostGet('start_date');
    $endDate    = getPostGet('end_date');

    $dt = new DataTables('payments');
    $dt->select("payments.id, payments.date, payments.reference_date, payments.reference,
        creator.fullname AS creator_name, biller.name AS biller_name,
        customers.name AS customer_name,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name, payments.method, payments.amount, payments.type, payments.note,
        payments.created_at, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->join('sales', 'sales.id = payments.sale_id', 'left')
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = payments.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('payment/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic2"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->editColumn('type', function ($data) {
        return renderStatus($data['type']);
      });


    $userJS = getJSON(session('login')?->json);

    if (isset($userJS->billers) && !empty($userJS->billers)) {
      if ($biller) {
        $biller = array_merge($biller, $userJS->billers);
      } else {
        $biller = $userJS->billers;
      }
    }

    if (session('login')->biller_id) {
      if ($biller) {
        $biller[] = session('login')->biller_id;
      } else {
        $biller = [session('login')->biller_id];
      }
    }

    if ($bank) {
      $dt->whereIn('payments.bank_id', $bank);
    }

    if ($biller) {
      $dt->whereIn('payments.biller_id', $biller);
    }

    if ($createdBy) {
      $dt->whereIn('payments.created_by', $createdBy);
    }

    if ($customer) {
      $dt->whereIn('customers.id', $customer);
    }

    if ($status) {
      $dt->whereIn('payments.type', $status);
    }

    if ($startDate) {
      $dt->where("payments.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("payments.date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  public function getReceivables()
  {
    checkPermission('Report.Receivable');

    $createdBy      = getPostGet('created_by');
    $status         = getPostGet('status');
    $paymentStatus  = getPostGet('payment_status');
    $customer       = getPostGet('customer');

    $startDate  = getPostGet('start_date');
    $endDate    = getPostGet('end_date');

    $dt = new DataTables('sales');
    $dt->select("sales.id, sales.date, sales.reference,
        (CASE
          WHEN customers.company IS NULL OR LENGTH(customers.company) = 0 THEN customers.name
          ELSE CONCAT(customers.name, ' (', customers.company, ')')
        END) AS customer_name,
        sales.status, sales.payment_status,
        sales.grand_total, sales.paid, sales.balance, sales.due_date,
        sales.created_at, creator.fullname AS creator_name, sales.attachment")
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = sales.created_by', 'left')
      ->where('sales.balance > 0')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('sale/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('paid', function ($data) {
        return '<div class="float-right">' . formatNumber($data['paid']) . '</div>';
      })
      ->editColumn('balance', function ($data) {
        return '<div class="float-right">' . formatNumber($data['balance']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      });

    if ($createdBy) {
      $dt->whereIn('sales.created_by', $createdBy);
    }

    if ($status) {
      $dt->whereIn('sales.status', $status);
    }

    if ($paymentStatus) {
      $dt->whereIn('sales.payment_status', $paymentStatus);
    }

    if ($customer) {
      $dt->whereIn('sales.customer_id', $customer);
    }

    if ($startDate) {
      $dt->where("sales.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("sales.date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  public function index()
  {
    echo "OK";
  }

  public static function callback($job, string $response)
  {
    $user = User::getRow(['id' => $job->created_by]);

    if (!$user) {
      return false;
    }

    if ($job->status == 'success') {
      $msg = '<a href="' . $response . '">' . $response . '</a>';

      Notification::add([
        'title'   => 'Export report',
        'note'    => 'Report has been created: ' . $msg,
        'scope'   => json_encode(['users' => [$user->id]]),
        'status'  => 'active'
      ]);

      WAJob::add(['phone' => $user->phone, 'message' => "Report has been created: {$response}."]);
    } else {
      WAJob::add(['phone' => $user->phone, 'message' => "*FAILED*: {$response}."]);
    }
  }

  /**
   * Debt from supplier.
   */
  public function debt()
  {
    checkPermission('Report.Debt');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.debt'), 'slug' => 'debt', 'url' => '#']
      ],
      'content' => 'Report/Debt/index',
      'title' => lang('App.debt')
    ];

    return $this->buildPage($this->data);
  }

  /**
   * Called by client side using POST ajax or fetch. JSON as data.
   */
  public function export(string $name = null)
  {
    $param = file_get_contents('php://input'); // JSON data.

    if (empty($name)) {
      $this->response(400, ['message' => 'Report name is required.']);
    }

    if (empty($param)) {
      $this->response(400, ['message' => 'Param is required.']);
    }

    $data = [
      'class'     => '\App\Controllers\Report::job_' . $name,
      'callback'  => '\App\Controllers\Report::callback',
      'param'     => $param
    ];

    $paramJS = getJSON($param);

    if ($paramJS) {
      if (!empty($paramJS->report_to)) {
        $user = User::getRow(['phone' => $paramJS->report_to]);

        if ($user) {
          $data['created_by'] = $user->id;
        }
      }
    }

    // PrintERP Job service 'printerp-job' must be run to running the export jobs.
    // Make sure 'systemctl status printerp-job' is running.
    $insertId = Jobs::add($data);

    if (!$insertId) {
      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(200, ['message' => 'Job report has been created.']);
  }

  public function incomestatement()
  {
    checkPermission('Report.IncomeStatement');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.incomestatement'), 'slug' => 'incomestatement', 'url' => '#']
      ],
      'content' => 'Report/IncomeStatement/index',
      'title' => lang('App.incomestatement')
    ];

    return $this->buildPage($this->data);
  }

  public function inventorybalance()
  {
    checkPermission('Report.InventoryBalance');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.inventorybalance'), 'slug' => 'inventorybalance', 'url' => '#']
      ],
      'content' => 'Report/InventoryBalance/index',
      'title' => lang('App.inventorybalance')
    ];

    return $this->buildPage($this->data);
  }

  // Called by service.
  public static function job_dailyPerformance(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $opt = [
      'period' => ($param->period ?? date('Y-m')) // Default current year and month.
    ];

    $ddGrid = [
      ['F', 'G', 'H'], ['I', 'J', 'K'], ['L', 'M', 'N'], ['O', 'P', 'Q'], ['R', 'S', 'T'],
      ['U', 'V', 'W'], ['X', 'Y', 'Z'], ['AA', 'AB', 'AC'], ['AD', 'AE', 'AF'], ['AG', 'AH', 'AI'],
      ['AJ', 'AK', 'AL'], ['AM', 'AN', 'AO'], ['AP', 'AQ', 'AR'], ['AS', 'AT', 'AU'], ['AV', 'AW', 'AX'],
      ['AY', 'AZ', 'BA'], ['BB', 'BC', 'BD'], ['BE', 'BF', 'BG'], ['BH', 'BI', 'BJ'], ['BK', 'BL', 'BM'],
      ['BN', 'BO', 'BP'], ['BQ', 'BR', 'BS'], ['BT', 'BU', 'BV'], ['BW', 'BX', 'BY'], ['BZ', 'CA', 'CB'],
      ['CC', 'CD', 'CE'], ['CF', 'CG', 'CH'], ['CI', 'CJ', 'CK'], ['CL', 'CM', 'CN'], ['CO', 'CP', 'CQ'],
      ['CR', 'CS', 'CT']
    ];

    $dailyPerfData = getDailyPerformanceReport($opt);

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/DailyPerformance_Report.xlsx');

    $sheet->setTitle('Period ' . $param->period);

    $r1 = 3; // 3rd row.

    foreach ($dailyPerfData as $dp) {
      $sheet->setCellValue('A' . $r1, $dp['biller']);
      $sheet->setCellValue('B' . $r1, $dp['target']);
      $sheet->setCellValue('C' . $r1, $dp['revenue']);
      $sheet->setCellValue('D' . $r1, $dp['avg_revenue']);
      $sheet->setCellValue('E' . $r1, $dp['forecast']);

      $r2 = 0;
      foreach ($dp['daily_data'] as $dd) {
        $sheet->setCellValue($ddGrid[$r2][0] . $r1, $dd['revenue']);
        $sheet->setCellValue($ddGrid[$r2][1] . $r1, $dd['stock_value']);
        $sheet->setCellValue($ddGrid[$r2][2] . $r1, $dd['piutang']);

        $r2++;
      }

      $r1++;
    }

    $last = $r1 - 1;

    $sheet->setCellValue('A' . $r1, 'GRAND TOTAL');
    $sheet->setCellValue('B' . $r1, "=SUM(B3:B{$last})");
    $sheet->setCellValue('C' . $r1, "=SUM(C3:C{$last})");
    $sheet->setCellValue('D' . $r1, "=SUM(D3:D{$last})");
    $sheet->setCellValue('E' . $r1, "=SUM(E3:E{$last})");

    $sheet->setBold('A' . $r1);

    return $sheet->export('PrintERP-DailyPerformance-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_debt(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $q = DB::table('purchases')
      ->select("purchases.id, purchases.date, purchases.reference,
      (CASE
        WHEN suppliers.company IS NULL THEN suppliers.name
        ELSE CONCAT(suppliers.name, ' (', suppliers.company, ')')
      END) AS supplier_name,
      purchases.status, purchases.payment_status,
      purchases.grand_total, purchases.paid, purchases.balance, purchases.due_date,
      purchases.created_at, creator.fullname AS creator_name, purchases.attachment")
      ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
      ->join('users creator', 'creator.id = purchases.created_by', 'left')
      ->where('purchases.balance < 0');

    if (!empty($param->created_by)) {
      $q->whereIn('purchases.created_by', $param->created_by);
    }

    if (!empty($param->supplier)) {
      $q->whereIn('purchases.supplier_id', $param->supplier);
    }

    if (!empty($param->status)) {
      $q->whereIn('purchases.status', $param->status);
    }

    if (!empty($param->payment_status)) {
      $q->whereIn('purchases.payment_status', $param->payment_status);
    }

    if (!empty($param->start_date)) {
      $q->where("purchases.date >= '{$param->start_date} 00:00:00'");
    }

    if (!empty($param->end_date)) {
      $q->where("purchases.date <= '{$param->end_date} 23:59:59'");
    }

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/Debt_Report.xlsx');
    $sheet->setTitle('Debt Report');

    $r = 2;

    foreach ($q->get() as $purchase) {
      $sheet->setCellValue('A' . $r, $purchase->date);
      $sheet->setCellValue('B' . $r, $purchase->reference);
      $sheet->setCellValue('C' . $r, $purchase->supplier_name);
      $sheet->setCellValue('D' . $r, $purchase->status);
      $sheet->setCellValue('E' . $r, $purchase->payment_status);
      $sheet->setCellValue('F' . $r, $purchase->grand_total);
      $sheet->setCellValue('G' . $r, $purchase->paid);
      $sheet->setCellValue('H' . $r, $purchase->balance);
      $sheet->setCellValue('I' . $r, $purchase->due_date);
      $sheet->setCellValue('J' . $r, $purchase->created_at);
      $sheet->setCellValue('K' . $r, $purchase->creator_name);

      if ($purchase->attachment) {
        $sheet->setCellValue('L' . $r, lang('App.view'));
        $sheet->setUrl('L' . $r, 'https://erp.indoprinting.co.id/attachment/' . $purchase->attachment);
      }

      $r++;
    }

    return $sheet->export('PrintERP-DebtReport-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_incomestatement(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $startDate  = null;
    $endDate    = null;

    if (!empty($param->biller)) {
      $billers = $param->biller;
    } else { // Default biller to NOT Lucretai.
      $billers = [];
      $bills = Biller::select('*')->whereNotIn('code', ['LUC'])->where('active', 1)->get();

      foreach ($bills as $bill) {
        $billers[] = $bill->id;
      }
    }

    if (!empty($param->start_date)) {
      $startDate = $param->start_date;
    }

    if (!empty($param->end_date)) {
      $endDate = $param->end_date;
    }

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/IncomeStatement_Report.xlsx');
    $sheet->setTitle('Income Statement');

    $incomeStatementSheet = [];

    foreach ($billers as $billerId) {
      $biller = Biller::getRow(['id' => $billerId]);

      $incomeStatementSheet[] = [
        'biller' => $biller->name,
        'data' => getIncomeStatementReport([
          'biller_id'  => [$billerId],
          'start_date' => $startDate,
          'end_date'   => $endDate
        ])
      ];
    }

    $r = 2;

    // Vertical Columns First.
    foreach ($incomeStatementSheet[0]['data'] as $is) {
      $sheet->setCellValue('A' . $r, $is['name']);
      $sheet->setBold('A' . $r);

      if (!empty($is['data']) && is_array($is['data'])) {
        foreach ($is['data'] as $subData) {
          $r++;

          $sheet->setCellValue('A' . $r, "--> " . $subData['name']);
        }
      }

      $r++;
    }

    $sheet->setColumnAutoWidth('A');

    $col = 66; // 66 = B

    foreach ($incomeStatementSheet as $iss) {
      $r = 2;

      $sheet->setCellValue(chr($col) . ($r - 1), $iss['biller']);
      $sheet->setBold(chr($col) . ($r - 1));

      foreach ($iss['data'] as $is) {
        $sheet->setCellValue(chr($col) . $r, round($is['amount']));

        if (!empty($is['data']) && is_array($is['data'])) {
          foreach ($is['data'] as $subData) {
            $r++;

            $sheet->setCellValue(chr($col) . $r, round($subData['amount']));
          }
        }

        $r++;
      }

      $sheet->setColumnAutoWidth(chr($col)); // B, C, D, ...

      $col++;
    }

    return $sheet->export('PrintERP-IncomeStatementReport-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_inventorybalance(string $response = null)
  {
    $param = getJSON($response);

    $clausesBegin = '';
    $clauses = '';

    $categoryId   = ($param->category ?? null);
    $itemName     = ($param->item_name ?? null);
    $warehouseId  = ($param->warehouse ?? null);
    $whSummary    = ($param->warehouse_summary ?? null);
    $startDate    = ($param->start_date ?? null);
    $endDate      = ($param->end_date ?? null);

    $lucretaiMode = false;
    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if ($warehouse && $warehouse->code == 'LUC') {
      $lucretaiMode = true;
    }

    if ($startDate) {
      $endDate = ($endDate ?? date('Y-m-d'));

      $clausesBegin .= "AND date < '{$startDate} 00:00:00'";
      $clauses .= "AND date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'";
    }

    if ($warehouseId) {
      if ($startDate) {
        $clausesBegin .= " AND warehouse_id = {$warehouseId}";
      }

      $clauses .= " AND warehouse_id = {$warehouseId}";
    } else { // Except Lucretia
      $clausesBegin .= " AND warehouse_code <> 'LUC'";
      $clauses .= " AND warehouse_code <> 'LUC'";
    }

    if ($categoryId) {
      if ($startDate) {
        $clausesBegin .= " AND category_id = {$categoryId}";
      }

      $clauses .= " AND category_id = {$categoryId}";
    }

    if ($whSummary) { // Warehouse Summary Detail
      $data = [];
      $warehouses = Warehouse::get(['active' => 1]);

      $products = Product::get(['active' => 1, 'type' => 'standard']); // Standard only.

      foreach ($warehouses as $warehouse) {
        if ($warehouse->code == 'ADV')  continue;
        if ($warehouse->active != 1)    continue;

        $totalCost = 0;

        foreach ($products as $product) {
          if ($product->category_id == 2) continue; // No asset(2).

          $incQty = 0;
          $decQty = 0;
          $itemCost = 0;

          $stocks = Stock::get(['product_id' => $product->id, 'warehouse_id' => $warehouse->id]);

          foreach ($stocks as $stock) {
            if ($stock->status == 'received') {
              $incQty += $stock->quantity;
            } else if ($stock->status == 'sent') {
              $decQty += $stock->quantity;
            }
          }

          $totalQty = $incQty - $decQty;

          if ($warehouse->code == 'LUC') {
            $itemCost  = $totalQty * $product->cost;
          } else {
            $itemCost  = $totalQty * $product->markon_price;
          }

          $totalCost += $itemCost;
        }

        $data[] = [
          'warehouse_name' => $warehouse->name,
          'cost' => round($totalCost)
        ];
      }

      if ($data) {
        $sheet = new Spreadsheet();
        $sheet->setTitle('Warehouse Summary Details');

        $sheet->setCellValue('A1', 'Warehouses');
        $sheet->setCellValue('B1', 'Total Cost');
        $sheet->setBold('A1:B1');

        $row = 2;

        foreach ($data as $wh_data) {
          $sheet->setCellValue('A' . $row, $wh_data['warehouse_name']);
          $sheet->setCellValue('B' . $row, $wh_data['cost']);

          $row++;
        }

        $sheet->setColumnAutoWidth('A');
        $sheet->setColumnAutoWidth('B');

        return $sheet->export('PrintERP-InventoryBalanceReport-WarehouseSummary-' . date('Ymd_His'));
      }
    } else { // Item Details
      //* QUERIES
      $query = "products.id AS product_id,
        products.code AS product_code,
        products.name AS product_name,
        units.code AS product_unit,
        categories.name AS category_name, products.type AS product_type, products.iuse_type AS iuse_type,";

      //* QUERY BEGINNING
      if ($startDate) {
        $query .= "(COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0)) AS beginning,";
      } else {
        $query .= "'0' AS beginning,";
      }

      //* QUERY INCREASE
      $query .= "COALESCE(stock_recv.total, 0) AS increase,";

      //* QUERY DECREASE
      $query .= "COALESCE(stock_sent.total, 0) AS decrease,";

      //* QUERY BALANCE
      if ($startDate) {
        $query .= "(COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0) + COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS balance,";
      } else {
        $query .= "(COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS balance,";
      }

      //* QUERY AVG COST / MARK-ON PRICE
      if ($lucretaiMode) { // If Lucretai mode.
        $query .= "products.cost AS new_cost,";
        // $query .= "products.avg_cost AS new_cost,";
      } else {
        $query .= "products.markon_price AS new_cost,"; // All outlet except Lucretai.
      }

      //* QUERY STOCK VALUE
      $cost = ($lucretaiMode ? 'products.cost' : 'products.markon_price');

      if ($startDate) {
        $query .= "{$cost} * (COALESCE(stock_begin_recv.total, 0) - COALESCE(stock_begin_sent.total, 0) + COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS stock_value";
      } else {
        $query .= "{$cost} * (COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS stock_value";
      }

      /* EXECUTE QUERIES */
      $q = Product::select($query);

      // JOIN BEGINNING
      if ($startDate) {
        $q
          ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'received' {$clausesBegin} GROUP BY product_id) stock_begin_recv", 'stock_begin_recv.product_id = products.id', 'left')
          ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'sent' {$clausesBegin} GROUP BY product_id) stock_begin_sent", 'stock_begin_sent.product_id = products.id', 'left');
      }

      // JOIN INCREASE OR BALANCE
      $q
        ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'received' {$clauses} GROUP BY product_id) stock_recv", 'stock_recv.product_id = products.id', 'left');

      // JOIN DECREASE OR BALANCE
      $q
        ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks WHERE status LIKE 'sent' {$clauses} GROUP BY product_id) stock_sent", 'stock_sent.product_id = products.id', 'left');

      // JOIN UNIT
      $q
        ->join('units', 'units.id=products.unit', 'left');

      // JOIN CATEGORY
      $q
        ->join('categories', 'categories.id = products.category_id', 'left');

      if ($itemName) {
        $q
          ->groupStart()
          ->like('products.code', $itemName, 'both')
          ->orLike('products.name', $itemName, 'both')
          ->groupEnd();
      }

      if ($categoryId) {
        $q->where('products.category_id', $categoryId);
      }

      $q
        ->whereIn('products.type', ['standard'])
        ->whereNotIn('products.category_id', [2, 14, 16, 17, 18]); // No Assets and Sub-Assets.

      $rows = $q->get();

      $sheet = new Spreadsheet();
      $sheet->setTitle('Inventory Balance');

      if ($rows) {
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Product Code');
        $sheet->setCellValue('C1', 'Produt Name');
        $sheet->setCellValue('D1', 'Unit');
        $sheet->setCellValue('E1', 'Category');
        $sheet->setCellValue('F1', 'Type');
        $sheet->setCellValue('G1', 'Internal Use Type');
        $sheet->setCellValue('H1', 'Beginning');
        $sheet->setCellValue('I1', 'Increase');
        $sheet->setCellValue('J1', 'Decrease');
        $sheet->setCellValue('K1', 'Balance');
        $sheet->setCellValue('L1', 'Purchase Cost');
        $sheet->setCellValue('M1', 'Stock Value');

        $r = 2;

        foreach ($rows as $row) {
          $sheet->setCellValue('A' . $r, $row->product_id);
          $sheet->setCellValue('B' . $r, $row->product_code);
          $sheet->setCellValue('C' . $r, $row->product_name);
          $sheet->setCellValue('D' . $r, $row->product_unit);
          $sheet->setCellValue('E' . $r, $row->category_name);
          $sheet->setCellValue('F' . $r, $row->product_type);
          $sheet->setCellValue('G' . $r, $row->iuse_type);
          $sheet->setCellValue('H' . $r, $row->beginning);
          $sheet->setCellValue('I' . $r, $row->increase);
          $sheet->setCellValue('J' . $r, $row->decrease);
          $sheet->setCellValue('K' . $r, $row->balance);
          $sheet->setCellValue('L' . $r, ceil(filterNumber($row->new_cost)));
          $sheet->setCellValue('M' . $r, ceil(filterNumber($row->stock_value)));

          $r++;
        }
      }

      return $sheet->export('PrintERP-InventoryBalanceReport-' . date('Ymd_His'));
    }
  }

  // Called by service.
  public static function job_machine(string $response = null)
  {
    $param  = getJSON($response);

    $startDate  = ($param->start_date ?? date('Y-m-') . '01');
    $endDate    = ($param->end_date ?? date('Y-m-d'));
    $whIds      = ($param->warehouse ?? []);

    $whNames = [];

    if ($whIds) {
      foreach ($whIds as $whId) {
        $warehouse = Warehouse::getRow(['id' => $whId]);

        if ($warehouse) {
          $whNames[] = $warehouse->name;
        }
      }
    }

    $q = DB::table('products')
      ->select("products.id AS product_id, products.code AS product_code, products.name AS product_name,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.sn')) AS sn,
        categories.name AS category_name,
        subcategories.name AS subcategory_name,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.assigned_at')) AS assigned_at,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.priority')) AS priority,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.order_date')) AS order_date,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.order_price')) AS order_price,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.maintenance_qty')) AS maintenance_qty,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.maintenance_cost')) AS maintenance_cost,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.disposal_date')) AS disposal_date,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.disposal_price')) AS disposal_price,
        products.active AS active,
        products.warehouses AS warehouses,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.condition')) AS last_condition,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.note')) AS note,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.pic_note')) AS pic_note,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.updated_at')) AS last_update,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.purchased_at')) AS purchased_at,
        pic.fullname AS pic_name,,
        creator.fullname AS creator_name")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->join('categories AS subcategories', 'subcategories.id = products.subcategory_id', 'left')
      ->join('users AS creator', "creator.id = JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.updated_by'))", 'left')
      ->join('users AS pic', "pic.id = JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.pic_id'))", 'left')
      ->groupStart()
      ->like('categories.code', 'AST', 'none')
      ->orLike('categories.code', 'EQUIP', 'none')
      ->groupEnd();

    if ($whNames) {
      $q->whereIn('products.warehouses', $whNames);
    }

    $assets = $q->get();

    // Summary Report (TAKETOOLONGTIME)
    $A1DateGrid = [
      null, 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
      'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL'
    ];

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/Machine_Report.xlsx');

    $sheet->getSheetByName('Sheet1');
    $sheet->setTitle('Summary Report');
    $sheet->setCellValue('A1', date('F Y', strtotime($startDate)));

    $warehouses = Warehouse::get(['active' => 1]);
    $pg = 10000; // Penalty

    $r = 4;
    $lastDate = intval(date('j', strtotime($endDate)));

    foreach ($warehouses as $wh) {
      if ($wh->active == 0) continue;
      if ($wh->code == 'ADV') continue; // No ADV warehouse please.
      // if ($wh->code == 'IDSLOS') continue; // No IDS.
      // if ($wh->code == 'IDSUNG') continue; // No IDS.

      $sheet->setCellValue('A' . $r, $wh->name);
      $sheet->setCellValue('C' . $r, "=COUNTIF(H{$r}:AL{$r},\"X\")");
      $sheet->setCellValue('D' . $r, "=COUNTIF(H{$r}:AL{$r},\"P\")");
      $sheet->setCellValue('E' . $r, "=C{$r}+D{$r}");
      $sheet->setCellValue('F' . $r, "=IF(E{$r}>0,E{$r}*-{$pg},(\$E\$1*{$pg})/(LEFT(\$B\$2,SEARCH(\":\",\$B\$2)-1)))");

      for ($x = 1; $x <= $lastDate; $x++) {
        $items = [];
        $dayCode = date('D', strtotime(date('Y-m-', strtotime($endDate)) . $x));

        // No checked for other except DUR, FAT, TEM and UNG. Don't let them checked partially.
        if ($dayCode == 'Sun') { // Sunday = Ahad
          // d(date('Y-m-d D', strtotime(date('Y-m-', strtotime($endDate)) . $x))); die();
          if ($wh->code != 'DUR' && $wh->code != 'FAT' && $wh->code != 'TEM' && $wh->code != 'UNG') {
            continue;
          }
        }

        $reports = ProductReport::select('*')->where('warehouse_id', $wh->id)
          ->where("created_at BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
          ->get();

        foreach ($assets as $asset) { // Filter items first.
          if ($asset->active == 0) continue;
          if (strcasecmp($asset->warehouses, $wh->name) !== 0) continue;

          $items[] = $asset;
        }

        $checkCount = 0;
        $filteredItems = [];

        foreach ($items as $item) {
          $isNewItem = false; // New item is not allowed.

          if (!empty($item->purchased_at)) {
            $isNewItem = (date('j', strtotime($item->purchased_at)) > $x ? true : false);
          }

          foreach ($reports as $report) {
            $isTimeEqual = (date('j', strtotime($report->created_at)) == $x);
            $needCheck   = ($isTimeEqual && !$isNewItem);

            if ($report->product_id == $item->product_id && $needCheck) {
              $checkCount++;
              break;
            }
          }

          if (!$isNewItem) $filteredItems[] = $item;
        }

        $itemTotal = count($filteredItems);

        if (!$checkCount) {
          $sheet->setCellValue($A1DateGrid[$x] . $r, 'X'); // Not checked.
        } else if ($itemTotal == $checkCount) {
          $sheet->setCellValue($A1DateGrid[$x] . $r, 'V'); // Fully checked.
        } else {
          // $sheet->setCellValue($A1DateGrid[$x] . $r, 'P'); // Partial checked.
          $sheet->setCellValue($A1DateGrid[$x] . $r, 'V'); // Partial checked.
        }
      }

      $r++;
    }

    // Support Performance
    $sheet->getSheetByName('Sheet2');
    $sheet->setTitle('Support Performance');

    $sheet->setCellValue('A1', date('F Y', strtotime($startDate)));

    $users  = User::get(['active' => 1, 'group_id' => UserGroup::getRow(['name' => 'TECHSUPPORT'])->id]);

    $r = 4;

    foreach ($users as $user) {
      $overTime = 0;

      foreach ($assets as $asset) {
        if ($asset->active != 1) continue;

        if ($asset->pic_name) {
          if (strcasecmp($user->fullname, $asset->pic_name) != 0) continue; // Error #2
        }

        if (empty($asset->assigned_at)) {
          log_message('notice', "Reports::getSupportPerformance(): Machine {$asset->product_code} doesn't have assigned date.");
          continue;
        }

        if (!empty($asset->pic_note)) {
          continue; // Good no PG boss!
        }

        // I think you got fucked here!
        if (getDaysInPeriod($asset->assigned_at, date('Y-m-d H:i:s')) > 2) $overTime++;
      }

      $sheet->setCellValue('A' . $r, $user->fullname);
      $sheet->setCellValue('C' . $r, $overTime);
      $sheet->setCellValue('D' . $r, "=IF(C{$r}>0,C{$r}*-{$pg},(\$C\$1*{$pg})/(LEFT(\$B\$2,SEARCH(\":\",\$B\$2)-1)))");

      $r++;
    }

    // Machine Report (PASSED)
    $sheet->getSheetByName('Sheet3');
    $sheet->setTitle('Machine Report');

    $r = 2;

    foreach ($assets as $asset) {
      $reportBegin = '';
      $reportEnd = date('Y-m-d H:i:s');

      if (!empty($asset->assigned_at)) { // If TS assigned, use assigned at as begin report date.
        $reportBegin = $asset->assigned_at;
      }

      $duration = ($reportBegin && $reportEnd ? getDaysInPeriod($reportBegin, $reportEnd) : '-');
      // if ($duration < 0) $duration = -1;

      $sheet->setCellValue('A' . $r, $r - 1);
      $sheet->setCellValue('B' . $r, $asset->product_code);
      $sheet->setCellValue('C' . $r, $asset->product_name);
      $sheet->setCellValue('D' . $r, $asset->sn);
      $sheet->setCellValue('E' . $r, $asset->category_name);
      $sheet->setCellValue('F' . $r, $asset->subcategory_name);
      $sheet->setCellValue('G' . $r, $asset->priority);
      $sheet->setCellValue('H' . $r, $asset->order_date);
      $sheet->setCellValue('I' . $r, $asset->order_price);
      $sheet->setCellValue('J' . $r, $asset->disposal_date);
      $sheet->setCellValue('K' . $r, $asset->disposal_price);
      $sheet->setCellValue('L' . $r, ($asset->active ? 'Active' : 'Inactive'));
      $sheet->setCellValue('M' . $r, $asset->warehouses);
      $sheet->setCellValue('N' . $r, $asset->maintenance_qty);
      $sheet->setCellValue('O' . $r, $asset->maintenance_cost);
      $sheet->setCellValue('P' . $r, ($asset->last_condition ? lang('Status.' . $asset->last_condition) : ''));
      $sheet->setCellValue('Q' . $r, $asset->creator_name);
      $sheet->setCellValue('R' . $r, htmlRemove($asset->note));
      $sheet->setCellValue('S' . $r, $asset->last_update);
      $sheet->setCellValue('T' . $r, $asset->assigned_at);
      $sheet->setCellValue('U' . $r, $asset->pic_name);
      $sheet->setCellValue('V' . $r, htmlRemove($asset->pic_note ?? ''));
      $sheet->setCellValue('W' . $r, $duration); // Duration in days

      $colorStatus = null;

      switch ($asset->last_condition) {
        case 'good':
          $colorStatus = '00FF00';
          break;
        case 'off':
          $colorStatus = 'FF0000';
          break;
        case 'trouble':
          $colorStatus = 'FF8000';
      }

      if ($colorStatus) {
        $sheet->setFillColor('P' . $r, $colorStatus);
      }

      $r++;
    }

    // $sheet->setColumnAutoWidth('A');
    $sheet->setColumnAutoWidth('B');
    // $sheet->setColumnAutoWidth('C');
    $sheet->setColumnAutoWidth('D');
    $sheet->setColumnAutoWidth('E');
    $sheet->setColumnAutoWidth('F');
    $sheet->setColumnAutoWidth('G');
    $sheet->setColumnAutoWidth('H');
    $sheet->setColumnAutoWidth('I');
    $sheet->setColumnAutoWidth('J');
    $sheet->setColumnAutoWidth('K');
    $sheet->setColumnAutoWidth('L');
    $sheet->setColumnAutoWidth('M');
    $sheet->setColumnAutoWidth('N');
    $sheet->setColumnAutoWidth('O');
    $sheet->setColumnAutoWidth('P');
    $sheet->setColumnAutoWidth('Q');
    // $sheet->setColumnAutoWidth('R');
    $sheet->setColumnAutoWidth('S');
    $sheet->setColumnAutoWidth('T');
    $sheet->setColumnAutoWidth('U');
    // $sheet->setColumnAutoWidth('V');
    $sheet->setColumnAutoWidth('W');

    // Maintenance Logs (PASSED)
    $mtLogs = MaintenanceLog::select('*')
      ->where("created_at BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->get();

    $sheet->getSheetByName('Sheet4');
    $sheet->setTitle('Maintenance Logs');

    $r = 2;

    foreach ($mtLogs as $mtLog) {
      if (!$mtLog->assigned_by) $mtLog->assigned_by = 0;
      if (!$mtLog->pic_id) $mtLog->pic_id = 0;

      $assigner = User::getRow(['id' => $mtLog->assigned_by]);
      $pic      = User::getRow(['id' => $mtLog->pic_id]);
      $loc      = Warehouse::getRow(['id' => $mtLog->warehouse_id]);
      $item     = Product::getRow(['id' => $mtLog->product_id]);

      $sheet->setCellValue('A' . $r, $item->code);
      $sheet->setCellValue('B' . $r, $item->name);
      $sheet->setCellValue('C' . $r, $mtLog->assigned_at);
      $sheet->setCellValue('D' . $r, ($assigner ? $assigner->fullname : ''));
      $sheet->setCellValue('E' . $r, $mtLog->fixed_at);
      $sheet->setCellValue('F' . $r, ($pic ? $pic->fullname : ''));
      $sheet->setCellValue('G' . $r, ($loc ? $loc->name : ''));
      $sheet->setCellValue('H' . $r, htmlRemove($mtLog->note));
      $sheet->setCellValue('I' . $r, htmlRemove($mtLog->pic_note));

      $r++;
    }

    $sheet->getSheet(0);
    $sheet->setCellValue('A1', date('F Y', strtotime($startDate)));
    $sheet->setBold('A1');

    return $sheet->export('PrintERP-MachineReport-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_payment(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $q = DB::table('payments')
      ->select("payments.id, payments.date, payments.reference_date, payments.reference,
        creator.fullname AS creator_name, biller.name AS biller_name,
        customers.name AS customer_name,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name, payments.method, payments.amount, payments.type, payments.note,
        payments.created_at, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->join('sales', 'sales.id = payments.sale_id', 'left')
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = payments.created_by', 'left');

    if (!empty($param->bank)) {
      $q->whereIn('payments.bank_id', $param->bank);
    }

    if (!empty($param->biller)) {
      $q->whereIn('payments.biller_id', $param->biller);
    }

    if (!empty($param->created_by)) {
      $q->whereIn('payments.created_by', $param->created_by);
    }

    if (!empty($param->customer)) {
      $q->whereIn('customers.id', $param->customer);
    }

    if (!empty($param->status)) {
      $q->whereIn('payments.status', $param->status);
    }

    if (!empty($param->start_date)) {
      $q->where("payments.date >= '{$param->start_date} 00:00:00'");
    }

    if (!empty($param->end_date)) {
      $q->where("payments.date <= '{$param->end_date} 23:59:59'");
    }

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/Payment_Report.xlsx');
    $sheet->setTitle('Payment Report');

    $r = 2;

    foreach ($q->get() as $payment) {
      $sheet->setCellValue('A' . $r, $payment->date);
      $sheet->setCellValue('B' . $r, $payment->reference_date);
      $sheet->setCellValue('C' . $r, $payment->reference);
      $sheet->setCellValue('D' . $r, $payment->creator_name);
      $sheet->setCellValue('E' . $r, $payment->biller_name);
      $sheet->setCellValue('F' . $r, $payment->customer_name);
      $sheet->setCellValue('G' . $r, $payment->bank_name);
      $sheet->setCellValue('H' . $r, $payment->method);
      $sheet->setCellValue('I' . $r, $payment->amount);
      $sheet->setCellValue('J' . $r, $payment->type);
      $sheet->setCellValue('K' . $r, html2Note($payment->note));
      $sheet->setCellValue('L' . $r, $payment->created_at);

      if ($payment->attachment) {
        $sheet->setCellValue('M' . $r, lang('App.view'));
        $sheet->setUrl('M' . $r, 'https://erp.indoprinting.co.id/attachment/' . $payment->attachment);
      }

      $r++;
    }

    return $sheet->export('PrintERP-PaymentReport-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_receivable(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $q = DB::table('sales')
      ->select("sales.id, sales.date, sales.reference,
      (CASE
        WHEN customers.company IS NULL OR LENGTH(customers.company) = 0 THEN customers.name
        ELSE CONCAT(customers.name, ' (', customers.company, ')')
      END) AS supplier_name,
      sales.status, sales.payment_status,
      sales.grand_total, sales.paid, sales.balance, sales.due_date,
      sales.created_at, creator.fullname AS creator_name, sales.attachment")
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = sales.created_by', 'left')
      ->where('sales.balance > 0');

    if (!empty($param->created_by)) {
      $q->whereIn('sales.created_by', $param->created_by);
    }

    if (!empty($param->supplier)) {
      $q->whereIn('sales.supplier_id', $param->supplier);
    }

    if (!empty($param->status)) {
      $q->whereIn('sales.status', $param->status);
    }

    if (!empty($param->payment_status)) {
      $q->whereIn('sales.payment_status', $param->payment_status);
    }

    if (!empty($param->start_date)) {
      $q->where("sales.date >= '{$param->start_date} 00:00:00'");
    }

    if (!empty($param->end_date)) {
      $q->where("sales.date <= '{$param->end_date} 23:59:59'");
    }

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/Receivable_Report.xlsx');
    $sheet->setTitle('Receivable Report');

    $r = 2;

    foreach ($q->get() as $sale) {
      $sheet->setCellValue('A' . $r, $sale->date);
      $sheet->setCellValue('B' . $r, $sale->reference);
      $sheet->setCellValue('C' . $r, $sale->supplier_name);
      $sheet->setCellValue('D' . $r, $sale->status);
      $sheet->setCellValue('E' . $r, $sale->payment_status);
      $sheet->setCellValue('F' . $r, $sale->grand_total);
      $sheet->setCellValue('G' . $r, $sale->paid);
      $sheet->setCellValue('H' . $r, $sale->balance);
      $sheet->setCellValue('I' . $r, $sale->due_date);
      $sheet->setCellValue('J' . $r, $sale->created_at);
      $sheet->setCellValue('K' . $r, $sale->creator_name);

      if ($sale->attachment) {
        $sheet->setCellValue('L' . $r, lang('App.view'));
        $sheet->setUrl('L' . $r, 'https://erp.indoprinting.co.id/attachment/' . $sale->attachment);
      }

      $r++;
    }

    return $sheet->export('PrintERP-ReceivableReport-' . date('Ymd_His'));
  }

  public function payment()
  {
    checkPermission('Report.Payment');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.payment'), 'slug' => 'payment', 'url' => '#']
      ],
      'content' => 'Report/Payment/index',
      'title' => lang('App.payment')
    ];

    return $this->buildPage($this->data);
  }

  public function printerp()
  {
    checkPermission('Report.PrintERP');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => 'PrintERP', 'slug' => 'printerp', 'url' => '#']
      ],
      'content' => 'Report/PrintERP/index',
      'title' => 'PrintERP'
    ];

    return $this->buildPage($this->data);
  }

  /**
   * Receivable from customers.
   */
  public function receivable()
  {
    checkPermission('Report.Receivable');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.receivable'), 'slug' => 'receivable', 'url' => '#']
      ],
      'content' => 'Report/Receivable/index',
      'title' => lang('App.receivable')
    ];

    return $this->buildPage($this->data);
  }
}
