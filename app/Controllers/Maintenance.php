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
    checkPermission('MaintenanceReport.View');

    $warehouses = getPost('warehouse');
    $status     = getPost('status');
    $pic        = getPost('pic');
    $startDate  = getPost('start_date');
    $endDate    = getPost('end_date');

    $param = '';

    if ($startDate) {
      $param .= '&start_date=' . $startDate;
    }

    if ($endDate) {
      $param .= '&end_date=' . $endDate;
    }

    if ($param) {
      $param = '?' . trim($param, '&');
    }

    $dt = new DataTables('products');
    $dt
      ->select("products.id AS id, products.id AS cid, products.code, products.name,
        categories.name AS category_name, subcategories.name AS subcategory_name,
        products.warehouses AS warehouse_name,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.condition')) AS last_condition,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.updated_at')) AS last_update,
        pic.fullname AS pic_name,
        JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.updated_at')) AS last_check")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->join('categories subcategories', 'subcategories.id = products.subcategory_id', 'left')
      ->join('users pic', "pic.id = JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.pic_id'))", 'left')
      ->join('warehouse', 'warehouse.name LIKE products.warehouses', 'left')
      ->whereIn('categories.code', ['AST', 'EQUIP'])
      ->editColumn('id', function ($data) use ($param) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('MaintenanceReport.Add')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('maintenance/report/add/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-plus-square"></i> ' . lang('App.add') . '
            </a>';
        }

        if (hasAccess('MaintenanceReport.Assign')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('maintenance/report/assign/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-plus-square"></i> ' . lang('App.assign') . '
            </a>';
        }

        $menu .= '
          <a class="dropdown-item" href="' . base_url('maintenance/report/view/' . $data['id']) . $param . '"
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
        $lastCheck  = (!empty($data['last_check']) ? strtotime($data['last_check']) : null);
        $todayCheck = ($lastCheck ? date('Y-m-d', $lastCheck) : null);
        $todayDate  = date('Y-m-d');
        $hasUpdated = ($todayCheck == $todayDate);

        return ($hasUpdated ? '<div class="text-center"><i class="fad fa-2x fa-check"></i></div>' : '');
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

    if ($status) {
      $dt->whereIn("JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.condition'))", $status);
    }

    if ($warehouses) {
      $dt->whereIn('warehouse.id', $warehouses);
    }

    if ($pic) {
      $dt->whereIn("JSON_UNQUOTE(JSON_EXTRACT(products.json, '$.pic_id'))", $pic);
    }

    $dt->generate();
  }

  public function getMaintenanceSchedules()
  {
    checkPermission('MaintenanceSchedule.View');

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

  public function getProductReport()
  {
    $productIds = getPostGet('product');
    $createdBy  = getPostGet('created_by');
    $startDate  = getPostGet('start_date') ?? date('Y-m-d', strtotime('-30 day'));
    $endDate    = getPostGet('end_date') ?? date('Y-m-d');

    checkPermission('MaintenanceReport.View');

    $dt = new DataTables('product_report');
    $dt
      ->select("product_report.id, product_report.created_at, product_report.condition,
        product_report.note, product_report.pic_note, creator.fullname AS creator_name")
      ->join('users creator', 'creator.id = product_report.created_by', 'left')
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('MaintenanceReport.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('maintenance/report/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic2"
              data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        if (hasAccess('MaintenanceReport.Delete')) {
          $menu .= '
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('maintenance/report/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('condition', function ($data) {
        return renderStatus($data['condition']);
      });

    if ($productIds) {
      $dt->whereIn('product_report.product_id', $productIds);
    }

    if ($createdBy) {
      $dt->whereIn('product_report.created_by', $createdBy);
    }

    if ($startDate) {
      $dt->where("product_report.created_at >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("product_report.created_at <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
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
    checkPermission('MaintenanceReport.Add');

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
        $this->response(400, ['message' => lang('App.condition') . ' harus di isi.']);
      }

      $condLang = lang('App.' . $condition);

      if (($condition == 'off' || $condition == 'trouble') && empty($note)) {
        $this->response(400, ['message' => "Note tidak boleh kosong saat kondisi {$condLang}."]);
      }

      if ($condition == 'good' && !hasAccess('MaintenanceReport.Good')) {
        $this->response(403, ['message' => "Anda tidak punya akses untuk mengubah ke {$condLang}."]);
      }

      if ($condition == 'solved' && !hasAccess('MaintenanceReport.Solved')) {
        $this->response(403, ['message' => "Anda tidak punya akses untuk mengubah ke {$condLang}."]);
      }

      if ($condition == 'off' && !hasAccess('MaintenanceReport.Off')) {
        $this->response(403, ['message' => "Anda tidak punya akses untuk mengubah ke {$condLang}."]);
      }

      if ($condition == 'trouble' && !hasAccess('MaintenanceReport.Trouble')) {
        $this->response(403, ['message' => "Anda tidak punya akses untuk mengubah ke {$condLang}."]);
      }

      $lastReport = ProductReport::select('*')->where('product_id', $productId)->orderBy('id', 'DESC')->getRow();

      $warehouse = Warehouse::getRow(['id' => $warehouseId]);

      // Validation.
      if ($condition == 'good') {
        if ($lastReport->condition != 'good' && $lastReport->condition != 'solved') {
          $this->response(400, ['message' => 'Status harus ' . lang('App.solved') . ' dahulu.']);
        }
      }

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

      $data = $this->useAttachment($data, null, function ($upload) use ($condition, $lastReport) {
        if ($upload->has('attachment')) {
          if ($upload->getSize('mb') > 2) {
            $this->response(400, ['message' => 'Attachment tidak boleh lebih dari 2MB.']);
          }
        }

        // Jika melakukan good.
        if ($lastReport->condition != 'good' && $condition == 'solved' && !$upload->has('attachment')) {
          $this->response(400, ['message' => 'Attachment harus disertakan.']);
        }
      });

      if (!ProductReport::add($data)) {
        $this->response(400, ['message' => getLastError()]);
      }

      $itemJS = getJSON($product->json);

      $itemJS->condition  = $condition;
      $itemJS->note       = $note;
      $itemJS->pic_note   = $noteTS;
      $itemJS->updated_at = date('Y-m-d H:i:s');
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

      if ($condition == 'good') { // Reset if machine is good.
        // If last status is solved.
        if ($lastReport && $lastReport->condition == 'solved') {
          // Send report to PIC/TS if status has been good.
          if ($pic && $pic->phone && $assigner) {
            $message = "Hi {$pic->fullname},\n\n" .
              "Terima kasih telah melakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . formatDateTime($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . formatDateTime($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n";

            if ($createdBy == 1) {
              WAJob::add(['phone' => '082311662064', 'message' => $message]);
            } else {
              WAJob::add(['phone' => $pic->phone, 'message' => $message]);
            }
          }

          // Send report to CS/TL if status has been good.
          if ($user->phone && $assigner) {
            $message = "Hi {$user->fullname},\n\n" .
              "Item berikut telah berhasil dilakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . formatDateTime($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . formatDateTime($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n\n" .
              "Jangan lupa untuk memberikan review bintang 5 kepada TS.\n\n" .
              "Terima kasih.";

            if ($createdBy == 1) {
              WAJob::add(['phone' => '082311662064', 'message' => $message]);
            } else {
              WAJob::add(['phone' => $user->phone, 'message' => $message]);
            }
          }

          // Add maintenance log.
          if (!MaintenanceLog::add([
            'product_id'      => $product->id,
            'product_code'    => $product->code,
            'assigned_at'     => (!empty($itemJS->assigned_at) ? $itemJS->assigned_at : $date),
            'assigned_by'     => (!empty($itemJS->assigned_by) ? $itemJS->assigned_by : $createdBy),
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
        } else if ($lastReport && $lastReport->condition != 'good') {
          $this->response(400, ['message' => 'Status harus <b>Solved</b> dahulu sebelum di <b>Good</b>.']);
        }

        $itemJS->assigned_at  = '';
        $itemJS->assigned_by  = '';
        $itemJS->pic_id       = '';
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

      // Solved by TechSupport.
      if ($condition == 'solved') {
        if (!empty($lastReport) && $lastReport->condition != 'good') {
          // Send report to CS/TL if status has been solved.
          if ($user->phone && $assigner) {
            $message = "Hi {$user->fullname},\n\n" .
              "Item berikut telah dilakukan perbaikan:\n\n" .
              "*Outlet*: {$warehouse->name}\n" .
              "*Assigned At*: " . ($itemJS->assigned_at) . "\n" .
              "*Assigned By*: {$assigner->fullname}\n" .
              "*Item Code*: {$product->code}\n" .
              "*Item Name*: {$product->name}\n" .
              "*Fixed At*: " . formatDateTime($date) . "\n" .
              "*Fixed By*: {$pic->fullname}\n" .
              "*User Note*: " . htmlRemove($note) . "\n" .
              "*TS Note*: " . htmlRemove($noteTS) . "\n\n" .
              "Silakan ubah status ke *Good* jika sudah benar.\n\n" .
              "Terima kasih.";

            if ($createdBy == 1) {
              WAJob::add(['phone' => '082311662064', 'message' => $message]);
            } else {
              WAJob::add(['phone' => $user->phone, 'message' => $message]);
            }
          }
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
                $picId = $schedule->pic;

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

                    if ($createdBy == 1) {
                      WAJob::add(['phone' => '082311662064', 'message' => $message]);
                    } else {
                      WAJob::add(['phone' => $user->phone, 'message' => $message]);
                    }
                  }
                }

                // Auto-Assign TechSupport.
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

  protected function report_assign($id = null)
  {
    checkPermission('MaintenanceReport.Assign');

    $product = Product::getRow(['id' => $id]);

    if (!$product) {
      $this->response(404, ['message' => 'Item is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $techsupport = getPost('techsupport');

      $productJS = getJSON($product->json);

      $productJS->pic_id = intval($techsupport);
      $productJS->assigned_by = intval(session('login')->user_id);
      $productJS->assigned_at = date('Y-m-d H:i:s');

      $json = json_encode($productJS);

      if (!Product::update((int)$id, ['json' => $json, 'json_data' => $json])) {
        $this->response(400, ['message' => getLastError()]);
      }

      $this->response(200, ['message' => "Item {$product->code} has been assigned."]);
    }

    $this->data['title']    = lang('App.assign');
    $this->data['product']  = $product;

    $this->response(200, ['content' => view('Maintenance/Report/assign', $this->data)]);
  }

  protected function report_delete($id = null)
  {
    checkPermission('MaintenanceReport.Delete');

    $report = ProductReport::getRow(['id' => $id]);

    if (!$report) {
      $this->response(404, ['message' => 'Product Report is not found.']);
    }

    $product = Product::getRow(['id' => $report->product_id]);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      if (!ProductReport::delete(['id' => $id])) {
        $this->response(400, ['message' => getLastError()]);
      }

      $lastReport = ProductReport::select('*')->where('product_id', $product->id)->orderBy('id', 'DESC')->getRow();

      if ($lastReport) {
        $itemJS = getJSON($product->json);

        $itemJS->condition  = $lastReport->condition;
        $itemJS->note       = $lastReport->note;
        $itemJS->pic_note   = $lastReport->pic_note;
        $itemJS->updated_at = $lastReport->created_at;
        $json = json_encode($itemJS);

        if (!Product::update((int)$product->id, [
          'json'      => $json,
          'json_data' => $json
        ])) {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Report has been deleted.']);
      }

      $this->response(400, ['message' => 'Product Report failed to delete.']);
    }
  }

  protected function report_edit($id = null)
  {
    checkPermission('MaintenanceReport.Edit');

    $report = ProductReport::getRow(['id' => $id]);

    if (!$report) {
      $this->response(404, ['message' => 'Product Report is not found.']);
    }

    $product = Product::getRow(['id' => $report->product_id]);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
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

      $warehouse = Warehouse::getRow(['id' => $warehouseId]);

      $lastReport = ProductReport::select('*')->where('product_id', $product->id)->orderBy('id', 'DESC')->getRow();

      $data = [
        'product_id'    => $product->id,
        'warehouse_id'  => $warehouse->id,
        'condition'     => $condition,
        'note'          => $note,
        'pic_note'      => $noteTS,
        'created_at'    => $date,
        'created_by'    => $createdBy
      ];

      DB::transStart();

      $data = $this->useAttachment($data, null, function ($upload) use ($condition) {
        if ($upload->has('attachment')) {
          if ($upload->getSize('mb') > 2) {
            $this->response(400, ['message' => 'Attachment tidak boleh lebih dari 2MB.']);
          }
        }
      });

      // If last report is edited. Product get edited too.
      if ($lastReport->id == $report->id) {
        $itemJS = getJSON($product->json);

        $itemJS->condition  = $condition;
        $itemJS->note       = $note;
        $itemJS->pic_note   = $noteTS;
        $itemJS->updated_at = $date;
        $json = json_encode($itemJS);

        if (!Product::update((int)$product->id, [
          'json'      => $json,
          'json_data' => $json
        ])) {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      if (!ProductReport::update((int)$id, $data)) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Report has been updated.']);
      }

      $this->response(400, ['message' => 'Failed to edit report.']);
    }

    $this->data['title']    = lang('App.editmaintenancereport');
    $this->data['report']   = $report;
    $this->data['product']  = $product;

    $this->response(200, ['content' => view('Maintenance/Report/edit', $this->data)]);
  }

  protected function report_view($productId = null)
  {
    checkPermission('MaintenanceReport.View');

    Product::sync(['id' => $productId]);

    $product = Product::getRow(['id' => $productId]);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
    }

    $param = [
      'product' => [$productId]
    ];

    if ($startDate = getPostGet('start_date')) {
      $param['start_date'] = $startDate;
    }

    if ($endDate = getPostGet('end_date')) {
      $param['end_date'] = $endDate;
    }

    $this->data['title']    = lang('App.viewmaintenancereport');
    $this->data['product']  = $product;
    $this->data['modeLang'] = $product->code;
    $this->data['param'] = $param;

    $this->response(200, ['content' => view('Maintenance/Report/view', $this->data)]);
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
