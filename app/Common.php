<?php

declare(strict_types=1);

use App\Models\{Customer, CustomerGroup, User};

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
  $ip = \Config\Services::request()->getIPAddress();
  $ua = \Config\Services::request()->getUserAgent();

  $data = [
    'data'        => $data,
    'ip_address'  => $ip,
    'user_agent'  => $ua
  ];

  if ($json) {
    $data['json'] = json_encode($json);
  }

  return \App\Models\Activity::add($data);
}

/**
 * Check for permission and login status.
 * @param string $permission Permission to check. Ex. "User.View". If null it will check for login session.
 */
function checkPermission(string $permission = null)
{
  $request = \Config\Services::request();
  $ajax   = $request->isAJAX();

  if (isLoggedIn()) {
    if ($permission) {
      if ($ajax) {
        if (!hasAccess($permission)) {
          sendJSON(['err' => 1, 'text' => lang('Msg.notAuthorized'), 'title' => lang('Msg.accessDenied')]);
        }
      }

      if (!hasAccess($permission)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url()));
        die;
      }
    }
  } else {
    if ($ajax) {
      sendJSON(['err' => 2, 'text' => lang('Msg.notLoggedIn'), 'title' => lang('Msg.accessDenied')]);
    } else {
      $data = [
        'resver' => '1.0'
      ];

      if (!isLoggedIn() && getCookie('___')) {
        if (\App\Models\Auth::loginRememberMe(getCookie('___'))) {
          header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
      }

      echo view('Auth/login', $data);
      die;
    }
  }
}

/**
 * Convert JS time to PHP time or vice versa.
 */
function dateTimeJS(string $datetime)
{
  if (strlen($datetime) && strpos($datetime, 'T') !== false) {
    return str_replace('T', ' ', $datetime);
  }

  if (empty($datetime)) {
    return date('Y-m-d H:i:s');
  }

  return str_replace(' ', 'T', $datetime);
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

/**
 * Format date to readable date.
 */
function formatDate(string $dateTime)
{
  return date('d M Y H:i:s', strtotime($dateTime));
}

/**
 * Filter number string into float.
 * @param mixed $num Number string.
 */
function filterDecimal($num)
{
  return (float)preg_replace('/([^\-\.0-9Ee])/', '', strval($num));
}

/**
 * Convert number into formatted currency.
 */
function formatCurrency($num)
{
  return 'Rp ' . number_format(filterDecimal($num), 0, ',', '.');
}

/**
 * Convert number into formatted number.
 */
function formatNumber($num)
{
  return number_format(filterDecimal($num), 0);
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
  return \Config\Services::request()->getCookie($name);
}

/**
 * Fetch an item from GET data.
 */
function getGet($name)
{
  return \Config\Services::request()->getGet($name);
}

/**
 * Fetch an item from POST.
 */
function getPost($name)
{
  return \Config\Services::request()->getPost($name);
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
  return \Config\Services::request()->getRawInput();
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
 * @return string|null Return error message. null or empty string if no error.
 */
function getLastError()
{
  return (session()->has('lastErrMsg') ? session('lastErrMsg') : null);
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

/**
 * Check if request from AJAX.
 */
function isAJAX()
{
  return \Config\Services::request()->isAJAX();
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
    $status == 'delivered' || $status == 'finished' ? true : false);
}

/**
 * Check if due date has happened.
 * @param string $due_date Due date
 * @example 1 isDueDate('2020-01-20 20:40:11'); // Return false if current time less then due date.
 */
function isDueDate($due_date)
{
  return (strtotime($due_date) > time() ? false : true);
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
  return (session()->has('login') ? true : false);
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
    return (strcasecmp($csGroup->name, 'PRIVILEGE') === 0 || strcasecmp($csGroup->name, 'TOP') === 0 ? true : false);
  }
  return false;
}

/**
 * Check if user_id is W2P or not.
 */
function isW2PUser($user_id)
{
  $user = User::getRow(['id' => $user_id]);

  if ($user) {
    return (strcasecmp($user->username, 'W2P') === 0 ? true : false);
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
    $saleJS = getJSON($sale->json_data);

    return (strcasecmp(($saleJS->source ?? ''), 'W2P') === 0 ? true : false);
  }
  return false;
}

/**
 * Nulling empty data.
 */
function nulling(array $data, array $keys)
{
  if (empty($keys)) return $data;

  foreach ($keys as $key) {
    if (isset($data[$key]) && empty($data[$key])) {
      $data[$key] = null;
    }
  }

  return $data;
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

function renderStatus(string $status)
{
  if (empty($status)) return '';

  $type = 'default';
  $st = strtolower($status);

  $danger = [
    'bad', 'decrease', 'due', 'due_partial', 'expired', 'need_approval', 'need_payment', 'off',
    'over_due', 'over_received', 'overwrite', 'returned', 'skipped'
  ];
  $info = [
    'calling', 'completed_partial', 'confirmed', 'delivered', 'excellent', 'finished',
    'installed_partial', 'ordered', 'partial', 'preparing', 'received', 'received_partial', 'serving'
  ];
  $success = [
    'approved', 'completed', 'increase', 'formula', 'good', 'installed', 'paid',
    'sent', 'served', 'verified'
  ];
  $warning = [
    'called', 'cancelled', 'checked', 'draft', 'packing', 'pending', 'slow', 'trouble', 'waiting',
    'waiting_production', 'waiting_transfer'
  ];

  if (array_search($st, $danger) !== false) {
    $type = 'danger';
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
 * Send JSON response.
 * @param mixed $data Data to send.
 * @param array $options Options [ string origin ].
 */
function sendJSON($data, $options = [])
{
  $origin = base_url();

  if (!empty($options['origin'])) $origin = $options['origin'];

  header("Access-Control-Allow-Origin: {$origin}");
  header('Content-Type: application/json');
  die(json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Set created_by based on user id and created_at. Used for Model data.
 * @param array $data
 */
function setCreatedBy(array $data)
{
  $data['created_at'] = ($data['created_at'] ?? date('Y-m-d H:i:s'));

  if (empty($data['created_by']) && isLoggedIn()) {
    $data['created_by'] = session('login')->user_id;
  } else if (empty($data['created_by'])) {
    $data['created_by'] = 119; // System.
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
    $data['expired_date'] = date('Y-m-d H:i:s', strtotime('+1 day', time())); // Compatibility
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
 * @param string $message Error message.
 */
function setLastError(string $message = null)
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
