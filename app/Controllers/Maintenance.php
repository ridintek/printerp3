<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{
  Attachment,
  DB,
  MaintenanceLog,
  Product,
  ProductCategory,
  ProductReport,
  Stock,
  Unit,
  User,
  WAJob,
  Warehouse
};

class Maintenance extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getMaintenanceLogs()
  {
    checkPermission('MaintenanceLog.View');

    $dt = new DataTables('maintenance_logs');
    $dt
      ->select("maintenance_logs.product_code, products.name,
        subcategories.name AS subcategory_name,
        maintenance_logs.assigned_at, assigner.fullname AS assigner_name,
        maintenance_logs.fixed_at, pic.fullname AS pic_name,
        products.warehouses AS warehouse_name,
        maintenance_logs.note")
      ->join('products', 'products.id = maintenance_logs.product_id', 'left')
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->join('categories subcategories', 'subcategories.id = products.subcategory_id', 'left')
      ->join('users assigner', 'assigner.id = maintenance_logs.assigned_by', 'left')
      ->join('users pic', 'pic.id = maintenance_logs.pic_id', 'left')
      ->generate();
  }

  public function getMaintenanceReports()
  {
    checkPermission('Maintenance.View');

    $dt = new DataTables('products');
    $dt
      ->select("products.id AS id, products.id AS cid, products.code, products.name,
        categories.name AS category_name, subcategories.name AS subcategory_name,
        products.warehouses AS warehouse_name,
        products.json->>'$.condition' AS last_condition,
        products.json->>'$.updated_at' AS last_update,
        pic.fullname AS pic_name,
        products.json->>'$.updated_at' AS last_check")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->join('categories subcategories', 'subcategories.id = products.subcategory_id', 'left')
      ->join('users pic', "pic.id = JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.pic_id'))", 'left')
      ->whereIn('categories.code', ['AST', 'EQUIP'])
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('Maintenance.Add')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('maintenance/report/add/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-plus-square"></i> ' . lang('App.add') . '
            </a>';
        }

        $menu .= '
          <a class="dropdown-item" href="' . base_url('maintenance/report/view/' . $data['id']) . '"
            data-toggle="modal" data-target="#ModalStatic"
            data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
            <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
          </a>';

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('cid', function ($data) {
        return "<input class=\"checkbox\" type=\"checkbox\" value=\"{$data['cid']}\">";
      })
      ->editColumn('last_condition', function ($data) {
        return renderStatus($data['last_condition']);
      })
      ->editColumn('last_check', function ($data) {
        $todayCheck = date('Y-m-d', strtotime($data['last_check']));
        $todayDate  = date('Y-m-d');
        $hasUpdated = ($todayCheck == $todayDate ? TRUE : FALSE);

        return ($hasUpdated ? '<div class="text-center"><i class="fad fa-2x fa-check"></i></div>' : '');
      })
      ->generate();
  }

  public function getMaintenanceSchedules()
  {
    checkPermission('Maintenance.View');

    $dt = new DataTables('warehouse');

    $dt
      ->select("warehouse.id AS id, warehouse.name,
        warehouse.json AS tsname,
        warehouse.json AS auto_assign", FALSE)
      ->where('warehouse.active', 1)
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('MaintenanceSchedule.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('maintenance/schedule/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('tsname', function ($data) {
        $js = getJSON($data['tsname']);
        $maintenances = ($js->maintenances ?? []);
        $res = '<ul style="list-style:inside;">';

        foreach ($maintenances as $mt) {
          $category = ProductCategory::getRow(['code' => $mt->category]);
          $tsname = '-';

          if (!$category) continue;

          if (!empty($mt->pic)) {
            $user = User::getRow(['id' => $mt->pic]);
            $tsname = $user->fullname;
          }

          $res .= "<li>{$category->name}: {$tsname}</li>";
        }

        $res .= '</ul>';

        return trim($res);
      })
      ->editColumn('auto_assign', function ($data) {
        $js = getJSON($data['auto_assign']);
        $maintenances = ($js->maintenances ?? []);
        $res = '<ul style="list-style:inside;">';

        foreach ($maintenances as $mt) {
          $category = ProductCategory::getRow(['code' => $mt->category]);

          if (!$category) continue;

          $auto_assign = (!empty($mt->auto_assign) && $mt->auto_assign == 1 ? 'Yes' : 'No');

          $res .= "<li>{$category->name}: {$auto_assign}</li>";
        }

        $res .= '</ul>';

        return trim($res);
      })
      ->generate();
  }

  public function log()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('MaintenanceLog.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.maintenance'), 'slug' => 'maintenance', 'url' => '#'],
        ['name' => lang('App.maintenancelog'), 'slug' => 'maintenancelog', 'url' => '#']
      ],
      'content' => 'Maintenance/Log/index',
      'title' => lang('App.maintenancelog')
    ];

    return $this->buildPage($this->data);
  }

  public function report()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('MaintenanceReport.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.maintenance'), 'slug' => 'maintenance', 'url' => '#'],
        ['name' => lang('App.maintenancereport'), 'slug' => 'maintenancereport', 'url' => '#']
      ],
      'content' => 'Maintenance/Report/index',
      'title' => lang('App.maintenancereport')
    ];

    return $this->buildPage($this->data);
  }

  protected function report_add($productId = null)
  {
    $product = Product::getRow(['id' => $productId]);

    if (!$product) {
      $this->response(404, ['message' => 'Item is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $date         = dateTimePHP(getPost('date'));
      $condition    = getPost('condition');
      $createdBy    = getPost('created_by');
      $warehouseId  = getPost('warehouse');
      $note         = getPost('note');
      $noteTS       = getPost('note_ts');

      if (empty($condition)) {
        $this->response(400, ['message' => 'Condition harus di isi.']);
      }

      if (($condition == 'off' || $condition == 'trouble') && empty($note)) {
        $this->response(400, ['message' => 'Note tidak boleh kosong.']);
      }

      $lastReport = ProductReport::select('*')->where('product_id', $productId)->orderBy('id', 'DESC')->getRow();

      $warehouse = Warehouse::getRow(['id' => $warehouseId]);

      $data = [
        'product_id'    => $productId,
        'warehouse_id'  => $warehouse->id,
        'condition'     => $condition,
        'note'          => $note,
        'pic_note'      => $noteTS,
        'created_at'    => $date,
        'created_by'    => $createdBy
      ];

      DB::transStart();

      $data = $this->useAttachment($data);

      if (!ProductReport::add($data)) {
        $this->response(400, ['message' => getLastError()]);
      }

      $itemJS = getJSON($product->json);

      $itemJS->note     = $note;
      $itemJS->pic_note = $noteTS;
      $json = json_encode($itemJS);

      if (!Product::update((int)$productId, [
        'json'      => $json,
        'json_data' => $json
      ])) {
        $this->response(400, ['message' => getLastError()]);
      }

      $assigner = (!empty($itemJS->assigned_by) ? User::getRow(['id' => $itemJS->assigned_by]) : null);
      $pic      = User::getRow(['id' => $itemJS->pic_id]);
      $user     = User::getRow(['id' => $createdBy]);

      if ($condition == 'solved') {
        if (!empty($lastReport) && $lastReport->condition != 'good') {
          // Send report to CS/TL if status has been solved.
          if ($user->phone && $assigner) {
            $message = "Hi {$user->fullname},\n\n" .
              "Item berikut telah dilakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . dtLocal($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . dtLocal($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n\n" .
              "Silakan ubah status ke *Good* jika sudah benar.\n\n" .
              "Terima kasih.";

            WAJob::add(['phone' => $user->phone, 'message' => $message]);
          }
        }
      }

      if ($condition == 'good') { // Reset if machine is good.
        // If last status is solved.
        if (!empty($lastReport) && $lastReport->condition == 'solved') {
          // Send report to PIC/TS if status has been good.
          if ($pic && $pic->phone && $assigner) {
            $message = "Hi {$pic->fullname},\n\n" .
              "Terima kasih telah melakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . dtLocal($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . dtLocal($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n";

            WAJob::add(['phone' => $pic->phone, 'message' => $message]);
          }

          // Send report to CS/TL if status has been good.
          if ($user->phone && $assigner) {
            $message = "Hi {$user->fullname},\n\n" .
              "Item berikut telah berhasil dilakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . dtLocal($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . dtLocal($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n\n" .
              "Jangan lupa untuk memberikan review bintang 5 kepada TS pada link berikut:\n\n" .
              admin_url("machines?code={$product->code}\n\n") .
              "Terima kasih.";

            WAJob::add(['phone' => $user->phone, 'message' => $message]);
          }

          // Add maintenance log.
          if (!MaintenanceLog::add([
            'product_id'      => $product->id,
            'product_code'    => $product->code,
            'assigned_at'     => (!empty($itemJS->assigned_at) ? $itemJS->assigned_at : $date),
            'assigned_by'     => (!empty($itemJS->assigned_by) ? $itemJS->assigned_by : 1),
            'fixed_at'        => $date,
            'pic_id'          => $itemJS->pic_id,
            'warehouse_id'    => $warehouse->id,
            'warehouse_code'  => $warehouse->code,
            'note'            => $note,
            'pic_note'        => $noteTS,
            'created_by'      => $createdBy
          ])) {
            $this->response(400, ['message' => getLastError()]);
          }
        } else if (!empty($lastReport) && $lastReport->condition != 'good') {
          $this->response(400, ['message' => 'Status harus <b>Solved</b> dahulu sebelum di <b>Good</b>.']);
        }

        $itemJS->pic_id       = '';
        $itemJS->assigned_at  = '';
        $itemJS->assigned_by  = '';
        $itemJS->pic_note     = '';
        $json = json_encode($itemJS);

        // Reset product.
        if (!Product::update((int)$productId, [
          'json'      => $json,
          'json_data' => $json
        ])) {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      // Auto Assign TS.
      if ($condition == 'off' || $condition == 'trouble') {
        $whJS = getJSON($warehouse->json);
        $maintenances = ($whJS->maintenances ?? []);

        // If has maintenance schedule and pic is empty. Do not overwrite PIC if present!
        if ($maintenances && empty($itemJS->pic_id)) {
          if ($subcat = ProductCategory::getRow(['id' => $product->subcategory_id])) {
            foreach ($maintenances as $schedule) {
              if (empty($schedule->pic)) continue;

              if ($schedule->category == $subcat->code) {
                $picId = ($picId ?? $schedule->pic);

                // Send report to PIC/TS about problem.
                if ($user = User::getRow(['id' => $picId])) {
                  if (!empty($user->phone)) {
                    $message = "Hi {$user->fullname},\n\n" .
                      "Outlet *{$warehouse->name}* membutuhkan perbaikan berikut:\n\n" .
                      "*Item Code*: {$product->code}\n" .
                      "*Item Name*: {$product->name}\n" .
                      "*Condition*: _*" . ucfirst($condition) . "*_\n" .
                      "*User Note*: _" . htmlRemove($note) . "_\n\n" .
                      "Mohon untuk segera diperbaiki. Terima kasih";

                    WAJob::add(['phone' => $user->phone, 'message' => $message]);
                  }
                }

                if (isset($schedule->auto_assign) && $schedule->auto_assign == 1) {
                  $itemJS->pic_id       = $picId;
                  $itemJS->assigned_at  = $date;
                  $itemJS->assigned_by  = $createdBy;
                  $json = json_encode($itemJS);

                  // Reset product.
                  if (!Product::update((int)$productId, [
                    'json'      => $json,
                    'json_data' => $json
                  ])) {
                    $this->response(400, ['message' => getLastError()]);
                  }
                }
              }
            }
          }
        }
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Report has been added.']);
      }

      $this->response(400, ['message' => 'Failed to add report.']);
    }

    $this->data['title']    = lang('App.addmaintenancereport');
    $this->data['product']  = $product;

    $this->response(200, ['content' => view('Maintenance/Report/add', $this->data)]);
  }

  public function schedule()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('MaintenanceSchedule.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.maintenance'), 'slug' => 'maintenance', 'url' => '#'],
        ['name' => lang('App.maintenanceschedule'), 'slug' => 'maintenanceschedule', 'url' => '#']
      ],
      'content' => 'Maintenance/Schedule/index',
      'title' => lang('App.maintenanceschedule')
    ];

    return $this->buildPage($this->data);
  }

  protected function schedule_edit($warehouseId)
  {
    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $groups = getPost('group'); // Each group or Each warehouse.
      $m = [];
      $whJS = getJSON($warehouse->json);

      foreach ($groups as $group) {
        $m[] = $group;
      }

      $whJS->maintenances = $m;

      if (!Warehouse::update((int)$warehouseId, ['json' => json_encode($whJS)])) {
        $this->response(400, ['message' => getLastError()]);
      }

      $this->response(200, ['message' => 'Jadwal berhasil diubah.']);
    }

    $this->data['title']      = lang('App.editmaintenanceschedule');
    $this->data['warehouse']  = $warehouse;

    $this->response(200, ['content' => view('Maintenance/Schedule/edit', $this->data)]);
  }
}
