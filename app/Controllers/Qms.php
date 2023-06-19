<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{Customer, DB, QueueCategory, QueueSession, QueueTicket, User, Warehouse};

class Qms extends BaseController
{
  public function getQueueTickets()
  {
    checkPermission('QMS.View');

    $dt = new DataTables('queue_tickets');
    $dt
      ->select('queue_tickets.id, queue_tickets.date, queue_tickets.call_date, queue_tickets.serve_date,
        queue_tickets.end_date,  customers.name AS customer_name, queue_tickets.token,
        queue_tickets.queue_category_name, queue_tickets.warehouse_name, queue_tickets.status2,
        queue_tickets.counter, caller.fullname')
      ->join('customers', 'customers.id = queue_tickets.customer_id', 'left')
      ->join('users caller', 'caller.id = queue_tickets.user_id', 'left')
      ->editColumn('id', function ($data) {
        return '
        <div class="btn-group btn-action">
          <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
            <i class="fad fa-gear"></i>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="' . base_url('qms/counter?recall=' . $data['id']) . '"
              target="_blank">
              <i class="fad fa-fw fa-megaphone"></i> ' . lang('App.recall') . '
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('qms/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>
          </div>
        </div>';
      })
      ->editColumn('status2', function ($data) {
        return renderStatus($data['status2']);
      });

    $userJS = getJSON(session('login')?->json);
    $warehouse = [];

    if (isset($userJS->warehouses) && !empty($userJS->warehouses)) {
      if ($warehouse) {
        $warehouse = array_merge($warehouse, $userJS->warehouses);
      } else {
        $warehouse = $userJS->warehouses;
      }
    }

    if (session('login')->warehouse_id) {
      if ($warehouse) {
        $warehouse[] = session('login')->warehouse_id;
      } else {
        $warehouse = [session('login')->warehouse_id];
      }
    }

    if ($warehouse) {
      $dt->whereIn('queue_tickets.warehouse_id', $warehouse);
    }

    if ($wh = session('login')->warehouse) {
      $warehouse = Warehouse::getRow(['code' => $wh]);
      $dt->where('queue_tickets.warehouse_id', $warehouse->id);
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

    checkPermission('QMS.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.qms'), 'slug' => 'qms', 'url' => '#'],
        ['name' => lang('App.queue'), 'slug' => 'queue', 'url' => '#']
      ],
      'content' => 'QMS/index',
      'title' => lang('App.queue')
    ];

    return $this->buildPage($this->data);
  }

  public function addQueueTicket()
  {
    checkPermission('QMS.Registration');

    $name         = getPost('name');
    $phone        = getPost('phone');
    $categoryId   = getPost('category');
    $warehouseId  = getPost('warehouse');

    $data = [
      'name'              => $name,
      'phone'             => $phone,
      'queue_category_id' => $categoryId,
      'warehouse_id'      => $warehouseId
    ];

    DB::transStart();

    $insertID = QueueTicket::addQueue($data);

    if (!$insertID) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $ticket = QueueTicket::getRow(['id' => $insertID]);

      $this->response(200, ['data' => $ticket]);
    }

    $this->response(400, ['message' => 'Cannot create Queue Ticket.']);
  }

  public function callQueueTicket($id = null)
  {
    $warehouseId = ($id ?? 'LUC');

    if (is_numeric($warehouseId)) {
      $warehouse = Warehouse::getRow(['id' => $warehouseId]);
    } else {
      $warehouse = Warehouse::getRow(['code' => $warehouseId]);
    }

    $callData = [
      'user_id'       => session('login')->user_id,
      'counter'       => session('login')->counter,
      'warehouse_id'  => $warehouse->id
    ];

    if ($response = QueueTicket::callQueue($callData)) {
      $this->response(200, ['data' => $response]);
    }

    $this->response(400, ['message' => 'No Queue Ticket is available.']);
  }

  public function counter()
  {
    checkPermission('QMS.Counter');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.qms'), 'slug' => 'qms', 'url' => '#'],
        ['name' => lang('App.counter'), 'slug' => 'counter', 'url' => '#']
      ],
      'content' => 'QMS/counter',
      'title' => lang('App.counter')
    ];

    return $this->buildPage($this->data);
  }

  public function delete($id = null)
  {
    checkPermission('QMS.Delete');

    $ticket = QueueTicket::getRow(['id' => $id]);

    if (!$ticket) {
      $this->response(404, ['message' => 'Queue Ticket is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = QueueTicket::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Queue Ticket has been deleted.']);
      }

      $this->response(400,  ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete Queue Ticket.']);
  }

  /**
   * Display for QMS.
   */
  public function display($id = null)
  {
    checkPermission('QMS.Display');

    $id = ($id ?? session('login')->warehouse_id);

    if (!$id) { // Default
      $id = 'LUC';
    }

    if (is_numeric($id)) {
      $warehouse = Warehouse::getRow(['id' => $id]);
    } else {
      $warehouse = Warehouse::getRow(['code' => $id]);
    }

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    $this->data['active']     = (getGet('active') == 1);
    $this->data['warehouse']  = $warehouse;

    return view('QMS/display', $this->data);
  }

  /**
   * Display will call this function if any ticket to be call.
   */
  public function displayResponse($ticketId = null)
  {
    if ($ticketId) {
      if (QueueTicket::update((int)$ticketId, [
        'status' => QueueTicket::STATUS_CALLED,
        'status2' => QueueTicket::toStatus(QueueTicket::STATUS_CALLED)
      ])) {
        $this->response(200, ['message' => 'Queue Ticket has been called.']);
      } else {
        $this->response(400, ['message' => 'Failed to call Queue Ticket.']);
      }
    }

    $this->response(400, ['message' => 'Ticket id is not set.']);
  }

  public function endQueueTicket()
  {
    $ticketId = getPost('ticket');

    $queueData = [
      'serve_time' => (getPOST('serve_time') ?? null)
    ];

    if (QueueTicket::endQueue((int)$ticketId, $queueData)) {
      $this->response(200, ['message' => 'Queue Ticket has been ended.']);
    }

    $this->response(400, ['message' => 'Failed to end Queue Ticket.']);
  }

  public function getCustomers()
  {
    $phone = getGet('term');

    $customers = Customer::select('name, phone')->where('phone', $phone)->get();
    $data = [];

    if ($customers) {
      foreach ($customers as $customer) {
        $data[] = ['id' => $customer->phone, 'text' => $customer->phone, 'name' => $customer->name];
      }
    }

    $this->response(200, ['results' => $data]);
  }

  /**
   * Display will call this function intervally.
   */
  public function getDisplayData($id = null)
  {
    if ($id) {
      if (is_numeric($id)) {
        $warehouse = Warehouse::getRow(['id' => $id]);
      } else {
        $warehouse = Warehouse::getRow(['code' => $id]);
      }

      $displayData = [
        'call'       => [],
        'counter'    => [],
        'queue_list' => [],
        'skip_list'  => []
      ];

      $call = QueueTicket::select('*')
        ->like('date', date('Y-m-d'), 'after')
        ->where('warehouse_id', $warehouse->id)
        ->where('status', QueueTicket::STATUS_CALLING)
        ->getRow();

      if ($call) {
        $displayData['call'] = ['code' => 200, 'data' => $call];
      } else {
        $displayData['call'] = ['code' => 404, 'data' => [], 'message' => 'No queue ticket to call.'];
      }

      $counters = QueueTicket::getTodayOnlineCounters((int)$warehouse->id);

      if ($counters) {
        foreach ($counters as $counter) {
          $queueCategory = QueueCategory::getRow(['id' => $counter->queue_category_id]);

          $counterList[] = [
            'counter' => $counter->counter,
            'name' => explode(' ', $counter->fullname)[0],
            'token' => $counter->token,
            'category_name' => (!empty($queueCategory) ? $queueCategory->name : null)
          ];
        }

        $displayData['counter'] = ['code' => 200, 'data' => $counterList];
      } else {
        $displayData['counter'] = ['code' => 404, 'data' => [], 'message' => 'No counter online.'];
      }

      // Get today queue ticket list.
      $queueLists = QueueTicket::select('*')
        ->like('date', date('Y-m-d'), 'after')
        ->where('warehouse_id', $warehouse->id)
        ->where('status', QueueTicket::STATUS_WAITING)
        ->orderBy('date', 'ASC') // ASC from first get ticket.
        ->get();

      if ($queueLists) {
        foreach ($queueLists as $ticket) {
          $customer = Customer::getRow(['id' => $ticket->customer_id]);

          $queueList[] = [
            'customer_id' => intval($customer->id),
            'customer_name' => $customer->name,
            'est_call_date' => $ticket->est_call_date,
            'queue_category_id' => intval($ticket->queue_category_id),
            'queue_category_name' => $ticket->queue_category_name,
            'token' => $ticket->token,
            'user_id' => ($ticket->user_id ? intval($ticket->user_id) : $ticket->user_id),
            'warehouse_id' => intval($ticket->warehouse_id)
          ];
        }

        $displayData['queue_list'] = ['code' => 200, 'data' => $queueList];
      } else {
        $displayData['queue_list'] = ['code' => 404, 'data' => [], 'message' => 'No queue ticket available.'];
      }

      $expMinute = 20; // Hardcoded for 20 minutes.
      $date = date('Y-m-d H:i:s', strtotime("-{$expMinute} minute"));

      // Get today skipped queue ticket list.
      $skipLists = QueueTicket::select('*')
        ->like('date', date('Y-m-d'), 'after')
        ->where("est_call_date > '{$date}'")
        ->where('warehouse_id', $warehouse->id)
        ->where('status', QueueTicket::STATUS_SKIPPED)
        ->orderBy('date', 'ASC')
        ->get();

      if ($skipLists) {
        foreach ($skipLists as $ticket) {
          $customer = Customer::getRow(['id' => $ticket->customer_id]);

          $skipList[] = [
            'customer_id' => intval($customer->id),
            'customer_name' => $customer->name,
            'est_call_date' => $ticket->est_call_date,
            'queue_category_id' => intval($ticket->queue_category_id),
            'queue_category_name' => $ticket->queue_category_name,
            'token' => $ticket->token,
            'user_id' => ($ticket->user_id ? intval($ticket->user_id) : $ticket->user_id),
            'warehouse_id' => intval($ticket->warehouse_id)
          ];
        }

        $displayData['skip_list'] = ['code' => 200, 'data' => $skipList];
      } else {
        $displayData['skip_list'] = ['code' => 404, 'data' => [], 'message' => 'No skipped ticket available.'];
      }

      $this->response(200, ['data' => $displayData]);
    }

    $this->response(400, ['message' => 'Warehouse is not found.']);
  }

  public function recallQueueTicket($id = null)
  {
    $ticket = QueueTicket::getRow(['id' => $id]);

    if (!$ticket) {
      $this->response(404, ['message' => 'Queue Ticket is not found.']);
    }

    if (QueueTicket::update((int)$id, [
      'status' => QueueTicket::STATUS_CALLING,
      'status2' => QueueTicket::toStatus(QueueTicket::STATUS_CALLING)
    ])) {
      $this->response(200, [
        'data' => QueueTicket::getQueueTicketById((int)$id),
        'message' => 'Queue Ticket has been recalled.'
      ]);
    }

    $this->response(400, ['message' => 'Failed to recall Queue Ticket.']);
  }

  public function registration($id = null)
  {
    checkPermission('QMS.Registration');

    $id = ($id ?? session('login')->warehouse_id);

    if (!$id) { // Default
      $id = 'LUC';
    }

    if (is_numeric($id)) {
      $warehouse = Warehouse::getRow(['id' => $id]);
    } else {
      $warehouse = Warehouse::getRow(['code' => $id]);
    }

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    $this->data['warehouse'] = $warehouse;

    return view('QMS/registration', $this->data);
  }

  public function serveQueueTicket($id = null)
  {
    if (QueueTicket::serveQueue((int)$id)) {
      $this->response(200, ['message' => 'Queue Ticket has been served.']);
    }

    $this->response(400, ['message' => 'Cannot serving Queue Ticket.']);
  }

  public function setCounter($counter = null)
  {
    if (!is_numeric($counter)) {
      $this->response(400, ['message' => 'Counter is not number.']);
    }

    $userId = session('login')->user_id;

    $warehouseId  = (session('login')->warehouse_id ?? 'LUC');

    if (is_numeric($warehouseId)) {
      $warehouse = Warehouse::getRow(['id' => $warehouseId]);
    } else {
      $warehouse = Warehouse::getRow(['code' => $warehouseId]);
    }

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    DB::transStart();

    User::update((int)$userId, ['counter' => $counter]);

    $onlineCounters = QueueTicket::getTodayOnlineCounters((int)$warehouse->id);

    if ($onlineCounters) {
      foreach ($onlineCounters as $onlineCounter) {
        if ($onlineCounter->counter == $counter && $onlineCounter->id != $userId) { // Make offline to another user.
          // Set counter to offline.
          User::update((int)$onlineCounter->id, ['counter' => 0, 'token' => null, 'queue_category_id' => 0]);
        }
      }
    }

    if ($counter > 0) {
      // Prevent duplicate queue session.
      if (!QueueSession::getTodayQueueSession((int)$userId)) {
        $sessionData = [
          'user_id'       => $userId,
          'warehouse_id'  => $warehouse->id
        ];

        QueueSession::add($sessionData); // Start Queue session.
      }
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $login = session('login');

      $login->counter = $counter;

      session()->set('login', $login);

      $this->response(200, ['message' => 'Set counter to ' . $counter]);
    }

    $this->response(400, ['message' => 'Failed to set counter']);
  }

  public function skipQueueTicket()
  {
    $ticketId = getPost('ticket');

    if (QueueTicket::skipQueue((int)$ticketId)) {
      $this->response(200, ['message' => 'Queue Ticket has been skipped.']);
    }

    $this->response(400, ['message' => 'Failed to skip Queue Ticket.']);
  }
}
