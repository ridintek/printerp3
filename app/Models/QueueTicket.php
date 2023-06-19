<?php

declare(strict_types=1);

namespace App\Models;

class QueueTicket
{
  const STATUS_WAITING = 1;
  const STATUS_CALLING = 2;
  const STATUS_CALLED  = 3;
  const STATUS_SERVING = 4;
  const STATUS_SERVED  = 5;
  const STATUS_SKIPPED = 6;

  /**
   * Add new QueueTicket.
   */
  public static function addQueue(array $data)
  {
    if (empty($data['phone'])) {
      setLastError('Phone number is empty.');
      return false;
    }

    if (empty($data['name'])) {
      setLastError('Customer name is empty.');
      return false;
    }

    if (empty($data['queue_category_id'])) {
      setLastError('Queue Category is empty.');
      return false;
    }

    if (empty($data['warehouse_id'])) {
      setLastError('Warehouse is empty.');
      return false;
    }

    $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']); // Filter phone number.

    $category   = QueueCategory::getRow(['id' => $data['queue_category_id']]);

    if (!$category) {
      setLastError('Queue Category is not found.');
      return false;
    }

    $warehouse  = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if (!$warehouse) {
      setLastError('Warehouse is not found.');
      return false;
    }

    $customer = Customer::getRow(['phone' => $data['phone']]);

    if (!$customer) {
      $insertID = Customer::add([
        'company'             => '',
        'customer_group_id'   => 1, // Reguler
        'group_name'          => 'QMS',
        'name'                => $data['name'],
        'phone'               => $data['phone']
      ]);

      if (!$insertID) {
        return false;
      }

      $customer = Customer::getRow(['id' => $insertID]);
    }

    unset($data['name'], $data['phone']);

    // Begin Prevent Duplicate entries.
    $lastTicket = self::select('*')->orderBy('date', 'DESC')->getRow([
      'customer_id'   => $customer->id,
      'status'        => self::STATUS_WAITING,
      'warehouse_id'  => $data['warehouse_id']
    ]);


    if ($lastTicket) {
      setLastError("Anda sudah mengambil tiket sebelumnya.<br> No tiket terakhir anda {$lastTicket->token}.");
      return false;
    }
    // End Prevent Duplicate entries.

    // Begin get estimated call date.
    $servingQueues = self::select('*')->like('date', date('Y-m-d'), 'after')->get([
      'status' => self::STATUS_SERVING,
      'warehouse_id' => $data['warehouse_id']
    ]);

    $waitingQueues = self::select('*')->like('date', date('Y-m-d'), 'after')->get([
      'status' => self::STATUS_WAITING,
      'warehouse_id' => $data['warehouse_id'],
    ]);

    $waitTime = 0;

    if ($servingQueues && $servingQueues[0]->queue_category_name == 'Siap Cetak') {
      $waitTime += 10;
    } else if ($servingQueues && $servingQueues[0]->queue_category_name == 'Edit Design') {
      $waitTime += 20;
    }

    foreach ($waitingQueues as $waitQueue) {
      if ($waitQueue->queue_category_name == 'Siap Cetak') {
        $waitTime += 10;
      } else if ($waitQueue->queue_category_name == 'Edit Design') {
        $waitTime += 20;
      }
    };

    $di = new \DateInterval("PT{$waitTime}M");

    $estCallDate = new \DateTime('now', new \DateTimeZone('Asia/Jakarta')); // Current datetime.
    $estCallDate->add($di);

    $est_call_date = $estCallDate->format('Y-m-d H:i:s');
    // End get estimated call date.

    $data['customer_id']          = $customer->id;
    $data['est_call_date']        = getQueueDateTime($est_call_date);
    $data['warehouse_name']       = $warehouse->name;
    $data['queue_category_name']  = $category->name;
    $data['status']               = self::STATUS_WAITING;
    $data['status2']              = self::toStatus(self::STATUS_WAITING);
    $data['token']                = self::generateNewTicketToken($data);

    $data['date'] = date('Y-m-d H:i:s');

    DB::table('queue_tickets')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Call a queue ticket.
   * @param array $data [ *user_id, *warehouse_id ]
   */
  public static function callQueue(array $data)
  {
    $warehouse = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if (!$warehouse) {
      setLastError('Warehouse is not found.');
      return false;
    }

    // Get callable ticket.
    $ticket = self::select('*')
      ->where('warehouse_id', $warehouse->id)
      ->where('status', self::STATUS_WAITING)
      ->orderBy('date', 'ASC')
      ->getRow();


    if ($ticket) {
      $user = User::getRow(['id' => $data['user_id']]);

      if (!$user) {
        setLastError('User is not found.');
        return false;
      }

      $call_date   = date('Y-m-d H:i:s');
      $create_date = $ticket->date;

      $callDate = new \DateTime($call_date);
      $createDate = new \DateTime($create_date);
      $wait_time = $createDate->diff($callDate)->format('%H:%I:%S');

      $ticketData = [
        'call_date' => $call_date,
        'wait_time' => $wait_time, // OK.
        'counter' => $user->counter,
        'status'  => self::STATUS_CALLING, // 2 = To be call by Display.
        'status2' => self::toStatus(self::STATUS_CALLING),
        'user_id' => $user->id,
      ];

      if (self::update((int)$ticket->id, $ticketData)) {
        User::update((int)$user->id, ['token' => $ticket->token, 'queue_category_id' => $ticket->queue_category_id]);
        return self::getQueueTicketById((int)$ticket->id);
      }

      return null;
    }

    return null;
  }

  /**
   * Delete QueueTicket.
   */
  public static function delete(array $where)
  {
    DB::table('queue_tickets')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  public static function endQueue(int $ticketId, array $data)
  {
    $ticket   = self::getRow(['id' => $ticketId]);
    $category = QueueCategory::getRow(['id' => $ticket->queue_category_id]);

    if (!empty($data['serve_time'])) { // 00:05:00
      $st = explode(':', $data['serve_time']);

      if (!is_array($st) || (is_array($st) && count($st) != 3)) {
        setLastError('serve_time format is invalid.');
        return FALSE;
      }

      $di = new \DateInterval('PT' . intval($st[0]) . 'H' . intval($st[1]) . 'M' . intval($st[2]) . 'S');
      $endDate = new \DateTime($ticket->serve_date); // Calculate since serve_date + serve_time = end_date.
      $endDate->add($di);
      $end_date = $endDate->format('Y-m-d H:i:s');
    } else {
      $end_date = date('Y-m-d H:i:s');
      $endDate = new \DateTime($end_date);
    }

    $serveDate = new \DateTime($ticket->serve_date);

    $serve_time = $serveDate->diff($endDate)->format('%H:%I:%S');
    $limitDate = new \DateTime(date('Y-m-d ') . $category->duration); // 00:10:00
    $overDate  = new \DateTime(date('Y-m-d ') . $serve_time); // 00:12:00

    $diffOver = $overDate->diff($limitDate);

    // Check if minus then overtime.
    $over_time = ($diffOver->format('%r') == '-' ? $overDate->diff($limitDate)->format('%H:%I:%S') : '00:00:00');

    if (self::update((int)$ticketId, [
      'end_date'    => $end_date,
      'over_time'   => $over_time, // OK
      'serve_time'  => $serve_time, // OK
      'status'      => self::STATUS_SERVED,
      'status2'     => self::toStatus(self::STATUS_SERVED),
    ])) {
      return true;
    }

    return false;
  }

  /**
   * Format ticket.
   * @param int $number Number of ticket to format.
   * @param string Return ticket number. Ex. 003, 006, 009, 012, ...
   */
  public static function formatTicket(int $number)
  {
    return ($number < 10 ? '00' . $number : ($number < 100 ? '0' . $number : $number));
  }

  /**
   * Generate new queue ticket token.
   * @param array $data [ *queue_category_id, *warehouse_id ]
   */
  public static function generateNewTicketToken(array $data)
  {
    $queueCategory = QueueCategory::getRow(['id' => $data['queue_category_id']]);
    $lastTicket = self::select('*')
      ->where('queue_category_id', $data['queue_category_id'])
      ->where('warehouse_id', $data['warehouse_id'])
      ->orderBy('date', 'DESC')
      ->getRow();

    if ($lastTicket) {
      $ticketNumber = intval(str_replace($queueCategory->prefix, '', $lastTicket->token));
      $ticketNumber++;

      return $queueCategory->prefix . self::formatTicket($ticketNumber);
    }

    // If not ticket present.
    return $queueCategory->prefix . '001'; // For first ticket.
  }

  /**
   * Get QueueTicket collections.
   */
  public static function get($where = [])
  {
    return DB::table('queue_tickets')->get($where);
  }

  public static function getQueueTicketById(int $ticketId)
  {
    return self::select("queue_tickets.*,
      queue_categories.prefix, queue_categories.attempt,
      queue_categories.duration,
      customers.id AS customer_id, customers.name AS customer_name, customers.phone AS customer_phone")
      ->from('queue_tickets')
      ->join('queue_categories', 'queue_categories.id = queue_tickets.queue_category_id', 'left')
      ->join('customers', 'customers.id = queue_tickets.customer_id', 'left')
      ->where('queue_tickets.id', $ticketId)
      ->getRow();
  }

  /**
   * Get QueueTicket row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  public static function getTodayOnlineCounters(int $warehouseId)
  {
    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    $user = User::select('*')
      ->where('warehouse_id', $warehouse->id);

    if ($warehouse->code == 'LUC') {
      $user->orWhere('warehouse_id IS null');
    }

    return $user
      ->where('counter > 0')
      ->orderBy('counter', 'ASC')
      ->get();
  }

  /**
   * Select QueueTicket.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('queue_tickets')->select($columns, $escape);
  }

  public static function serveQueue(int $ticketId)
  {
    if (self::update($ticketId, [
      'serve_date'  => date('Y-m-d H:i:s'),
      'status'      => self::STATUS_SERVING,
      'status2'     => self::toStatus(self::STATUS_SERVING),
    ])) {
      return true;
    }

    return false;
  }

  public static function skipQueue(int $ticketId)
  {
    if (self::update($ticketId, [
      'end_date'  => date('Y-m-d H:i:s'),
      'status'    => self::STATUS_SKIPPED,
      'status2'   => self::toStatus(self::STATUS_SKIPPED),
    ])) {
      return true;
    }
    return false;
  }

  /**
   * Convert status (int) to status (string).
   */
  public static function toStatus(int $status)
  {
    switch ($status) {
      case self::STATUS_WAITING;
        return 'waiting';
      case self::STATUS_CALLING;
        return 'calling';
      case self::STATUS_CALLED;
        return 'called';
      case self::STATUS_SERVING;
        return 'serving';
      case self::STATUS_SERVED;
        return 'served';
      case self::STATUS_SKIPPED;
        return 'skipped';
      default:
        return null;
    }
  }

  /**
   * Update QueueTicket.
   */
  public static function update(int $id, array $data)
  {
    DB::table('queue_tickets')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
