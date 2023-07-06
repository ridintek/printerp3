<?php

declare(strict_types=1);

use App\Models\{
  Activity,
  Auth,
  Biller,
  ComboItem,
  Customer,
  CustomerGroup,
  DB,
  Expense,
  ExpenseCategory,
  Income,
  IncomeCategory,
  InternalUse,
  Payment,
  PaymentValidation,
  Product,
  ProductPurchase,
  ProductTransfer,
  Sale,
  SaleItem,
  Stock,
  StockOpname,
  User,
  Voucher,
  Warehouse,
  WarehouseProduct
};
use Config\Services;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

/**
 * Add new activity.
 * @param string $data Activity data.
 * @param array $json JSON data.
 */
function addActivity(string $data, array $json = [])
{
  $ip = Services::request()->getIPAddress();
  $ua = Services::request()->getUserAgent();

  $data = [
    'data'        => $data,
    'ip_address'  => $ip,
    'user_agent'  => $ua
  ];

  if ($json) {
    $data['json'] = json_encode($json);
  }

  return Activity::add($data);
}

/**
 * Convert Biller ID to Warehouse ID. See warehouseToBiller.
 * @param int|array $billerId Biller ID. It can be biller id or array of biller id.
 * @return int|array|null Return Warehouse ID. Return null if error.
 */
function billerToWarehouse($billerId)
{
  if (gettype($billerId) == 'array') {
    $data = [];

    foreach ($billerId as $biller_id) {
      $biller = Biller::getRow(['id' => $biller_id]);

      if ($biller) {
        $warehouse = Warehouse::getRow(['code' => $biller->code]);

        if ($warehouse) {
          $data[] = $warehouse->id;
        }
      }
    }

    return $data;
  } else {
    $biller = Biller::getRow(['id' => $billerId]);

    if ($biller) {
      $warehouse = Warehouse::getRow(['code' => $biller->code]);

      if ($warehouse) {
        return $warehouse->id;
      }
    }
  }

  return null;
}

/**
 * Check for permission and login status.
 * @param string $permission Permission to check. Ex. "User.View". If null it will check for login session.
 */
function checkPermission(string $permission = null)
{
  $request = Services::request();
  $ajax   = $request->isAJAX();

  if (isLoggedIn()) {
    if ($permission) {
      if ($ajax) {
        if (!hasAccess($permission)) {
          http_response_code(403);
          sendJSON(['code' => 403, 'message' => lang('Msg.notAuthorized'), 'title' => lang('Msg.accessDenied')]);
        }
      }

      if (!hasAccess($permission)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url()));
        die;
      }
    }
  } else {
    if ($ajax) {
      http_response_code(401);
      sendJSON(['code' => 401, 'message' => lang('Msg.notLoggedIn'), 'title' => lang('Msg.accessDenied')]);
    } else {
      $data = [
        'resver' => '1.0'
      ];

      if (!isLoggedIn() && getCookie('___')) {
        if (Auth::loginRememberMe(getCookie('___'))) {
          header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
      }

      echo view('Auth/login', $data);
      die;
    }
  }
}

/**
 * Convert PHP time to JS time.
 * @param string $dateTime dateTime.
 * @param int $currentDate Return current date if dateTime is empty.
 */
function dateTimeJS(string $dateTime = null, bool $currentDate = true)
{
  if ($currentDate && empty($dateTime)) {
    $dateTime = date('Y-m-d H:i:s');
  }

  if (empty($dateTime)) {
    return null;
  }

  return str_replace(' ', 'T', $dateTime);
}

/**
 * Convert JS time to PHP time.
 * @param string $dateTime dateTime.
 * @param int $currentDate Return current date if dateTime is empty.
 */
function dateTimePHP(string $dateTime = null, bool $currentDate = true)
{
  if ($currentDate && empty($dateTime)) {
    $dateTime = date('Y-m-d H:i:s');
  }

  if (empty($dateTime)) {
    return null;
  }

  return str_replace('T', ' ', $dateTime);
}

/**
 * Print debug output.
 */
function dbgprint()
{
  $args = func_get_args();

  foreach ($args as $arg) {
    $str = print_r($arg, true);
    echo ('<pre>');
    echo ($str);
    echo ('</pre>');
  }
}

function dispatchW2PSale($saleId = null)
{
  $curl = curl_init('https://admin.indoprinting.co.id/api/v1/printerp-sales');
  $key = 'g4Jlk3cILfITrbN74kwFHD1p9R3v15lmuLU_l3N9k4psUd4hD3rltAL03';
  $res = '';

  if ($sale = Sale::getRow(['id' => $saleId])) {
    $saleJS = getJSON($sale->json_data);

    if ($saleJS->source != 'W2P') {
      setLastError('Sale ID is not from Web2Print.');
      return false;
    }

    $saleItems = SaleItem::get(['sale_id' => $sale->id]);
    $pic = User::getRow(['id' => $sale->created_by]);

    if ($sale && $saleItems) {
      $customer = Customer::getRow(['id' => $sale->customer_id]);
      $payments = Payment::get(['sale_id' => $sale->id]);
      $payment_validation = PaymentValidation::getRow(['sale_id' => $sale->id]);

      if ($customer) {
        $sale->status = lang($sale->status);
        $response['error'] = 0;
        $response['message'] = 'OK';
        $response['key'] = $key;

        $response['data'] = [];
        $response['data']['customer'] = [
          'company' => $customer->company,
          'name'  => $customer->name,
          'phone' => $customer->phone
        ];

        if ($payments) {
          foreach ($payments as $payment) {
            $response['data']['payments'][] = [
              'date' => $payment->date,
              'reference' => $payment->reference,
              'method' => $payment->method,
              'amount' => $payment->amount
            ];
          }
        }

        $response['data']['pic'] = [
          'name' => $pic->fullname
        ];

        $warehouse = Warehouse::getRow(['id' => $sale->warehouse_id]);

        $response['data']['sale'] = [
          'no'                      => $sale->reference,
          'date'                    => $sale->date,
          'est_complete_date'       => ($saleJS->est_complete_date ?? ''),
          'payment_due_date'        => ($saleJS->payment_due_date ?? ''),
          'waiting_production_date' => ($saleJS->waiting_production_date ?? ''),
          'grand_total'             => $sale->grand_total,
          'paid'                    => $sale->paid,
          'balance'                 => ($sale->grand_total - $sale->paid),
          'status'                  => lang($sale->status),
          'payment_status'          => lang($sale->payment_status),
          'paid_by'                 => ($sale->payment_method ?? '-'),
          'outlet'                  => $sale->biller,
          'note'                    => htmlDecode($sale->note),
          'warehouse'               => $warehouse->name,
          'warehouse_code'          => $warehouse->code
        ];

        $response['data']['sale_items'] = [];

        foreach ($saleItems as $saleItem) {
          $saleItemJS   = getJSON($saleItem->json);
          $operator     = User::getRow(['id' => $saleItemJS->operator_id ?? null]);
          $operatorName = ($operator ? $operator->fullname : '');

          $response['data']['sale_items'][] = [
            'product_code' => $saleItem->product_code,
            'product_name' => $saleItem->product_name,
            'price'        => $saleItem->price,
            'subtotal'     => $saleItem->subtotal,
            'width'        => $saleItemJS->w,
            'length'       => $saleItemJS->l,
            'area'         => $saleItemJS->area,
            'quantity'     => $saleItemJS->sqty,
            'spec'         => $saleItemJS->spec,
            'status'       => lang($saleItemJS->status),
            'due_date'     => ($saleItemJS->due_date ?? ''),
            'completed_at' => ($saleItemJS->completed_at ?? ''),
            'operator'     => $operatorName
          ];
        }

        if ($payment_validation) {
          $response['data']['payment_validation'] = [
            'amount'           => $payment_validation->amount,
            'unique_code'      => $payment_validation->unique_code,
            'transfer_amount'  => ($payment_validation->amount + $payment_validation->unique_code),
            'expired_date'     => $payment_validation->expired_date,
            'transaction_date' => $payment_validation->transaction_date,
            'description'      => $payment_validation->description,
            'status'           => lang($payment_validation->status)
          ];
        }
      }
    }

    $body = json_encode($response);

    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

    $res = curl_exec($curl);

    if (!$res) {
      setLastError(curl_error($curl));
    }
    curl_close($curl);
  }

  return $res;
}

/**
 * Format date to readable date.
 */
function formatDate(string $dateTime)
{
  return date('d M Y', strtotime($dateTime));
}

/**
 * Format date to readable date.
 */
function formatDateTime(string $dateTime)
{
  return date('d M Y H:i:s', strtotime($dateTime));
}

/**
 * Filter number string into float.
 * @param mixed $num Number string.
 * @deprecated Replaced by filterNumber
 */
function filterDecimal($num)
{
  return (float)preg_replace('/([^\-\.0-9Ee])/', '', strval($num));
}

/**
 * Filter string into float.
 * @param mixed $num Number string.
 */
function filterNumber($num)
{
  return (float)preg_replace('/([^\-\.0-9Ee])/', '', strval($num));
}

/**
 * Filter number as quantity with (max. 6 fp) or without floating point.
 * @param mixed $num Number to filter.
 * @example 1 filterQuantity('35,836.924'); // Return "35836.924000"
 * @example 1 filterQuantity('1.5'); // Return "1.500000"
 * @return string
 */
function formatQuantity($num)
{
  $decimals = (isNumberFloated($num) ? 6 : 0);
  return number_format(filterNumber($num), $decimals, '.', '');
}

/**
 * Convert number into formatted currency.
 */
function formatCurrency($num)
{
  // return 'Rp ' . number_format(filterNumber($num), 0, ',', '.');
  return 'Rp ' . number_format(filterNumber($num), 0);
}

/**
 * Convert number into formatted number.
 */
function formatNumber($num, $useDecimal = true)
{
  $dec = 0;

  if (strpos(strval(floatval($num)), '.') !== false && $useDecimal) { // Check if has decimal point.
    $dec = strlen(explode('.', strval(floatval($num)))[1]);

    if ($dec > 6) {
      $dec = 6;
    }
  }

  return number_format(filterNumber($num), $dec);
}

/**
 * Internal Use unique code generator.
 * @param string $category Category name (consumable, category).
 */
function generateInternalUseUniqueCode(string $category)
{
  $code = null;
  $prefix = [
    'consumable'  => 'C',
    'sparepart'   => 'S'
  ];

  if (strcasecmp($category, 'consumable') != 0 && strcasecmp($category, 'sparepart') != 0) {
    setLastError('Category must be consumable or sparepart.');
    return false;
  }

  $lastItem = DB::table('stocks')->isNotNull('internal_use_id')
    ->like('unique_code', $prefix[$category], 'right')
    ->orderBy('unique_code', 'DESC')->getRow(); // Find Cxxxx or Sxxxx

  if ($lastItem) {
    $lastUniqueCode = $lastItem->unique_code; // Ex. SA0001, CA0001

    $prf = substr($lastUniqueCode, 0, 1); // Prefix C (Consumable) or S (Sparepart)
    $alp = substr($lastUniqueCode, 1, 1); // Alphabet A,B,C,...,Z
    $idx = substr($lastUniqueCode, 2); // Index 0001,0002,0003,...,9999

    if (intval($idx) == 9999) {
      $a = ord($alp);

      if ($a == 90) { // if Z reset to A
        $a = 65;
      } else {
        $a++;
      }

      $code = $prf . chr($a) . '0001';
    } else {
      $i = intval($idx);
      $i++;

      // Prepend zero.
      $id = strval($i);
      $id = ($i < 1000 ? ($i < 100 ? ($i < 10 ? '000' . $id : '00' . $id) : '0' . $id) : $id);

      $code = $prf . $alp . $id;
    }
  }

  return ($code ? $code : $prefix[$category] . 'A0001');
}

/**
 * Get adjusted quantity.
 * @return array Return adjusted object [ quantity, type ]
 */
function getAdjustedQty(float $oldQty, float $newQty)
{
  $adjusted = [
    'quantity'  => ($oldQty > $newQty ? $oldQty - $newQty : $newQty - $oldQty),
    'type'      => ($oldQty > $newQty ? 'sent' : 'received')
  ];

  return $adjusted;
}

/**
 * Fetch an item from GET data.
 */
function getCookie($name)
{
  return Services::request()->getCookie($name);
}

/**
 * Get current month period.
 * @param array $period [ start_date, end_date ]
 * @return array ['start_date', 'end_date']
 */
function getCurrentMonthPeriod($period = [])
{
  $period['start_date'] = ($period['start_date'] ?? date('Y-m-') . '01');
  $period['end_date']   = ($period['end_date']   ?? date('Y-m-d'));

  return $period;
}

/**
 * Get daily performance report. biller_id MUST BE Array (PROGRESS). period = yyyy-mm
 * @param array $opt [ biller_id[], period ]
 * @return array Return daily performance data.
 */
function getDailyPerformanceReport($opt)
{
  $dailyPerformanceData = [];
  $billers              = [];

  if (!empty($opt['biller_id']) && is_array($opt['biller_id'])) {
    foreach ($opt['biller_id'] as $billerId) {
      $billers[] = Biller::getRow(['id' => $billerId, 'active' => '1']);
    }
  } else if (empty($opt['biller_id'])) {
    $billers  = Biller::get(['active' => '1']);
  }

  if ($opt['period']) {
    $period = new DateTime($opt['period'] . '-01');
    unset($opt['period']);
  } else {
    $period = new DateTime(date('Y-m-') . '01'); // Current month and date.
  }

  $currentDate  = new DateTime();
  $beginDate    = new DateTime('2022-01-01 00:00:00'); // First data date of begin date.
  $startDate    = new DateTime($period->format('Y-m-d')); // First date of current period.
  $endDate      = new DateTime($period->format('Y-m-t')); // Date must be end of month. (28 to 31)
  $activeDays   = intval($startDate->diff($currentDate)->format('%a'));

  $firstDate  = 1; // First date of month.
  $lastDate   = intval($endDate->format('j')); // Date only. COUNTABLE
  $ymPeriod   = $period->format('Y-m'); // 2022-11

  foreach ($billers as $biller) {
    if ($biller->active != 1) continue;
    // if ($biller->code == 'BALINN') continue;
    // Hide FUCKED IDS
    if ($biller->code == 'IDSUNG') continue;
    if ($biller->code == 'IDSLOS') continue;
    if ($biller->code == 'BALINT') continue;

    $dailyData = [];

    $billerJS = getJSON($biller->json);
    $warehouse = Warehouse::getRow(['code' => $biller->code]);

    if ($biller->code == 'LUC') { // Lucretia method is different.
      $revenue = round(floatval(DB::table('product_transfer')
        ->selectSum('grand_total', 'total')
        ->where('warehouse_id_from', $warehouse->id)
        ->where("created_at BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'")
        ->getRow()->total) ?? 0);

      for ($a = $firstDate; $a <= $lastDate; $a++) {
        $dt       = prependZero($a);
        $dtDaily  = new DateTime("{$ymPeriod}-{$dt}");

        $overTime = ($currentDate->diff($dtDaily)->format('%R') === '+');

        if (!$overTime) {
          $dailyRevenue = round(floatval(DB::table('product_transfer')
            ->selectSum('grand_total', 'total')
            ->where('warehouse_id_from', $warehouse->id)
            ->where("created_at LIKE '{$ymPeriod}-{$dt}%'")
            ->getRow()->total) ?? 0);
        } else {
          $dailyRevenue = 0;
        }

        $stockValue = getWarehouseStockValue((int)$warehouse->id, [
          'start_date'  => $beginDate->format('Y-m-d'),
          'end_date'    => "{$ymPeriod}-{$dt}"
        ]); // sql

        if (!$overTime) {
          $piutang  = round(floatval(DB::table('product_transfer')
            ->selectSum('(grand_total - paid)', 'total')
            ->where('warehouse_id_from', $warehouse->id)
            ->where("created_at BETWEEN '{$beginDate->format('Y-m-d')} 00:00:00' AND '{$ymPeriod}-{$dt}%'")
            ->getRow()->total) ?? 0);
        } else {
          $piutang = 0;
        }

        $dailyData[] = [
          'revenue'     => $dailyRevenue,
          'stock_value' => $stockValue,
          'piutang'     => $piutang
        ];
      }
    } else { // All warehouses except Lucretia.
      $sale = DB::table('sales')
        ->selectSum('grand_total', 'total')
        ->where('biller_id', $biller->id)
        ->where("date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'");

      // I/O MANIP: Tanggal lebih dari 2023-01-01 00:00:00, maka jangan include sale.status = need_payment.
      if (strtotime($startDate->format('Y-m-d')) >= strtotime('2023-01-01 00:00:00') || strtotime($endDate->format('Y-m-d')) >= strtotime('2023-01-01 00:00:00')) {
        $sale->notLike('status', 'need_payment', 'none');
      }

      $revenue = round(floatval($sale->getRow()->total) ?? 0);

      for ($a = $firstDate; $a <= $lastDate; $a++) {
        $dt = prependZero($a);
        $dtDaily = new DateTime("{$ymPeriod}-{$dt}");

        $overTime = ($currentDate->diff($dtDaily)->format('%R') === '+');

        if (!$overTime) {
          $dailyRevenue = round(floatval(DB::table('sales')
            ->selectSum('grand_total', 'total')
            ->notLike('status', 'need_payment')
            ->where('biller_id', $biller->id)
            ->where("date LIKE '{$ymPeriod}-{$dt}%'")
            ->getRow()->total) ?? 0);
        } else {
          $dailyRevenue = 0;
        }

        if ($warehouse) {
          $stockValue = getWarehouseStockValue((int)$warehouse->id, [
            'start_date'  => $beginDate->format('Y-m-d'),
            'end_date'    => "{$ymPeriod}-{$dt}"
          ]); // sql
        } else {
          $stockValue = 0;
        }

        if (!$overTime) {
          $piutang  = round(floatval(DB::table('sales')
            ->selectSum('balance', 'total')
            ->notLike('payment_status', 'paid')
            ->where('biller_id', $biller->id)
            ->whereIn('status', ['waiting_production', 'completed_partial', 'completed'])
            ->where("date BETWEEN '{$beginDate->format('Y-m-d')} 00:00:00' AND '{$ymPeriod}-{$dt}%'")
            ->getRow()->total) ?? 0);
        } else {
          $piutang = 0;
        }

        $dailyData[] = [
          'revenue'     => $dailyRevenue,
          'stock_value' => $stockValue,
          'piutang'     => $piutang
        ];
      }
    }

    // $activeDays     = intval($startDate->diff($currentDate)->format('%d'));
    $daysInMonth    = getDaysInMonth($startDate->format('Y'), $startDate->format('n'));
    $averageRevenue = ($revenue / $activeDays);

    $dailyPerformanceData[] = [
      'biller_id'   => $biller->id,
      'biller'      => $biller->name,
      'avg_revenue' => round($averageRevenue),
      'forecast'    => round($averageRevenue * $daysInMonth),
      'revenue'     => round($revenue), // total sales even not paid.
      'target'      => ($billerJS->target ?? 0), // set on biller
      'daily_data'  => $dailyData // [['revenue' => 100, 'stock_value' => 200, 'piutang' => 300]]
    ];
  }

  return $dailyPerformanceData;
}

/**
 * Get day name from index.
 * @param int $index Index of the day. 1 = Minggu, 7 = Sabtu.
 * @example 1 getDayName(2); // Return "senin".
 */
function getDayName(int $index): string
{
  if ($index == 0) return null;

  $days = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  $x = filterNumber($index);
  return $days[($x - 1) % 7];
}

/**
 * Get days between two period.
 * @param string $startDate Start Date.
 * @param string $endDate End Date.
 * @return int Return days.
 */
function getDaysInPeriod($startDate, $endDate)
{
  $sdate = new DateTime($startDate);
  $edate = new DateTime($endDate);

  $diff = $sdate->diff($edate);

  return (int)$diff->format('%r%a');
}

/**
 * Get total days in a month.
 * @param int $year Year.
 * @param int $month Month.
 * @example 1 getDaysInMonth(2021, 2); // Return 28
 */
function getDaysInMonth($year, $month)
{
  return cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));
}

/**
 * Get excerpt text.
 * @param string $text Text to excerpt.
 * @param int $length Return text length include '...'. Default: 20
 */
function getExcerpt($text, int $length = 20)
{
  $text_len = strlen($text);

  if ($length < 3 || !$length) $length = 3;

  if ($text_len <= ($length - 3)) {
    return $text;
  }

  return substr($text, 0, $length - 3) . '...';
}

/**
 * Fetch an item from GET data.
 */
function getGet($name)
{
  return Services::request()->getGet($name);
}

/**
 * Fetch an item from GET data with fallback to POST.
 */
function getGetPost($name)
{
  return Services::request()->getGetPost($name);
}

/**
 * Get income statement report.
 * @param array $opt [ biller_id[], start_date, end_date ]
 * @return array|null Return income statement data.
 */
function getIncomeStatementReport($opt)
{
  // Lucretia gunakan harga average cost.
  // Outlet gunakan harga mark-on.

  $lucretaiMode = false;

  if (!isset($opt['biller_id']) || empty($opt['biller_id'])) {
    setLastError('Biller is not set.');
    return null;
  }

  if (!is_array($opt['biller_id'])) {
    setLastError('Biller id is not an array.');
    return null;
  }

  $startDate  = ($opt['start_date'] ?? date('Y-m-') . '01');
  $endDate    = ($opt['end_date'] ?? date('Y-m-d'));
  $billerIds  = $opt['biller_id']; // Must be array.

  // BEGIN COLLECT DATA.
  $expenses     = Expense::select('*')
    ->whereIn('biller_id', $billerIds)
    ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
    ->get();
  $expenseGroup = ExpenseCategory::select('*')->orderBy('name', 'ASC')->get();
  $incomes      = Income::select('*')
    ->whereIn('biller_id', $billerIds)
    ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
    ->get();
  $incomeGroup  = IncomeCategory::get();
  $internalUses = InternalUse::select('*')
    ->whereIn('biller_id', $billerIds)
    ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
    ->get();
  $sales        = Sale::select('*')
    ->whereIn('biller_id', $billerIds)
    ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
    ->get();

  $warehouseIds = billerToWarehouse($billerIds); // Convert biller to warehouse.

  $billerLucretai = Biller::getRow(['code' => 'LUC']);

  if (count($billerIds) == 1 && $billerIds[0] == $billerLucretai->id) {
    $lucretaiMode = true;
  } else {
    foreach ($billerIds as $biller_id) {
      if ($biller_id == $billerLucretai->id) $lucretaiMode = true;
    }
  }

  if ($warehouseIds) {
    $purchases    = ProductPurchase::select('*')
      ->whereIn('warehouse_id', $warehouseIds)
      ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->get();
    $stockOpnames = StockOpname::select('*')
      ->whereIn('warehouse_id', $warehouseIds)
      ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->get();
    $transfers = ProductTransfer::select('*')
      ->whereIn('warehouse_id_from', $warehouseIds)
      ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->get();
  } else {
    $purchases = [];
    $stockOpnames = [];
    $transfers = [];
  }

  $capInvAmount = 0;
  $invCost = [
    '32. Purchase of Vehicle', '33. Purchase of Land and Building', '34. Purchase of Production Machine',
    '35. Purchase of Finishing Machine', '36. Purchase of Computers and Supporting Equipment',
    '37. Purchase of Building Construction', '38. Purchase of Another Investation Cost',
  ];
  $invCostData = [];
  $invCostAmount = 0;
  $expenseAmount = 0;
  $expenseData   = [];
  $incomeAmount  = 0;
  $incomeData    = [];
  $internalUseAmount = 0;
  $internalUseData = [];
  $priveAmount = 0;
  $purchaseAmount = 0;
  $revenue       = 0;
  $soldItemCost  = 0;
  $soAmount = 0;
  $transferAmount = 0;
  $transferItemCost = 0;

  // EXPENSES
  foreach ($expenseGroup as $exgroup) {
    $amount = 0;

    if (strcasecmp($exgroup->name, 'Capital Investment (Not used)') === 0) continue; // Ignored.
    if (strcasecmp($exgroup->name, 'Sales TB') === 0) continue; // Ignore Sales TB.

    foreach ($expenses as $expense) {
      if ($expense->category_id == $exgroup->id) {
        $amount += $expense->amount;
      }
    }

    if (strcasecmp($exgroup->name, 'Prive') === 0) {
      $priveAmount += $amount;

      continue;
    }

    if (array_search($exgroup->name, $invCost) !== false) { // Biaya Investasi.
      $invCostAmount += $amount;

      $invCostData[] = [
        'name' => $exgroup->name,
        'amount' => $amount
      ];

      continue;
    }

    $expenseAmount += $amount;

    $expenseData[] = [
      'name'   => $exgroup->name,
      'amount' => $amount
    ];
  }

  // INCOMES
  foreach ($incomeGroup as $ingroup) {
    $amount = 0;

    if (strcasecmp($ingroup->name, 'Sales TB') === 0) continue; // Ignore Sales TB.
    if (strcasecmp($ingroup->name, 'Penanaman Modal') === 0) continue; // Ignored.
    // if (strcasecmp($ingroup->name, 'Pendapatan Baltis Inn') === 0) continue; // Ignored.
    if (strcasecmp($ingroup->name, 'Setoran Kewajiban IDS') === 0) continue; // Ignored.

    foreach ($incomes as $income) {
      if ($income->category_id == $ingroup->id) {
        $amount += $income->amount;
      }
    }

    if (strcasecmp($ingroup->name, 'Capital Investment') === 0) {
      $capInvAmount += $amount;

      continue;
    }

    if (strcasecmp($ingroup->name, 'Pendapatan Baltis Inn') === 0) {
      $revenue += $amount;

      continue;
    }

    $incomeAmount += $amount;

    $incomeData[] = [
      'name'   => $ingroup->name,
      'amount' => $amount
    ];
  }

  // INTERNAL USES.
  $iuCategories = ['consumable', 'sparepart'];

  foreach ($iuCategories as $iuCategory) {
    // if ($iuCategory == 'sparepart' && !$lucretaiMode) continue; // Ignore sparepart if not lucretai.

    $amount = 0;

    foreach ($internalUses as $internalUse) {
      if ($internalUse->category == $iuCategory) {
        $amount += $internalUse->grand_total;
      }
    }

    $internalUseAmount += $amount;

    $internalUseData[] = [
      'name'   => ucfirst($iuCategory),
      'amount' => $amount
    ];
  }

  if ($purchases) {
    foreach ($purchases as $purchase) {
      $purchaseAmount += $purchase->grand_total;
    }
  }

  $saleCount = 0;

  // SALES
  foreach ($sales as $sale) {
    // I/O MANIP: Tanggal lebih dari 2023-01-01 00:00:00, maka jangan include sale.status = need_payment.
    if (strtotime($startDate) >= strtotime('2023-01-01 00:00:00') || strtotime($endDate) >= strtotime('2023-01-01 00:00:00')) {
      if (strcasecmp($sale->status, 'need_payment') === 0) continue;
    }

    // #1 Revenue.
    $revenue += $sale->grand_total;
    $saleCount++;

    $saleItems = SaleItem::get(['sale_id' => $sale->id]);

    if ($saleItems) {
      foreach ($saleItems as $saleItem) {
        if ($saleItem->product_type == 'combo') {
          // Selling item to raw materials;
          $comboItems = ComboItem::get(['product_id' => $saleItem->product_id]);

          foreach ($comboItems as $comboItem) {
            // Raw material.
            $item = Product::getRow(['code' => $comboItem->item_code]);

            // #2 Cost of Goods > Sold Items Cost.
            $soldItemCost += round($item->markon_price * $comboItem->quantity * $saleItem->finished_qty);
          }
        }
      }
    }
  }

  // STOCK OPNAMES
  foreach ($stockOpnames as $stockOpname) {
    $soAmount += ($stockOpname->total_lost + $stockOpname->total_plus);
  }

  // If SO Amount plus then make minus, if minus make plus.
  $soAmount = ($soAmount * -1);

  // TRANSFERS
  foreach ($transfers as $transfer) {
    $transferItems = Stock::get(['transfer_id' => $transfer->id, 'status' => 'sent']);

    if ($transferItems) {
      foreach ($transferItems as $transferItem) {
        $product = Product::getRow(['id' => $transferItem->product_id]);
        // $transferItemCost += ($transferItem->price * $transferItem->quantity);
        // $transferItemCost += ($product->avg_cost * $transferItem->quantity);
        $transferItemCost += ($product->cost * $transferItem->quantity);
      }
    }

    $transferAmount += $transfer->grand_total;
  }

  if ($lucretaiMode) { // Change revenue if lucretai mode enabled.
    $revenue = $transferAmount;
    $soldItemCost = $transferItemCost;
  }

  $costOfGoodsData   = [['name' => 'RAW Materials', 'amount' => $soldItemCost]];
  $costOfGoodsData   = array_merge($costOfGoodsData, $internalUseData);
  $costOfGoodsData   = array_merge($costOfGoodsData, [['name' => 'Lost of Goods', 'amount' => $soAmount]]);
  $costOfGoodsAmount = getTotalAmount($costOfGoodsData); // Sold Item Cost, Internal Use.

  $grossProfit = ($revenue - $costOfGoodsAmount);
  $netProfit   = ($grossProfit + $incomeAmount - $expenseAmount);
  $balanceSheetAmount = ($netProfit - $invCostAmount + $capInvAmount - $priveAmount);

  $incomeStatementData = [
    ['name' => 'Revenue', 'amount' => $revenue],
    ['name' => 'Cost of Goods', 'amount' => $costOfGoodsAmount, 'data' => $costOfGoodsData],
    ['name' => 'Gross Profit', 'amount' => $grossProfit],
    ['name' => 'Other Income', 'amount' => $incomeAmount, 'data' => $incomeData],
    ['name' => 'Operational Cost', 'amount' => $expenseAmount, 'data' => $expenseData],
    ['name' => 'Net Profit', 'amount' => $netProfit],
    ['name' => 'Investation Cost', 'amount' => $invCostAmount, 'data' => $invCostData],
    ['name' => 'Capital Investment', 'amount' => $capInvAmount],
    ['name' => 'Prive', 'amount' => $priveAmount],
    ['name' => 'Balance Sheet', 'amount' => $balanceSheetAmount]
  ];

  return $incomeStatementData;
}

/**
 * Get month name by index.
 * @param int $index Month index.
 * @example 1 getMonthName(8); // Return 'agustus'.
 */
function getMonthName($index)
{
  $months = [
    null, 'januari', 'februari', 'maret', 'april', 'mei', 'juni',
    'juli', 'agustus', 'september', 'oktober', 'november', 'desember'
  ];
  $x = filterNumber($index);
  return $months[$x % 13];
}

/**
 * Get order stock quantity by current stock, min order and safety stock.
 * @param float $currentStock Current stock of item.
 * @param float $minOrderQty Min. order of item.
 * @param float $safetyStock Safety stock of item.
 * @example 1 getOrderStock(4, 3, 15); // Return 12.
 *
 */
function getOrderStock($currentStock, $minOrderQty, $safetyStock)
{
  $currStock  = filterNumber($currentStock);
  $minOrder   = filterNumber($minOrderQty);
  $safeStock  = filterNumber($safetyStock);
  $orderStock = 0;

  if ($currStock < $safeStock) { // Safe stock (current stock < safe_stock)
    $restStock = round($safeStock - $currStock); // 400 - (-10) = 410 < 224 = false
    $orderStock = (ceil($restStock / $minOrder) * $minOrder); // (410 / 224) * 224
  }

  return $orderStock;
}

/**
 * Get past month period.
 * @param int $month How many past month.
 * @example 1 getPastMonthPeriod(1);
 * // Return ['start_date' => '2020-01-01', 'end_date' => '2020-01-31', 'days' => 31]
 */
function getPastMonthPeriod($month)
{
  $mn   = intval($month);
  $base = strtotime(date('Y-m-') . '01'); // Current year and month with date 1.
  $y    = date('Y', strtotime('-1 month', $base));
  $m    = date('n', strtotime('-1 month', $base));
  $days = 0;

  $start_date = date('Y-m', strtotime("-{$mn} month", $base)) . '-01';
  $end_date   = date('Y-m', strtotime('-1 month', $base)) . '-' . getDaysInMonth($y, $m);

  for ($a = 1; $a <= $mn; $a++) {
    $days += getDaysInMonth(date('Y', strtotime("-{$a} month", $base)), date('n', strtotime("-{$a} month", $base)));
  }

  return [
    'start_date' => $start_date,
    'end_date'   => $end_date,
    'days'       => $days // Total days.
  ];
}

/**
 * Fetch an item from POST.
 */
function getPost($name)
{
  return Services::request()->getPost($name);
}

/**
 * Fetch an item from POST data with fallback to GET.
 */
function getPostGet($name)
{
  return Services::request()->getPostGet($name);
}

/**
 * Return QRIS bank.
 */
function getQRISBank(int $billerId)
{
  return Bank::getRow(['biller' => $billerId, 'number' => '1360000555323']);
}

/**
 * Get queue date time for customer who commit ticket registration.
 * @param string $dateTime Initial datetime string.
 * @return string return Working date for customer who commit ticket registration.
 */
function getQueueDateTime($dateTime)
{
  $dt = new DateTime($dateTime);
  $hour   = $dt->format('H');
  $day    = $dt->format('D');
  $holiday = false;
  $h = 0;

  if (strcasecmp($day, 'Sun') === 0 || strcasecmp($day, 'Sat') === 0) {
    $holiday = true;
  }

  if ($hour >= 23 || $hour < 7) {
    $h = ($holiday ? 9 : 7);
  }

  // if ($hour >= 23 && $minute <= 59) { // Off time.
  //   $h = (24 - $hour + 8);
  // } elseif ($hour >= 0 && $hour < 7 && $minute <= 59) { // Next day.
  //   $h = (7 - $hour);
  // } else {
  //   $h = 0;
  // }

  if ($h) $dt->add(new DateInterval("PT{$h}H")); // Period Time $h Hour

  return $dt->format('Y-m-d H:i:s');
}

/**
 * A convenience method that grabs the raw input stream(send method in PUT, PATCH, DELETE) and
 * decodes the String into an array.
 */
function getRawInput()
{
  return Services::request()->getRawInput();
}

/**
 * Decode JSON string into object.
 *
 * @param mixed $json JSON string to decode into object or array.
 * @param bool $assoc Return as associative array if true. Default false.
 */
function getJSON($json, bool $assoc = false)
{
  if ($json) {
    return (json_decode($json, $assoc) ?? ($assoc ? [] : (object)[]));
  }
  return ($assoc ? [] : (object)[]);
}

/**
 * Get last error message.
 * @param string $defaultMsg Default message if last error message is not defined.
 * @return string|null Return error message. null or empty string if no error.
 */
function getLastError(string $defaultMsg = null)
{
  return (session()->has('lastErrMsg') ? session('lastErrMsg') : $defaultMsg);
}

/**
 * Get Mark-on price or Warehouse price from cost and mark-on.
 * @param float $cost Item cost.
 * @param float $markon Mark-on percent.
 */
function getMarkonPrice(float $cost, float $markon)
{
  return round(filterNumber($cost) / (1 - (filterNumber($markon) / 100)));
}

/**
 * Get new user Stock Opname Cycle.
 * @param int $userId User ID.
 * @param int $warehouseId Warehouse ID.
 * @return int Return SO Cycle. Return 0 if no item to be check.
 */
function getNewSOCycle(int $userId, int $warehouseId)
{
  $so = StockOpname::select('*')->where('created_by', $userId)->orderBy('id', 'DESC')->getRow();

  if ($so) {
    $soCycle = intval($so->cycle) + 1;

    $whp = WarehouseProduct::getRow(['warehouse_id' => $warehouseId, 'user_id' => $userId, 'so_cycle' => $soCycle]);

    if ($whp) {
      return $soCycle;
    }
  }

  $whp = WarehouseProduct::getRow(['warehouse_id' => $warehouseId, 'user_id' => $userId, 'so_cycle' => 1]);

  if ($whp) {
    return 1;
  }

  return 0;
}

/**
 * Get StockOpname items suggestion.
 */
function getStockOpnameSuggestion(int $userId, int $warehouseId, int $cycle)
{
  return WarehouseProduct::select('products.id AS id, products.code AS code, products.name AS name,
    units.code AS unit, warehouses_products.quantity AS quantity')
    ->join('products', 'products.id = warehouses_products.product_id', 'left')
    ->join('units', 'units.id = products.unit', 'left')
    ->where('products.active', 1)
    ->where('warehouses_products.user_id', $userId)
    ->where('warehouses_products.warehouse_id', $warehouseId)
    ->where('warehouses_products.so_cycle', $cycle)
    ->like('products.type', 'standard')
    ->groupBy('products.id')
    ->orderBy('name', 'DESC')
    ->get();
}

/**
 * Get real stock quantity.
 * @param int $productId Product ID.
 * @param int $warehouseId Warehouse ID.
 * @param array $opt Options. [ start_date (Y-m-d H:i:s), end_date (Y-m-d H:i:s) ]
 * @return float Total stock quantity.
 */
function getStockQuantity(int $productId, int $warehouseId, array $opt = [])
{
  return Stock::totalQuantity($productId, $warehouseId, $opt);
}

/**
 * Get total amount of array data.
 * @param array $data [ *amount ]
 * @return float Total of accumulated amount.
 */
function getTotalAmount(array $data)
{
  $amount = 0.0;

  foreach ($data as $d) {
    $amount += floatval($d['amount']);
  }

  return $amount;
}

/**
 * Get Warehouse stock value.
 * @param int $warehouseId Warehouse ID.
 * @param array $opt [ start_date, end_date ]
 */
function getWarehouseStockValue(int $warehouseId, array $opt = [])
{
  $currentDate  = new DateTime();
  $startDate    = new DateTime($opt['start_date'] ?? date('Y-m-') . '01');
  $endDate      = new DateTime($opt['end_date'] ?? date('Y-m-t'));
  $warehouse    = Warehouse::getRow(['id' => $warehouseId]);

  if (!$warehouse) {
    setLastError("getWarehouseStockValue(): Cannot find warehouse [id:{$warehouseId}]");
    return NULL;
  }

  // If end date is more than current date then 0.
  // if ($currentDate->diff($endDate)->format('%R') == '+') {
  //   return 0;
  // }

  if ($warehouse->code == 'LUC') { // Lucretai mode.
    $value = DB::table('products')->selectSum('products.cost * (recv.total - sent.total)', 'total')
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks
        WHERE status LIKE 'received' AND warehouse_id = {$warehouse->id}
        AND date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
        GROUP BY product_id) recv", 'recv.product_id = products.id', 'left')
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks
      WHERE status LIKE 'sent' AND warehouse_id = {$warehouse->id}
      AND date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
      GROUP BY product_id) sent", 'sent.product_id = products.id', 'left')
      ->whereIn('products.type', ['standard']) // Standard only
      ->whereNotIn('products.category_id', [2, 14, 16, 17, 18]) // Not Assets and Sub-Assets.
      ->getRow();

    return floatval($value->total);
  } else {
    $value = DB::table('products')->selectSum('products.markon_price * (recv.total - sent.total)', 'total')
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks
        WHERE status LIKE 'received' AND warehouse_id = {$warehouse->id}
        AND date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
        GROUP BY product_id) recv", 'recv.product_id = products.id', 'left')
      ->join("(SELECT product_id, SUM(quantity) AS total FROM stocks
      WHERE status LIKE 'sent' AND warehouse_id = {$warehouse->id}
      AND date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
      GROUP BY product_id) sent", 'sent.product_id = products.id', 'left')
      ->whereIn('products.type', ['standard']) // Standard only
      ->whereNotIn('products.category_id', [2, 14, 16, 17, 18]) // Not Assets and Sub-Assets.
      ->getRow();

    return floatval($value->total);
  }
}

/**
 * Get working date time for customer who take an order.
 * @param string $dateTime Initial datetime string.
 * @return string return Working date for customer who take an order.
 */
function getWorkingDateTime($dateTime)
{
  $dt = new DateTime($dateTime);
  $hour   = $dt->format('H');
  $minute = $dt->format('i');

  if ($hour >= 17 && $hour <= 23 && $minute <= 59) { // After office hour.
    $h = (24 - $hour + 9); // Return must hour 9.
  } elseif ($hour >= 0 && $hour < 9 && $minute <= 59) {
    $h = (9 - $hour);
  } else {
    $h = 0;
  }

  if ($h) $dt->add(new DateInterval("PT{$h}H")); // Period Time $h Hour

  return $dt->format('Y-m-d H:i:s');
}

/**
 * Check if current login session has permission access.
 * If session has permission 'All' then it's always return true.
 *
 * @param array|string $permission Permission to check. Ex. 'User.Add'
 */
function hasAccess($permission)
{
  if (isLoggedIn()) {
    $perms = session('login')->permissions;

    if (is_array($permission)) {
      $roles = $permission;
    } else {
      $roles[] = $permission;
    }

    foreach ($roles as $role) {
      if (in_array('All', $perms) || in_array($role, $perms)) {
        return true;
      }
    }
  }

  return false;
}

function hasNotificationAccess(object $scope)
{
  if (!empty($scope->billers) && session('login')->biller_id) {
    if (!in_array(session('login')->biller_id, $scope->billers)) {
      return false;
    }
  }

  if (!empty($scope->users)) {
    if (!in_array(session('login')->user_id, $scope->users)) {
      return false;
    }
  }

  if (!empty($scope->usergroups)) {
    $hasAccess = false;

    foreach (session('login')->groups as $group) {
      $userGroup = \App\Models\UserGroup::getRow(['code' => $group]);

      if (!in_array($userGroup, $scope->usergroups)) {
        $hasAccess = true;
      }
    }

    if (!$hasAccess) {
      return false;
    }
  }

  if (!empty($scope->warehouses) && session('login')->warehouse_id) {
    if (!in_array(session('login')->warehouse_id, $scope->warehouses)) {
      return false;
    }
  }

  return true;
}

/**
 * Convert html string into readable note.
 */
function html2Note($html)
{
  $str = str_replace('<br>', "\r\n", ($html ?? ''));
  return htmlRemove($str);
}

/**
 * Decode HTML string.
 * @param string $html HTML string to decode.
 * @return string Return decoded HTML string.
 * @example 1 htmlDecode('&lt;b&gt;OK&lt;/b&gt;'); // Return '<b>OK</b>'.
 */
function htmlDecode($html)
{
  return html_entity_decode(trim($html ?? ''), ENT_HTML5 | ENT_QUOTES | ENT_XHTML, 'UTF-8');
}

/**
 * Encode HTML string.
 * @param string $html HTML string to encode.
 * @return string Return encoded HTML string.
 * @example 1 htmlEncode('<b>OK</b>'); // Return '&lt;b&gt;OK&lt;/b&gt;'.
 */
function htmlEncode($html)
{
  $allowed = '<a><span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><pre>
  <code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>';
  $stripped = strip_tags($html ?? '', $allowed);
  return htmlentities(trim($stripped), ENT_HTML5 | ENT_QUOTES | ENT_XHTML, 'UTF-8');
}

/**
 * Remove HTML tag.
 * @param string $html HTML string to remove.
 * @example 1 htmlRemove('<b>OK</b>'); // Return 'OK'.
 */
function htmlRemove($html)
{
  $decoded = html_entity_decode(trim($html ?? ''), ENT_HTML5 | ENT_QUOTES | ENT_XHTML, 'UTF-8');
  return preg_replace('/\<(.*?)\>/', '', $decoded);
}

/**
 * Determine if current status is the same like one of the status list.
 * @param string $currentStatus Current status.
 * @param array $statusList Status list.
 */
function inStatus(string $currentStatus, array $statusList)
{
  foreach ($statusList as $st) {
    if (strcasecmp($currentStatus, $st) === 0) {
      return true;
    }
  }

  return false;
}

/**
 * Check if request from AJAX.
 */
function isAJAX()
{
  return Services::request()->isAJAX();
}

/**
 * Check if all array key is empty.
 * @param array $data Array data to check.
 * @param array $key Set of key to check.
 * 
 * Example: isArrayEmpty($data, ['user_id', 'biller_id'])
 */
function isArrayEmpty(array $data, array $key)
{
  foreach ($key as $k) {
    if (!empty($data[$k])) {
      return false;
    }
  }

  return true;
}

/**
 * Check if request from command line.
 */
function isCLI()
{
  return (PHP_SAPI === 'cli');
}

/**
 * Check if status completed. Currently 'completed', 'completed_partial' or 'delivered' as completed.
 * @param string $status Status to check.
 */
function isCompleted($status)
{
  return ($status == 'completed' || $status == 'completed_partial' ||
    $status == 'delivered' || $status == 'finished');
}

/**
 * Check if due date has happened.
 * @param string $due_date Due date
 * @example 1 isDueDate('2020-01-20 20:40:11'); // Return false if current time less then due date.
 */
function isDueDate($dueDate)
{
  return (time() > strtotime($dueDate));
}

/**
 * Check if current environment is same as value.
 */
function isEnv($environment)
{
  return (ENVIRONMENT == $environment);
}

/**
 * Check current session if has login data.
 */
function isLoggedIn()
{
  return session()->has('login');
}

/**
 * Check if number has floated point.
 * @param string $num Number to check.
 */
function isNumberFloated($num)
{
  return (strpos(strval(floatval($num)), '.') !== false ? true : false);
}

/**
 * Check assigned product warehouse by warehouse name.
 * @param string $product_warehouse Assigned product warehouse name.
 * Ex. "Durian, Tembalang" or "-Tlogosari, -Ungaran".
 * @param string $warehouse_name Warehouse name to check assign.
 * Ex. "Durian", "Ngesrep", ...
 */
function isProductWarehouses($productWarehouse, $warehouseName)
{
  if (!empty($productWarehouse)) {
    $negated = false;
    $pwhs = explode(',', trim($productWarehouse));

    if (substr($pwhs[0], 0, 1) == '-') $negated = true;

    foreach ($pwhs as $pwh) {
      $pwh = trim($pwh);

      if ($negated) {
        if (strcasecmp(substr($pwh, 1), $warehouseName) === 0) {
          return false;
        }
      } else {
        if (strcasecmp($pwh, $warehouseName) === 0) {
          return true;
        }
      }
    }

    if (!$negated) {
      return false;
    }
  }
  return true;
}

/**
 * Determine is HTTP connection is secure.
 */
function isSecure()
{
  return (!isCLI() && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
}

/**
 * Determine special customer (Privilege or TOP) by customer id.
 * @param int $customerId Customer ID.
 */
function isSpecialCustomer($customerId)
{
  $customer = Customer::getRow(['id' => $customerId]);

  if (!$customer) {
    return false;
  }

  $csGroup = CustomerGroup::getRow(['id' => $customer->customer_group_id]);

  if ($csGroup) {
    return (strcasecmp($csGroup->name, 'PRIVILEGE') === 0 || strcasecmp($csGroup->name, 'TOP') === 0);
  }

  return false;
}

/**
 * Determine if Sale is TB by biller code and warehouse code.
 */
function isTBSale(string $biller, string $warehouse)
{
  return (strcasecmp(Biller::getRow(['code' => $biller])->name, Warehouse::getRow(['code' => $warehouse])->name) != 0);
}

/**
 * Check if user_id is W2P or not.
 */
function isW2PUser($user_id)
{
  $user = User::getRow(['id' => $user_id]);

  if ($user) {
    return (strcasecmp($user->username, 'W2P') === 0);
  }
  return false;
}

/**
 * Check if invoice from W2P or note.
 */
function isWeb2Print($sale_id)
{
  $sale = Sale::getRow(['id' => $sale_id]);

  if ($sale) {
    $saleJS = getJSON($sale->json);

    return (strcasecmp(($saleJS->source ?? ''), 'W2P') === 0);
  }
  return false;
}

/**
 * Create mutual exclusion
 * @param string $name Mutex name.
 */
function mutexCreate(string $name = null)
{
  if (!is_dir(WRITEPATH . 'mutex')) {
    mkdir(WRITEPATH . 'mutex');
  }

  if (!$name) {
    $name = 'default';
  }

  $hFile = fopen(WRITEPATH . 'mutex/' . $name, 'w');

  if ($hFile && flock($hFile, LOCK_EX)) {
    return $hFile;
  }

  return false;
}

/**
 * Release mutual exclusion.
 * @param resource $hMutex Mutex instance.
 */
function mutexRelease($hMutex)
{
  if ($hMutex) {
    $meta_data = stream_get_meta_data($hMutex); // Get absolute file name from resource/stream.
    $filename = $meta_data['uri'];

    flock($hMutex, LOCK_UN);
    fclose($hMutex);

    if (file_exists($filename)) {
      @unlink($filename);
      return true;
    }
  }

  return false;
}

/**
 * Nulling empty data except zero.
 */
function nulling(array $data, array $keys)
{
  if (empty($keys)) return $data;

  foreach ($keys as $key) {
    if (isset($data[$key]) && empty($data[$key]) && $data[$key] != 0) {
      $data[$key] = null;
    }
  }

  return $data;
}

/**
 * Optical Character Recognition. Get readable text from image.
 * @param string $image Image to read as text.
 * @return array|false Return array of string data or false if error.
 */
function ocr($image)
{
  setLastError();

  $exe = "tesseract";
  $output = [];
  $retval = 0;

  exec("$exe --version", $output, $retval);

  if ($retval != 0) {
    setLastError("Tesseract is not found.");
    return false;
  }

  if (is_file($image)) {
    $output = [];
    exec("$exe $image stdout", $output);
  } else {
    setLastError('Image file is not found.');
    return false;
  }

  return $output;
}

/**
 * Add 62 to phone number.
 * @param string $phone Phone number.
 */
function phoneCode($phone)
{
  if (substr($phone, 0, 2) == '08') {
    return '62' . substr($phone, 1);
  }
  if (substr($phone, 0, 3) == '+62') {
    return substr($phone, 1);
  }
  if (substr($phone, 0, 2) != '62') {
    $phone = '62' . $phone;
  }
  return $phone;
}

/**
 * Prepend zero for number.
 * @param int $num Number to prepend with zero.
 */
function prependZero($num)
{
  return ($num < 10 ? '0' . $num : $num);
}

function renderAttachment(string $attachment = null)
{
  $res = '';

  if ($attachment) {
    $res = '
      <a href="' . base_url('filemanager/view/' . $attachment) . '"
        data-toggle="modal" data-target="#ModalDefault2" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
        <i class="fas fa-paperclip"></i>
      </a>';
  }

  return $res;
}

function renderStatus($status)
{
  if (empty($status)) return '';

  $type = 'default';
  $st = strtolower($status);

  $danger = [
    'bad', 'decrease', 'due', 'due_partial', 'expired', 'need_approval', 'need_payment', 'off',
    'over_due', 'over_received', 'overwrite', 'returned', 'sent', 'skipped'
  ];
  $purple = [
    'solved'
  ];
  $info = [
    'calling', 'completed_partial', 'confirmed', 'delivered', 'excellent', 'finished',
    'installed_partial', 'ordered', 'partial', 'percent', 'preparing',
    'received_partial', 'serving'
  ];
  $success = [
    'active', 'approved', 'completed', 'consumable', 'currency', 'increase', 'formula',
    'good', 'installed', 'paid', 'received', 'served', 'verified'
  ];
  $warning = [
    'called', 'cancelled', 'checked', 'draft', 'inactive', 'packing', 'pending', 'slow', 'sparepart',
    'trouble', 'waiting', 'waiting_production', 'waiting_transfer'
  ];

  if (array_search($st, $danger) !== false) {
    $type = 'danger';
  } elseif (array_search($st, $purple) !== false) {
    $type = 'purple';
  } elseif (array_search($st, $info) !== false) {
    $type = 'info';
  } elseif (array_search($st, $success) !== false) {
    $type = 'success';
  } elseif (array_search($st, $warning) !== false) {
    $type = 'warning';
  }

  $name = lang('Status.' . $status);

  return "<div class=\"badge bg-gradient-{$type} p-2\">{$name}</div>";
}

/**
 * Get request method.
 */
function requestMethod()
{
  return (!isCLI() ? $_SERVER['REQUEST_METHOD'] : null);
}

/**
 * Round decimal floating point with filtering.
 * @param mixed $num Number to round.
 * @example 1 roundDecimal('2,34,30.20'); // Return 23430
 * @example 2 roundDecimal('25.5'); // Return 26
 */
function roundDecimal($num)
{
  return round(filterNumber($num));
}

/**
 * Send JSON response.
 * @param mixed $data Data to send.
 * @param array $options Options [ string origin ].
 */
function sendJSON($data, $options = [])
{
  if (!isCLI()) {
    $origin = base_url();

    if (!empty($options['origin'])) $origin = $options['origin'];

    header("Access-Control-Allow-Origin: {$origin}");
    header('Content-Type: application/json');
  }

  die(json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Send WA Message.
 * @param string $phone Phone number.
 * @param string $text Message to send.
 * @param array $opt Options [ api_key, device_id(watsapid only), engine:[ rapiwha | whacenter ] ]
 */
function sendWA($phone, $text, $opt = [])
{
  $ph = phoneCode($phone);
  $query = [];
  $defaultEngine = 'whacenter';

  $engine = (!empty($opt['engine']) ? $opt['engine'] : $defaultEngine);

  if ($engine == 'rapiwha') {
    $url = 'https://panel.rapiwha.com/send_message.php';
    $query['apikey'] = (!empty($opt['api_key']) ? $opt['api_key'] : '55L5E5BJQ2FPNK2LNEQQ');
    $query['text'] = $text;
    $query['number'] = $ph;
  } else if ($engine == 'watsap') {
    $url = 'https://api.watsap.id/send-message';
    $query['api-key'] = (!empty($opt['api_key']) ? $opt['api_key'] : 'a66d60ee436b0861c28353611d089dc872629d09');
    $query['id_device'] = (!empty($opt['device_id']) ? $opt['device_id'] : 612); // INDOPRINTING;
    $query['pesan'] = $text;
    $query['no_hp'] = $ph;
    $query = json_encode($query);
  } else if ($engine == 'whacenter') {
    $url = 'https://app.whacenter.com/api/send';
    $query['device_id'] = (!empty($opt['api_key']) ? $opt['api_key'] : '05fb9e0b23d2ef3f0b21eef5ba3a1f89');
    $query['message'] = $text;
    $query['number'] = $ph;
  }

  $curlOptions = [
    CURLOPT_CUSTOMREQUEST   => 'POST',
    CURLOPT_HEADER          => FALSE,
    CURLOPT_POSTFIELDS      => $query,
    CURLOPT_RETURNTRANSFER  => TRUE,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_CONNECTTIMEOUT  => 30
  ];

  $curl = curl_init($url);

  curl_setopt_array($curl, $curlOptions);

  $res = curl_exec($curl);

  if (!$res) {
    setLastError(curl_error($curl));
  }
  curl_close($curl);

  return $res;
}

/**
 * Set created_by based on user id and created_at. Used for Model data.
 * @param array $data
 */
function setCreatedBy(array $data)
{
  $createdAt = new DateTime($data['created_at'] ?? date('Y-m-d H:i:s'));

  if (empty($data['created_by']) && isLoggedIn()) {
    $data['created_by'] = session('login')->user_id;
  } else if (empty($data['created_by'])) {
    $data['created_by'] = 119; // System.
  }

  $data['created_at'] = $createdAt->format('Y-m-d H:i:s');

  // Zero date protection.
  if (isset($data['date']) && empty($data['date'])) {
    $data['date'] = $data['created_at'];
  }

  return $data;
}

/**
 * Set expired_at as expired date. Default +1 day.
 */
function setExpired(array $data)
{
  if (empty($data['expired_at'])) {
    $data['expired_at']   = date('Y-m-d H:i:s', strtotime('+1 day', time()));
    $data['expired_date'] = $data['expired_at']; // Compatibility
  }

  return $data;
}

/**
 * Set or update json column. Used for Model data.
 * @param array $data Column data.
 * @param array $columns JSON column to set.
 * @param array $jsonData Existing json data to be update.
 */
function setJSONColumn($data = [], $columns = [], $jsonData = [])
{
  $json = $jsonData;

  foreach ($columns as $col) {
    if (array_key_exists($col, $data)) {
      $json[$col] = $data[$col];
      unset($data[$col]);
    }
  }

  $data['json'] = json_encode($json);

  return $data;
}

/**
 * Set last error message.
 * @param mixed $message Error message.
 */
function setLastError($message = null)
{
  if ($message) {
    session()->set('lastErrMsg', $message);
  } else {
    session()->remove('lastErrMsg');
  }
}

/**
 * Set updated by based on user id. Used for Model data.
 * @param array $data
 */
function setUpdatedBy($data = [])
{
  $data['updated_at'] = ($data['updated_at'] ?? date('Y-m-d H:i:s'));

  if (empty($data['updated_by']) && isLoggedIn()) {
    $data['updated_by'] = session('login')->user_id;
  }

  return $data;
}

/**
 * Strip HTML tags for note.
 */
function stripTags(string $text)
{
  return strip_tags($text, '<a><br><em><h1><h2><h3><li><ol><p><strong><u><ul>');
}

/**
 * Return vouchers total amount.
 * @param array $vouchers Array of voucher id.
 * @param float $grandTotal Grand total of invoice. Required if voucher's method is percent.
 * @param float $lastDiscount Last discount of invoice if any.
 */
function useVouchers(array $vouchers, float $grandTotal, float $lastDiscount = 0.0)
{
  $discount = $lastDiscount;

  foreach ($vouchers as $vid) {
    $voucher = Voucher::getRow(['id' => $vid]);

    if (strtotime($voucher->valid_from) > time()) {
      continue;
    }

    if (strtotime($voucher->valid_to) < time()) {
      continue;
    }

    if ($voucher->quota < 1) {
      continue;
    }

    if (Voucher::update((int)$voucher->id, ['quota' => $voucher->quota - 1])) {
      if ($voucher->method == 'currency') {
        $discount += floatval($voucher->amount);
      } else if ($voucher->method == 'percent') {
        $discount += floatval($grandTotal * ($voucher->percent * 0.01));
      }
    }
  }

  return $discount;
}

/**
 * Generate UUID (Universally Unique Identifier)/GUID (Globally Unique Identifier)
 */
function uuid()
{
  $timeLow          = bin2hex(random_bytes(4));
  $timeHigh         = bin2hex(random_bytes(2));
  $timeHiAndVersion = bin2hex(random_bytes(2));
  $clockSeqLow      = bin2hex(random_bytes(2));
  $node             = bin2hex(random_bytes(6));

  return "{$timeLow}-{$timeHigh}-{$timeHiAndVersion}-{$clockSeqLow}-{$node}";
}

class FileLogger
{
  protected $hFile;

  public function __construct($filename = 'logger.log')
  {
    $this->hFile = fopen($filename, 'ab');

    return $this;
  }

  public function close()
  {
    return fclose($this->hFile);
  }

  public function write($data, $length = null)
  {
    return fputs($this->hFile, '[' . date('Y-m-d H:i:s') . '] ' . print_r($data, true) . "\r\n", $length);
  }
}
