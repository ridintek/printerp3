<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Libraries\Spreadsheet;
use App\Models\{
  Attachment,
  DB,
  InternalUse,
  Payment,
  Product,
  ProductCategory,
  ProductMutation,
  ProductMutationItem,
  ProductPurchase,
  ProductTransfer,
  ProductTransferItem,
  Stock,
  StockAdjustment,
  StockOpname,
  StockOpnameItem,
  Unit,
  User,
  Warehouse,
  WarehouseProduct
};

class Inventory extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getCategories()
  {
    checkPermission('ProductCategory.View');

    $dt = new DataTables('categories');
    $dt
      ->select("id, code, name, parent_code, description")
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('ProductCategory.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('inventory/category/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
              <a class="dropdown-item" href="' . base_url('inventory/category/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>';

        if (hasAccess('ProductCategory.Delete')) {
          $menu .= '
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('inventory/category/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->generate();
  }

  public function getInternalUses()
  {
    checkPermission('InternalUse.View');

    $warehouses     = getPost('warehouse');
    $status         = getPost('status');
    $createdBy      = getPost('created_by');
    $startDate      = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('internal_uses');
    $dt
      ->select("internal_uses.id AS id, internal_uses.date, internal_uses.reference,
        pic.fullname, whfrom.name AS warehouse_from_name, whto.name AS warehouse_to_name,
        internal_uses.items, internal_uses.grand_total, internal_uses.counter,
        internal_uses.note, internal_uses.status, internal_uses.created_at,
        internal_uses.attachment")
      ->join('warehouse whfrom', 'whfrom.id = internal_uses.from_warehouse_id', 'left')
      ->join('warehouse whto', 'whto.id = internal_uses.to_warehouse_id', 'left')
      ->join('users pic', 'pic.id = internal_uses.created_by', 'left')
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('InternalUse.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('inventory/internaluse/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
              <a class="dropdown-item" href="' . base_url('inventory/internaluse/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>';

        if (hasAccess('InternalUse.Delete')) {
          $menu .= '
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/internaluse/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
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
      $dt->groupStart()
        ->whereIn('internal_uses.from_warehouse_id', $warehouses)
        ->orWhereIn('internal_uses.to_warehouse_id', $warehouses)
        ->groupEnd();
    }


    $dt->generate();
  }



  public function getProductMutations()
  {
    checkPermission('ProductMutation.View');

    $dt = new DataTables('product_mutation');
    $dt
      ->select("product_mutation.id AS id, product_mutation.id AS cid, product_mutation.date,
      product_mutation.reference,
      warehousefrom.name AS warehouse_from_name, warehouseto.name AS warehouse_to_name,
      product_mutation.items, product_mutation.note, product_mutation.status,
      product_mutation.attachment, product_mutation.created_at, creator.fullname")
      ->join('warehouse warehousefrom', 'warehousefrom.id = product_mutation.from_warehouse_id', 'left')
      ->join('warehouse warehouseto', 'warehouseto.id = product_mutation.to_warehouse_id', 'left')
      ->join('users creator', 'creator.id = product_mutation.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/mutation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/mutation/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/mutation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('cid', function ($data) {
        return "<input class=\"checkbox\" type=\"checkbox\" value=\"{$data['cid']}\">";
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function getProducts()
  {
    checkPermission('Product.View');

    $dt = new DataTables('products');
    $dt
      ->select("products.id AS id, products.id AS cid, products.code, products.name, products.type,
        categories.name AS category_name, products.cost, products.markon_price, products.quantity")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('Product.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('inventory/product/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
              <a class="dropdown-item" href="' . base_url('inventory/product/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>';

        if (hasAccess('Product.Delete')) {
          $menu .= '
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('inventory/product/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('cid', function ($data) {
        return "<input class=\"checkbox\" type=\"checkbox\" value=\"{$data['cid']}\">";
      })
      ->editColumn('cost', function ($data) {
        return '<span class="float-right">' . formatNumber($data['cost']) . '</span>';
      })
      ->editColumn('markon_price', function ($data) {
        return '<span class="float-right">' . formatNumber($data['markon_price']) . '</span>';
      })
      ->editColumn('quantity', function ($data) {
        return '<span class="float-right">' . formatNumber($data['quantity']) . '</span>';
      })
      ->generate();
  }

  public function getProductPurchases()
  {
    checkPermission('ProductPurchase.View');

    $dt = new DataTables('purchases');

    $dt
      ->select("purchases.id AS id, purchases.date,
      purchases.reference, (
        CASE
          WHEN suppliers.company IS NOT NULL THEN CONCAT(suppliers.name, ' (', suppliers.company, ')')
          ELSE suppliers.name
        END
      ) AS supplier_name, purchases.status, purchases.payment_status,
      purchases.grand_total, purchases.paid, (purchases.grand_total - purchases.paid) AS balance,
      purchases.attachment, purchases.created_at, creator.fullname")
      ->join('biller', 'biller.id = purchases.biller_id', 'left')
      ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
      ->join('users creator', 'creator.id = purchases.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/purchase/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/purchase/print/' . $data['id']) . '"
                target="_blank">
                <i class="fad fa-fw fa-print"></i> ' . lang('App.print') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/purchase/print/' . $data['id']) . '?preview=1"
                data-toggle="modal" data-target="#ModalDefault"
                data-modal-class="modal-xl modal-dialog-centered">
                <i class="fad fa-fw fa-print"></i> ' . lang('App.preview') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/purchase/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <div class="dropdown-submenu dropdown-hover">
                <a href="#" class="dropdown-item dropdown-toggle" data-toggle="dropdown">
                  <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.payment') . '</a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="' . base_url('payment/add/purchase/' . $data['id']) . '"
                    data-toggle="modal" data-target="#ModalStatic"
                    data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                    <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.addpayment') . '
                  </a>
                  <a class="dropdown-item" href="' . base_url('payment/view/purchase/' . $data['id']) . '"
                      data-toggle="modal" data-target="#ModalStatic"
                      data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                      <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
                  </a>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/purchase/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
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
      })
      ->generate();
  }

  public function getProductPurchasePlans()
  {
    checkPermission('ProductPurchase.Plan');

    $today = getDayName(date('w') + 1); // Get today name. Ex. senin, selasa, ...

    $dt = new DataTables('suppliers');

    $dt->select("id, (
        CASE
          WHEN suppliers.company IS NOT NULL THEN CONCAT(suppliers.name, ' (', suppliers.company, ')')
          ELSE suppliers.name
        END
      ) AS supplier_name,
        JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_days')) AS visit_days,
        JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_weeks')) AS visit_weeks")
      ->like("LOWER(JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_days')))", $today, 'both')
      ->editColumn('id', function ($data) {
        return '<input name="check[]" class="checkbox" type="checkbox" value="' . $data['id'] . '">';
      });

    $dt->generate();
  }

  public function getProductTransfers()
  {
    checkPermission('ProductTransfer.View');

    $suppliers      = getPost('supplier');
    $warehouses     = getPost('warehouse');
    $status         = getPost('status');
    $paymentStatus  = getPost('payment_status');
    $createdBy      = getPost('created_by');
    $startDate      = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('product_transfer');

    $dt
      ->select("product_transfer.id AS id, product_transfer.date,
      product_transfer.reference,
      warehousefrom.name AS warehouse_from_name, warehouseto.name AS warehouse_to_name,
      product_transfer.items, product_transfer.grand_total, product_transfer.paid,
      product_transfer.status, product_transfer.payment_status,
      product_transfer.attachment, product_transfer.created_at, creator.fullname")
      ->join('warehouse warehousefrom', 'warehousefrom.id = product_transfer.warehouse_id_from', 'left')
      ->join('warehouse warehouseto', 'warehouseto.id = product_transfer.warehouse_id_to', 'left')
      ->join('users creator', 'creator.id = product_transfer.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/transfer/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/transfer/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/transfer/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('paid', function ($data) {
        return '<div class="float-right">' . formatNumber($data['paid']) . '</div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
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

    if ($status) {
      $dt->whereIn('product_transfer.status', $status);
    }

    if ($paymentStatus) {
      $dt->whereIn('product_transfer.payment_status', $paymentStatus);
    }

    if ($suppliers) {
      $dt->whereIn('product_transfer.supplier_id', $suppliers);
    }

    if ($warehouses) {
      $dt->groupStart()
        ->whereIn('product_transfer.warehouse_id_from', $warehouses)
        ->orWhereIn('product_transfer.warehouse_id_to', $warehouses)
        ->groupEnd();
    }

    if ($createdBy) {
      $dt->whereIn('product_transfer.created_by', $createdBy);
    }

    if ($startDate) {
      $dt->where("date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  public function getProductTransferPlans()
  {
    checkPermission('ProductTransfer.Plan');

    $today = getDayName(date('w') + 1); // Get today name. Ex. senin, selasa, ...

    $dt = new DataTables('warehouse');

    $dt->select("id, CONCAT('(', code, ') ', name) AS warehouse_name,
        JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_days')) AS visit_days,
        JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_weeks')) AS visit_weeks")
      ->where('active', 1)
      ->like("LOWER(JSON_UNQUOTE(JSON_EXTRACT(json, '$.visit_days')))", $today, 'both')
      ->editColumn('id', function ($data) {
        return '<input name="check[]" class="checkbox" type="checkbox" value="' . $data['id'] . '">';
      });

    $dt->generate();
  }

  public function getStockAdjustments()
  {
    checkPermission('StockAdjustment.View');

    $dt = new DataTables('adjustments');
    $dt
      ->select("adjustments.id AS id, adjustments.date, adjustments.reference,
        warehouse.name AS warehouse_name, adjustments.mode, adjustments.note,
        adjustments.created_at, creator.fullname, adjustments.attachment")
      ->join('warehouse', 'warehouse.id = adjustments.warehouse_id', 'left')
      ->join('users creator', 'creator.id = adjustments.created_by', 'left')
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('StockAdjustment.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
          <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/view/' . $data['id']) . '"
            data-toggle="modal" data-target="#ModalStatic"
            data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
            <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
          </a>';

        if (hasAccess('StockAdjustment.Delete')) {
          $menu .= '
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('mode', function ($data) {
        return renderStatus($data['mode']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      });

    if (session('login')->warehouse_id) {
      $dt->where('warehouse.id', session('login')->warehouse_id);
    }

    $dt->generate();
  }

  public function getStockOpnames()
  {
    checkPermission('StockOpname.View');

    $warehouses     = getPostGet('warehouse');
    $status         = getPostGet('status');
    $createdBy      = getPostGet('created_by');
    $startDate      = (getPostGet('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPostGet('end_date') ?? date('Y-m-d'));
    $xls            = getPostGet('xls');

    $dt = new DataTables('stock_opnames');
    $dt
      ->select("stock_opnames.id AS id, stock_opnames.date, stock_opnames.reference,
        adjustment_plus.reference AS plus_ref, adjustment_min.reference AS min_ref,
        creator.fullname AS pic_name, warehouse.name AS warehouse_name,
        stock_opnames.total_lost, stock_opnames.total_plus, stock_opnames.total_edited,
        stock_opnames.status, stock_opnames.note,
        stock_opnames.created_at, stock_opnames.attachment")
      ->join('adjustments adjustment_plus', 'adjustment_plus.id = stock_opnames.adjustment_plus_id', 'left')
      ->join('adjustments adjustment_min', 'adjustment_min.id = stock_opnames.adjustment_min_id', 'left')
      ->join('warehouse', 'warehouse.id = stock_opnames.warehouse_id', 'left')
      ->join('users creator', 'creator.id = stock_opnames.created_by', 'left')
      ->editColumn('id', function ($data) {
        $menu = '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">';

        if (hasAccess('StockOpname.Edit')) {
          $menu .= '
            <a class="dropdown-item" href="' . base_url('inventory/stockopname/edit/' . $data['id']) . '"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
            </a>';
        }

        $menu .= '
          <a class="dropdown-item" href="' . base_url('inventory/stockopname/view/' . $data['id']) . '"
            data-toggle="modal" data-target="#ModalStatic"
            data-modal-class="modal-xl modal-dialog-centered modal-dialog-scrollable">
            <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
          </a>';

        if (hasAccess('StockOpname.Delete')) {
          $menu .= '
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('inventory/stockopname/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>';
        }

        $menu .= '
            </div>
          </div>';

        return $menu;
      })
      ->editColumn('total_lost', function ($data) use ($xls) {
        return ($xls ? $data['total_lost'] : formatNumber($data['total_lost']));
      })
      ->editColumn('total_plus', function ($data) use ($xls) {
        return ($xls ? $data['total_plus'] : formatNumber($data['total_plus']));
      })
      ->editColumn('status', function ($data) use ($xls) {
        return ($xls ? $data['status'] : renderStatus($data['status']));
      })
      ->editColumn('attachment', function ($data) use ($xls) {
        return ($xls ? $data['attachment'] : renderAttachment($data['attachment']));
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
      $dt->orWhereIn('stock_opnames.warehouse_id', $warehouses);
    }

    if ($status) {
      $dt->whereIn('stock_opnames.status', $status);
    }

    if ($createdBy) {
      $dt->whereIn('stock_opnames.created_by', $createdBy);
    }

    if ($startDate) {
      $dt->where("stock_opnames.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("stock_opnames.date <= '{$endDate} 23:59:59'");
    }

    if ($xls == 1) {
      $rows = $dt->asObject()->generate(true);

      $sheet = new Spreadsheet();
      $sheet->setTitle('Stock Opnames');

      $sheet->setCellValue('A1', 'No');
      $sheet->setCellValue('B1', 'Date');
      $sheet->setCellValue('C1', 'Reference');
      $sheet->setCellValue('D1', 'Adjustment Plus Ref');
      $sheet->setCellValue('E1', 'Adjustment Minus Ref');
      $sheet->setCellValue('F1', 'PIC');
      $sheet->setCellValue('G1', 'Warehouse');
      $sheet->setCellValue('H1', 'Total Lost');
      $sheet->setCellValue('I1', 'Total Plus');
      $sheet->setCellValue('J1', 'Total Edited');
      $sheet->setCellValue('K1', 'Status');
      $sheet->setCellValue('L1', 'Note');
      $sheet->setCellValue('M1', 'Created At');
      $sheet->setCellValue('N1', 'Attachment');

      $r = 2;

      foreach ($rows as $row) {
        $sheet->setCellValue('A' . $r, $r);
        $sheet->setCellValue('B' . $r, $row['date']);
        $sheet->setCellValue('C' . $r, $row['reference']);
        $sheet->setCellValue('D' . $r, $row['plus_ref']);
        $sheet->setCellValue('E' . $r, $row['min_ref']);
        $sheet->setCellValue('F' . $r, $row['pic_name']);
        $sheet->setCellValue('G' . $r, $row['warehouse_name']);
        $sheet->setCellValue('H' . $r, $row['total_lost']);
        $sheet->setCellValue('I' . $r, $row['total_plus']);
        $sheet->setCellValue('J' . $r, $row['total_edited']);
        $sheet->setCellValue('K' . $r, $row['status']);
        $sheet->setCellValue('L' . $r, htmlRemove($row['note']));
        $sheet->setCellValue('M' . $r, $row['created_at']);

        if ($row['attachment']) {
          $sheet->setCellValue('N' . $r, lang('App.view'));
          $sheet->setUrl('N' . $r, 'https://erp.indoprinting.co.id/attachment/' . $row['attachment']);
        }

        $r++;
      }

      $sheet->export('PrintERP-StockOpname-' . date('Ymd_His'));
    }

    $dt->generate();
  }

  public function getUsageHistories()
  {
    checkPermission('Product.History');

    $product    = getPost('product');
    $status     = getPost('status');
    $warehouses = getPost('warehouse');
    $createdBy  = getPost('created_by');
    $startDate  = (getPost('start_date') ?? date('Y-m-d', strtotime('-7 day')));
    $endDate    = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('stocks');
    $dt->select("stocks.id, stocks.date,
      (CASE
        WHEN stocks.adjustment_id IS NOT NULL THEN adjustments.reference
        WHEN stocks.internal_use_id IS NOT NULL THEN internal_uses.reference
        WHEN stocks.pm_id IS NOT NULL THEN product_mutation.reference
        WHEN stocks.purchase_id IS NOT NULL THEN purchases.reference
        WHEN stocks.sale_id IS NOT NULL THEN sales.reference
        WHEN stocks.transfer_id IS NOT NULL THEN product_transfer.reference
        ELSE 'NOT VALID'
      END) AS reference, CONCAT('(', products.code, ') ', products.name) AS product_name,
      warehouse.name AS warehouse_name, categories.name AS category_name, products.type AS product_type,
      stocks.quantity, stocks.status, creator.fullname AS creator_name")
      ->join('adjustments', 'adjustments.id = stocks.adjustment_id', 'left')
      ->join('internal_uses', 'internal_uses.id = stocks.internal_use_id', 'left')
      ->join('product_mutation', 'product_mutation.id = stocks.pm_id', 'left')
      ->join('purchases', 'purchases.id = stocks.purchase_id', 'left')
      ->join('sales', 'sales.id = stocks.sale_id', 'left')
      ->join('product_transfer', 'product_transfer.id = stocks.transfer_id', 'left')
      ->join('products', 'products.id = stocks.product_id', 'left')
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->join('users creator', 'creator.id = stocks.created_by', 'left')
      ->join('warehouse', 'warehouse.id = stocks.warehouse_id', 'left')
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
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

    if ($product) {
      $dt->whereIn('stocks.product_id', $product);
    }

    if ($warehouses) {
      $dt->whereIn('stocks.warehouse_id', $warehouses);
    }

    if ($status) {
      $dt->whereIn('stocks.status', $status);
    }

    if ($createdBy) {
      $dt->whereIn('stocks.created_by', $createdBy);
    }

    $dt->where("stocks.date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'");

    $dt->generate();
  }

  public function category()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductCategory.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.category'), 'slug' => 'category', 'url' => '#']
      ],
      'content' => 'Inventory/Category/index',
      'title' => lang('App.productcategory')
    ];

    return $this->buildPage($this->data);
  }

  protected function category_add()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $code   = getPost('code');
      $name   = getPost('name');
      $parent = getPost('parent');
      $desc   = getPost('desc');

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      $parentCategory = ProductCategory::getRow(['id' => $parent]);

      if ($parentCategory) {
        $parentCode = $parentCategory->code;
      } else {
        $parentCode = null;
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'parent_code' => $parentCode,
        'description' => $desc
      ];

      DB::transStart();

      $insertId = ProductCategory::add($data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/add', $this->data)]);
  }

  protected function category_delete($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product category is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = ProductCategory::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad request.']);
  }

  protected function category_edit($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product Category is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $code   = getPost('code');
      $name   = getPost('name');
      $parent = getPost('parent');
      $desc   = getPost('desc');

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      $parentCategory = ProductCategory::getRow(['id' => $parent]);

      if ($parentCategory) {
        $parentCode = $parentCategory->code;
      } else {
        $parentCode = null;
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'parent_code' => $parentCode,
        'description' => $desc
      ];

      DB::transStart();

      $insertId = ProductCategory::update((int)$id, $data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['category'] = $category;
    $this->data['title']    = lang('App.editproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/edit', $this->data)]);
  }

  protected function category_view($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product Category is not found.']);
    }

    $parent = ProductCategory::getRow(['code' => $category->parent_code]);

    $this->data['category'] = $category;
    $this->data['parent']   = $parent;
    $this->data['title']    = lang('App.viewproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/view', $this->data)]);
  }

  public function cloudsync()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Product.CloudSync');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.cloudsync'), 'slug' => 'cloudsync', 'url' => '#']
      ],
      'content' => 'Inventory/CloudSync/index',
      'title' => lang('App.cloudsync')
    ];

    return $this->buildPage($this->data);
  }

  /**
   * Internal Use
   * 
   * Decrease quantity warehouseFrom without increase quantity warehouseTo.
   * 
   * category and status:
   *  - consumable: completed
   *  - sparepart: need_approval, approved
   *    - packing:
   *      - cancelled:
   *        - returned
   *      - installed:
   *        - completed
   * */
  public function internaluse()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('InternalUse.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.internaluse'), 'slug' => 'internaluse', 'url' => '#']
      ],
      'content' => 'Inventory/InternalUse/index',
      'title' => lang('App.internaluse')
    ];

    return $this->buildPage($this->data);
  }

  protected function internaluse_add()
  {
    checkPermission('InternalUse.Add');

    if (requestMethod() == 'POST') {
      $category         = getPost('category');
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'from_warehouse_id' => $warehouseIdFrom,
        'to_warehouse_id'   => $warehouseIdTo,
        'category'          => $category,
        'note'              => stripTags(getPost('note')),
        'supplier_id'       => getPost('supplier'),
        'ts_id'             => getPost('techsupport'),
      ];

      $itemId       = getPost('item[id]');
      $itemCode     = getPost('item[code]');
      $itemCounter  = getPost('item[counter]');
      $itemMachine  = getPost('item[machine]');
      $itemQty      = getPost('item[quantity]');
      $itemUnique   = getPost('item[unique]'); // Auto-generated on add.
      $itemUcr      = getPost('item[ucr]'); // Unique Code Replacement.

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        // Prevent input lower counter than current counter.
        if (!empty($itemCounter[$a])) {
          $whp = WarehouseProduct::getRow(['product_code' => 'KLIKPOD', 'warehouse_id' => $warehouseIdTo]);

          if ($whp) {
            $lastKLIKQty = intval($whp->quantity);

            if ($lastKLIKQty > intval($itemCounter[$a])) {
              $this->response(400, ['message' => "Klik {$itemCounter[$a]} tidak sesuai klik terakhir {$lastKLIKQty}."]);
            }
          }
        }

        $items[] = [
          'id'          => $itemId[$a],
          'counter'     => $itemCounter[$a],
          'machine_id'  => $itemMachine[$a],
          'quantity'    => $itemQty[$a],
          'unique_code' => $itemUnique[$a],
          'ucr'         => $itemUcr[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = InternalUse::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Internal Use has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/add', $this->data)]);
  }

  protected function internaluse_delete($id = null)
  {
    $iUse = InternalUse::getRow(['id' => $id]);
    $iUseItems = Stock::get(['internal_use_id' => $id]);

    if (!$iUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {

      DB::transStart();

      Attachment::delete(['hashname' => $iUse->attachment]);
      Stock::delete(['internal_use_id' => $id]);

      foreach ($iUseItems as $iUseItem) {
        Product::sync(['id' => $iUseItem->product_id]);
      }

      $res = InternalUse::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Internal Use has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad Request.']);
  }

  protected function internaluse_edit($id = null)
  {
    checkPermission('InternalUse.Edit');

    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    if (requestMethod() == 'POST') {
      $category         = getPost('category');
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'from_warehouse_id' => $warehouseIdFrom,
        'to_warehouse_id'   => $warehouseIdTo,
        'category'          => $category,
        'note'              => stripTags(getPost('note')),
        'status'            => getPost('status'), // Status is changeable from edit.
        'supplier_id'       => getPost('supplier'),
        'ts_id'             => getPost('techsupport'),
      ];

      $itemId       = getPost('item[id]');
      $itemCode     = getPost('item[code]');
      $itemCounter  = getPost('item[counter]');
      $itemMachine  = getPost('item[machine]');
      $itemQty      = getPost('item[quantity]');
      $itemUnique   = getPost('item[unique]');
      $itemUcr      = getPost('item[ucr]'); // Unique Code Replacement.

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'          => $itemId[$a],
          'counter'     => $itemCounter[$a],
          'machine_id'  => $itemMachine[$a],
          'quantity'    => $itemQty[$a],
          'unique_code' => $itemUnique[$a],
          'ucr'         => $itemUcr[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = InternalUse::update((int)$id, $data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Internal Use has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];

    foreach (Stock::get(['internal_use_id' => $internalUse->id]) as $stock) {
      $whp = WarehouseProduct::getRow([
        'product_id' => $stock->product_id, 'warehouse_id' => $internalUse->from_warehouse_id
      ]);

      $items[] = [
        'id'          => intval($stock->product_id),
        'code'        => $stock->product_code,
        'name'        => $stock->product_name,
        'unit'        => $stock->unit,
        'quantity'    => floatval($stock->quantity),
        'counter'     => $stock->spec,
        'unique'      => $stock->unique_code,
        'ucr'         => $stock->ucr,
        'current_qty' => floatval($whp->quantity),
        'machine'     => intval($stock->machine_id),
      ];
    }

    $this->data['internalUse']  = $internalUse;
    $this->data['items']        = $items;
    $this->data['title']        = lang('App.editinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/edit', $this->data)]);
  }

  protected function internaluse_status($id = null)
  {
    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $iuseItems = Stock::get(['internal_use_id' => $internalUse->id]);

    if (!$iuseItems) {
      $this->response(404, ['message' => 'Internal Use items are not found.']);
    }

    $items        = [];
    $status       = getPost('status');
    $itemId       = getPost('item[id]');
    $itemCode     = getPost('item[code]');
    $itemCounter  = getPost('item[counter]');

    foreach ($iuseItems as $iuseItem) {
      $counter = $iuseItem->spec;

      if ($status == 'installed') {
        for ($a = 0; $a < count($itemId); $a++) {
          if (empty($itemCounter[$a])) {
            $this->response(400, ['message' => "Counter {$itemCode[$a]} harus diisi."]);
          }

          if ($iuseItem->product_id == $itemId[$a]) {
            $counter = $itemCounter[$a];
            break;
          }
        }
      }

      $items[] = [
        'id'          => $iuseItem->product_id,
        'counter'     => $counter,
        'machine_id'  => $iuseItem->machine_id,
        'quantity'    => $iuseItem->quantity,
        'unique_code' => $iuseItem->unique_code,
        'ucr'         => $iuseItem->ucr
      ];
    }

    DB::transStart();

    $res = InternalUse::update((int)$id, ['category' => $internalUse->category, 'status' => $status], $items);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Internal Use status has been updated.']);
    }

    $this->response(400, ['message' => 'Failed to update status.']);
  }

  protected function internaluse_view($id = null)
  {
    checkPermission('InternalUse.View');

    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $this->data['internalUse']  = $internalUse;
    $this->data['title']        = lang('App.viewinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/view', $this->data)]);
  }

  /**
   * Product Mutation.
   * Status: packing -> received
   */
  public function mutation()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductMutation.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.productmutation'), 'slug' => 'mutation', 'url' => '#']
      ],
      'content' => 'Inventory/Mutation/index',
      'title' => lang('App.productmutation')
    ];

    return $this->buildPage($this->data);
  }

  protected function mutation_add()
  {
    checkPermission('ProductMutation.Add');

    if (requestMethod() == 'POST') {
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'from_warehouse_id' => $warehouseIdFrom,
        'to_warehouse_id'   => $warehouseIdTo,
        'note'              => stripTags(getPost('note')),
      ];

      $itemId   = getPost('item[id]');
      $itemCode = getPost('item[code]');
      $itemQty  = getPost('item[quantity]');

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'        => $itemId[$a],
          'quantity'  => $itemQty[$a],
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = ProductMutation::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Product Mutation has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproductmutation');

    $this->response(200, ['content' => view('Inventory/Mutation/add', $this->data)]);
  }

  protected function mutation_delete($id = null)
  {
    $mutation       = ProductMutation::getRow(['id' => $id]);
    $mutationItems  = Stock::get(['pm_id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Product Mutation is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {

      DB::transStart();

      Attachment::delete(['hashname' => $mutation->attachment]);
      Stock::delete(['pm_id' => $id]);

      foreach ($mutationItems as $mutationItem) {
        Product::sync(['id' => $mutationItem->product_id]);
      }

      $res = ProductMutation::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Mutation has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad request.']);
  }

  protected function mutation_status($id = null)
  {
    $mutation = ProductMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Product Mutation is not found.']);
    }

    $mutationItems = ProductMutationItem::get(['pm_id' => $mutation->id]);

    if (!$mutationItems) {
      $this->response(404, ['message' => 'Product Mutation items are not found.']);
    }

    $items  = [];
    $status = getPost('status');

    foreach ($mutationItems as $mutationItem) {
      $items[] = [
        'id'        => $mutationItem->product_id,
        'quantity'  => $mutationItem->quantity,
      ];
    }

    DB::transStart();

    $res = ProductMutation::update((int)$id, ['status' => $status], $items);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product Mutation status has been updated.']);
    }

    $this->response(400, ['message' => 'Failed to update status.']);
  }

  protected function mutation_view($id = null)
  {
    checkPermission('ProductMutation.View');

    $mutation = ProductMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $this->data['mutation'] = $mutation;
    $this->data['title']    = lang('App.viewproductmutation');

    $this->response(200, ['content' => view('Inventory/Mutation/view', $this->data)]);
  }

  public function product()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Product.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.product'), 'slug' => 'product', 'url' => '#']
      ],
      'content' => 'Inventory/Product/index',
      'title' => lang('App.product')
    ];

    return $this->buildPage($this->data);
  }

  protected function product_delete($id = null)
  {
    $product = Product::getRow(['id' => $id]);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {

      DB::transStart();

      if (!Product::delete(['id' => $id])) {
        $this->response(400, ['message' => getLastError()]);
      }

      WarehouseProduct::delete(['product_id' => $id]);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad request.']);
  }

  protected function product_history($id = null)
  {
    $startDate    = getGet('start_date');
    $endDate      = getGet('end_date');
    $warehouseId  = getGet('warehouse');
    $export_xls   = (getGet('xls') == 1 ? TRUE : FALSE);

    $this->data['product_id']   = $id;
    $this->data['product']      = Product::getRow(['id' => $id]);
    $this->data['start_date']   = $startDate;
    $this->data['end_date']     = $endDate;
    $this->data['warehouse_id'] = $warehouseId;
    $this->data['warehouse']    = Warehouse::getRow(['id' => $warehouseId]);

    $clause = [];

    if ($id)          $clause['product_id']   = $id;
    if ($warehouseId) $clause['warehouse_id'] = $warehouseId;

    $stock = Stock::select('*')->where('product_id', $id);

    if ($warehouseId) {
      $stock->where('warehouse_id', $warehouseId);
    }

    if ($startDate) {
      $stock->where("date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $stock->where("date <= '{$endDate} 23:59:59'");
    }

    $rows = $stock->orderBy('date', 'ASC')->get();

    $beginningQty  = ($startDate ? Stock::beginningQty($clause, $startDate) : 0);

    $this->data['beginningQty'] = $beginningQty;
    $this->data['rows']         = $rows;

    if ($export_xls) {
      // $total_balance = filterQuantity($beginningQty);
      // $total_decrease = 0;
      // $total_increase = 0;
      // $old_balance = 0;
      // $old_decrease = 0;
      // $old_increase = 0;
      // $old_date = '';
      // $iold_date = 0;
      // $x = 2;

      // $excel = $this->ridintek->spreadsheet();
      // $excel->setTitle('Product History');
      // $excel->setBold('A1:I1');
      // $excel->setHorizontalAlign('A1:I1', 'center');
      // $excel->setFillColor('A1:I1', 'FFFF00');
      // $excel->setCellValue('A1', 'Stock ID');
      // $excel->setCellValue('B1', 'Date');
      // $excel->setCellValue('C1', 'Reference');
      // $excel->setCellValue('D1', 'Warehouse');
      // $excel->setCellValue('E1', 'Category');
      // $excel->setCellValue('F1', 'Created By');
      // $excel->setCellValue('G1', 'Increase');
      // $excel->setCellValue('H1', 'Decrease');
      // $excel->setCellValue('I1', 'Balance');

      // if (!empty($rows)) {
      //   if ($beginning_qty > 0 || $beginning_qty < 0) {
      //     $excel->setBold("A2:I2");
      //     $excel->setCellValue('A2', '-');
      //     $excel->setCellValue('B2', $start_date . ' 00:00:00');
      //     $excel->mergeCells('C2:H2');
      //     $excel->setHorizontalAlign('C2', 'center');
      //     $excel->setCellValue('C2', 'BEGINNING');
      //     $excel->setCellValue('I2', filterQuantity($beginning_qty));

      //     $x++;
      //   }

      //   foreach ($rows as $row) {
      //     if ($row->status != 'received' && $row->status != 'sent') continue;

      //     $idate = strtotime($row->date);

      //     if ($iold_date && (date('m', $idate) != date('m', $iold_date))) { // Monthly Summary
      //       $excel->setBold("A{$x}:I{$x}");
      //       $excel->setCellValue("A{$x}", '-');
      //       $excel->setCellValue("B{$x}", $old_date . ' 23:59:59');
      //       $excel->mergeCells("C{$x}:F{$x}");
      //       $excel->setHorizontalAlign("C{$x}", 'center');
      //       $excel->setCellValue("C{$x}", 'SUMMARY ' . strtoupper(getMonthName(date('n', $iold_date)))); // Ex. SUMMARY JANUARY
      //       $excel->setCellValue("G{$x}", $old_increase);
      //       $excel->setCellValue("H{$x}", $old_decrease);
      //       $excel->setCellValue("I{$x}", $old_balance);
      //       $old_balance = 0;
      //       $old_decrease = 0;
      //       $old_increase = 0;
      //       $x++;
      //     }

      //     // BEGIN DATA
      //     $excel->setCellValue("A{$x}", $row->id);
      //     $excel->setCellValue("B{$x}", $row->date);

      //     $reference = '';
      //     if ($row->adjustment_id != NULL) {
      //       $reference = $this->site->getStockAdjustmentByID($row->adjustment_id)->reference;
      //     } else if ($row->internal_use_id != NULL) {
      //       $reference = $this->site->getStockInternalUseByID($row->internal_use_id)->reference;
      //     } else if ($row->purchase_id != NULL) {
      //       $reference = $this->site->getStockPurchaseByID($row->purchase_id)->reference;
      //     } else if ($row->sale_id != NULL) {
      //       $reference = $this->site->getSaleByID($row->sale_id)->reference;
      //     } else if ($row->transfer_id != NULL) {
      //       $transfer2 = ProductTransfer::getRow(['id' => $row->transfer_id]);

      //       if ($transfer2) {
      //         $reference = str_replace('TRF', 'TRF2', $transfer2->reference);
      //       } else {
      //         $reference = $this->site->getStockTransferByID($row->transfer_id)->reference;
      //       }
      //     }

      //     $excel->setCellValue("C{$x}", $reference);
      //     $excel->setCellValue("D{$x}", $row->warehouse_name);
      //     $excel->setCellValue("E{$x}", $row->category_code);

      //     $created_by = '';
      //     if ($row->created_by != NULL) {
      //       $user = $this->site->getUserByID($row->created_by);
      //       $created_by = ($user ? $user->fullname : '');
      //     }

      //     $excel->setCellValue("F{$x}", $created_by);

      //     $dec = 0;
      //     $inc = 0;

      //     if ($row->status == 'received') {
      //       $inc = $row->quantity;
      //       $total_increase = filterQuantity($total_increase + $inc);
      //     } else if ($row->status == 'sent') {
      //       $dec = $row->quantity;
      //       $total_decrease = filterQuantity($total_decrease + $dec);
      //     }

      //     $excel->setCellValue("G{$x}", ($inc ? $inc : ''));
      //     $excel->setCellValue("H{$x}", ($dec ? $dec : ''));

      //     if ($row->status == 'received') {
      //       $total_balance = filterQuantity($total_balance + $row->quantity);
      //     } else if ($row->status == 'sent') {
      //       $total_balance = filterQuantity($total_balance - $row->quantity);
      //     }

      //     $iold_date = $idate;
      //     $old_date = date('Y-m-d', $iold_date);
      //     $old_balance = $total_balance;
      //     $old_decrease += $dec;
      //     $old_increase += $inc;

      //     $excel->setCellValue("I{$x}", $total_balance);
      //     // END DATA

      //     $x++;
      //   }

      //   // LAST MONTHLY SUMMARY
      //   $excel->setBold("A{$x}:I{$x}");
      //   $excel->setCellValue("A{$x}", '-');
      //   $excel->setCellValue("B{$x}", ($end_date ? $end_date . date(' H:i:s') : ''));
      //   $excel->mergeCells("C{$x}:F{$x}");
      //   $excel->setHorizontalAlign("C{$x}", 'center');
      //   $excel->setCellValue("C{$x}", 'SUMMARY ' . strtoupper(getMonthName(date('m', $iold_date)))); // Ex. SUMMARY JANUARY
      //   $excel->setCellValue("G{$x}", $old_increase);
      //   $excel->setCellValue("H{$x}", $old_decrease);
      //   $excel->setCellValue("I{$x}", $old_balance);

      //   $x++;
      // } else { // If no data available.
      //   $excel->mergeCells('A2:I2');
      //   $excel->setCellValue('A2', lang('no_data_available'));
      //   $excel->setHorizontalAlign('A2', 'center');
      // }

      // $excel->setBold("A{$x}:I{$x}");
      // $excel->setCellValue("A{$x}", '-');
      // $excel->setCellValue("B{$x}", ($end_date ? $end_date . date(' H:i:s') : ''));
      // $excel->mergeCells("C{$x}:F{$x}");
      // $excel->setHorizontalAlign("C{$x}", 'center');
      // $excel->setCellValue("C{$x}", 'SUMMARY TOTAL');
      // $excel->setCellValue("G{$x}", $total_increase);
      // $excel->setCellValue("H{$x}", $total_decrease);
      // $excel->setCellValue("I{$x}", $total_balance);

      // // Set Auto Width
      // $excel->setColumnAutoWidth('A');
      // $excel->setColumnAutoWidth('B');
      // $excel->setColumnAutoWidth('C');
      // $excel->setColumnAutoWidth('D');
      // $excel->setColumnAutoWidth('E');
      // $excel->setColumnAutoWidth('F');
      // $excel->setColumnAutoWidth('G');
      // $excel->setColumnAutoWidth('H');
      // $excel->setColumnAutoWidth('I');

      // $excel->export('PrintERP - Product_History-' . date('Ymd_His'));
    }

    $this->data['title']  = lang('App.usagehistory');

    $this->response(200, ['content' => view('Inventory/Product/history', $this->data)]);
  }

  protected function product_sync()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $ids = getPost('id');

      if (empty($ids)) {
        $ids = [];

        foreach (Product::get(['active' => 1]) as $product) {
          $ids[] = $product->id;
        }
      }

      $synced = 0;

      foreach ($ids as $productId) {
        if (Product::sync(['id' => $productId])) {
          $synced++;
        } else {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      $this->response(200, ['message' => "{$synced} products have been synced."]);
    }

    $this->response(400, ['message' => 'Bad request.']);
  }


  /**
   * Product Purchase
   * status:
   *  - need_approval
   *  - approved
   *  - ordered
   *  - received / received_partial
   */
  public function purchase()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductPurchase.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.productpurchase'), 'slug' => 'purchase', 'url' => '#']
      ],
      'content' => 'Inventory/Purchase/index',
      'title' => lang('App.productpurchase')
    ];

    return $this->buildPage($this->data);
  }

  protected function purchase_add()
  {
    checkPermission('ProductPurchase.Add');

    if (requestMethod() == 'POST') {
      $billerId     = getPost('biller');
      $categoryId   = getPost('category');
      $supplierId   = getPost('supplier');
      $warehouseId  = getPost('warehouse');

      if (empty($billerId)) {
        $this->response(400, ['message' => 'Biller is not set.']);
      }

      if (empty($warehouseId)) {
        $this->response(400, ['message' => 'Warehouse is not set.']);
      }

      $data = [
        'date'          => dateTimePHP(getPost('date')),
        'biller_id'     => $billerId,
        'category_id'   => $categoryId,
        'supplier_id'   => $supplierId,
        'warehouse_id'  => $warehouseId,
        'note'          => stripTags(getPost('note')),
      ];

      $itemId   = getPost('item[id]');
      $itemCode = getPost('item[code]');
      $itemCost = getPost('item[cost]');
      $itemSpec = getPost('item[spec]');
      $itemQty  = getPost('item[quantity]');

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'            => intval($itemId[$a]),
          'purchased_qty' => floatval($itemQty[$a]),
          'cost'          => filterNumber($itemCost[$a]),
          'spec'          => stripTags($itemSpec[$a])
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = ProductPurchase::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Product Purchase has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproductpurchase');

    $this->response(200, ['content' => view('Inventory/Purchase/add', $this->data)]);
  }

  protected function purchase_delete($id = null)
  {
    checkPermission('ProductPurchase.Delete');

    $pt = ProductPurchase::getRow(['id' => $id]);

    if (!$pt) {
      $this->response(404, ['message' => 'Product Purchase is not found.']);
    }

    DB::transStart();

    if (!ProductPurchase::delete(['id' => $id])) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product Purchase has been deleted.']);
    }

    $this->response(400, ['message' => 'Failed to delete Product Purchase.']);
  }

  protected function purchase_edit($id = null)
  {
    checkPermission('ProductPurchase.Add');

    $purchase = ProductPurchase::getRow(['id' => $id]);

    if (!$purchase) {
      $this->response(404, ['message' => 'Product Purchase is not found.']);
    }

    $stocks  = Stock::get(['purchase_id' => $id]);

    if (!$stocks) {
      $this->response(404, ['message' => 'Product Purchase item is not found.']);
    }

    if (requestMethod() == 'POST') {
      $billerId     = getPost('biller');
      $categoryId   = getPost('category');
      $supplierId   = getPost('supplier');
      $warehouseId  = getPost('warehouse');

      if (empty($billerId)) {
        $this->response(400, ['message' => 'Biller is not set.']);
      }

      if (empty($warehouseId)) {
        $this->response(400, ['message' => 'Warehouse is not set.']);
      }

      $data = [
        'date'            => dateTimePHP(getPost('date')),
        'biller_id'       => $billerId,
        'category_id'     => $categoryId,
        'supplier_id'     => $supplierId,
        'warehouse_id'    => $warehouseId,
        'grand_total'     => 0,
        'received_value'  => 0,
        'note'            => stripTags(getPost('note')),
      ];

      $itemId     = getPost('item[id]');
      $itemCode   = getPost('item[code]');
      $itemCost   = getPost('item[cost]');
      $itemSpec   = getPost('item[spec]');
      $itemQty    = getPost('item[quantity]');
      $itemRcv    = getPost('item[received]');

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'            => intval($itemId[$a]),
          'quantity'      => floatval($itemRcv[$a]),
          'purchased_qty' => floatval($itemQty[$a]),
          'cost'          => filterNumber($itemCost[$a]),
          'spec'          => stripTags($itemSpec[$a])
        ];

        $data['grand_total'] += filterNumber($itemCost[$a]) * floatval($itemQty[$a]);
        $data['received_value'] += filterNumber($itemCost[$a]) * floatval($itemRcv[$a]);
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      if (!ProductPurchase::update((int)$id, $data, $items)) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Product Purchase has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];

    foreach ($stocks as $stock) {
      $product  = Product::getRow(['id' => $stock->product_id]);
      $whp  = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $purchase->warehouse_id]);
      $unit = Unit::getRow(['id' => $product->unit]);

      $items[] = [
        'id'            => $stock->product_id,
        'code'          => $stock->product_code,
        'name'          => $stock->product_name,
        'cost'          => $stock->cost,
        'quantity'      => $stock->purchased_qty,
        'current_qty'   => floatval($whp->quantity),
        'received_qty'  => floatval($stock->quantity),
        'unit'          => $unit->code,
        'spec'          => $stock->spec
      ];
    }

    $this->data['items']    = $items;
    $this->data['purchase'] = $purchase;
    $this->data['title']    = lang('App.editproductpurchase');

    $this->response(200, ['content' => view('Inventory/Purchase/edit', $this->data)]);
  }

  protected function purchase_plan()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $warehouses = getPost('check');

      if ($warehouses) {
        $success = 0;

        DB::transStart();

        foreach ($warehouses as $whId) {
          // Add product purchase by each warehouse id.
          if (ProductPurchase::addBySupplierId((int)$whId)) {
            $success++;
          } else {
            $this->response(400, ['message' => getLastError()]);
          }
        }

        DB::transComplete();

        if (DB::transStatus()) {
          $this->response(201, ['message' => "{$success} Product Purchase berhasil ditambahkan."]);
        }

        $this->response(400, ['message' => getLastError()]);
      }

      $this->response(400, ['message' => 'Pilih warehouse untuk menambah Purchase Plan.']);
    }

    $this->data['title'] = lang('App.purchaseplan');

    $this->response(200, ['content' => view('Inventory/Purchase/plan', $this->data)]);
  }

  protected function purchase_print($id = null)
  {
    checkPermission('Sale.View');

    $preview = getGet('preview');

    $purchase = ProductPurchase::getRow(['id' => $id]);

    if (!$purchase) {
      $this->response(404, ['message' => 'Purchase is not found.']);
    }

    $items = Stock::get(['purchase_id' => $purchase->id]);

    $this->data['purchase'] = $purchase;
    $this->data['items']    = $items;
    $this->data['title']    = "Invoice {$purchase->reference}";

    if ($preview) {
      $this->response(200, ['content' => view('Inventory/Purchase/preview', $this->data)]);
    }

    return view('Inventory/Purchase/print', $this->data);
  }

  protected function purchase_status($id = null)
  {
    $purchase = ProductPurchase::getRow(['id' => $id]);

    if (!$purchase) {
      $this->response(404, ['message' => 'Product Purchase is not found.']);
    }

    $purchaseItems = Stock::get(['purchase_id' => $id]);

    if (!$purchaseItems) {
      $this->response(404, ['message' => 'Product Purchase items are not found.']);
    }

    $date           = date('Y-m-d H:i:s'); // Tgl. sekarang.
    $status         = getPost('status');
    $itemId         = getPost('item[id]');
    $itemRest       = getPost('item[rest]');
    $hasPartial     = false;
    $receivedValue  = 0;

    $data = [
      'status'  => $status
    ];

    DB::transStart();

    if ($status == 'approve_payment') {
      $payments = Payment::get(['purchase_id' => $id]);

      foreach ($payments as $payment) {
        if ($payment->status == 'need_approval') {
          if (!Payment::update((int)$payment->id, ['status' => 'approved', 'type' => 'approved'])) {
            $this->response(400, ['message' => getLastError()]);
          }
        }
      }

      if (!ProductPurchase::update((int)$id, ['payment_status' => 'approved'])) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Purchase payment has been approved.']);
      }

      $this->response(400, ['message' => 'Failed to approve payment.']);
    }

    if ($status == 'commit_payment') {
      $payments = Payment::get(['purchase_id' => $id]);

      foreach ($payments as $payment) {
        if ($payment->status == 'approved') {
          if (!Payment::update((int)$payment->id, ['status' => 'paid', 'type' => 'sent'])) {
            $this->response(400, ['message' => getLastError()]);
          }
        }
      }

      if (!ProductPurchase::sync(['id' => $id])) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Purchase payment has been approved.']);
      }

      $this->response(400, ['message' => 'Failed to approve payment.']);
    }

    foreach ($purchaseItems as $purchaseItem) {
      for ($a = 0; $a < count($itemId); $a++) {
        if ($purchaseItem->product_id == $itemId[$a]) {
          if ($status == 'received') {
            $receivedQty = floatval($purchaseItem->quantity + $itemRest[$a]);

            if (!Stock::update((int)$purchaseItem->id, ['date' => $date, 'quantity' => $receivedQty, 'status' => 'received', 'created_by' => $purchase->created_by])) {
              $this->response(400, ['message' => getLastError()]);
            }

            if ($receivedQty < $purchaseItem->purchased_qty) {
              $hasPartial = true;
            }

            $receivedValue += floatval($receivedQty * $purchaseItem->cost);

            Product::sync(['id' => $purchaseItem->product_id]);
          } else {
            if (!Stock::update((int)$purchaseItem->id, ['quantity' => 0, 'status' => $status])) {
              $this->response(400, ['message' => getLastError()]);
            }

            Product::sync(['id' => $purchaseItem->product_id]);
          }

          break;
        }
      }
    }

    $data['received_value'] = $receivedValue;

    if ($status == 'received') {
      $data['received_date']  = $date;
    } else {
      $data['received_date'] = null;
    }

    if ($hasPartial) {
      $data['status'] = 'received_partial';
    }

    $data = $this->useAttachment($data);

    if (!ProductPurchase::update((int)$id, $data)) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Status has been changed.']);
    }

    $this->response(400, ['message' => 'Failed']);
  }

  protected function purchase_view($id = null)
  {
    checkPermission('ProductPurchase.View');

    ProductPurchase::sync(['id' => $id]);

    $purchase = ProductPurchase::getRow(['id' => $id]);

    if (!$purchase) {
      $this->response(404, ['message' => 'Product Purchase is not found.']);
    }

    $this->data['purchase'] = $purchase;
    $this->data['title']    = lang('App.viewproductpurchase');

    $this->response(200, ['content' => view('Inventory/Purchase/view', $this->data)]);
  }


  public function stockadjustment()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('StockAdjustment.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.stockadjustment'), 'slug' => 'stockadjustment', 'url' => '#']
      ],
      'content' => 'Inventory/StockAdjustment/index',
      'title' => lang('App.stockadjustment')
    ];

    return $this->buildPage($this->data);
  }

  protected function stockadjustment_add()
  {
    checkPermission('StockAdjustment.Add');

    if (requestMethod() == 'POST') {
      $data = [
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'mode'          => getPost('mode'),
        'note'          => stripTags(getPost('note'))
      ];

      $itemIds    = getPost('item[id]');
      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');

      if (!is_array($itemIds) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
          'quantity'  => $itemQty[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = StockAdjustment::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Stock Adjustment has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/add', $this->data)]);
  }

  protected function stockadjustment_delete($id = NULL)
  {
    checkPermission('StockAdjustment.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $adjustment = StockAdjustment::getRow(['id' => $id]);
      $stocks     = Stock::get(['adjustment_id' => $id]);

      if (!$adjustment) {
        $this->response(404, ['message' => 'Stock Adjustment is not found.']);
      }

      DB::transStart();

      $res = StockAdjustment::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Stock::delete(['adjustment_id' => $id]);
      Attachment::delete(['hashname' => $adjustment->attachment]);

      foreach ($stocks as $stock) {
        Product::sync(['id' => $stock->product_id]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Stock Adjustment has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete Stock Adjustment.']);
  }

  protected function stockadjustment_edit($id = null)
  {
    checkPermission('StockAdjustment.Edit');

    $adjustment = StockAdjustment::getRow(['id' => $id]);

    if (!$adjustment) {
      $this->response(404, ['message' => 'Stock Adjustment is not found.']);
    }

    $stocks = Stock::get(['adjustment_id' => $adjustment->id]);

    if (!$stocks) {
      $this->response(404, ['message' => 'Stock Adjustment item is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'mode'          => getPost('mode'),
        'note'          => stripTags(getPost('note'))
      ];

      $itemIds    = getPost('item[id]');
      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a]) && $itemQty[$a] != 0) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
          'quantity'  => $itemQty[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = StockAdjustment::update((int)$id, $data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Stock Adjustment has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];

    foreach ($stocks as $stock) {
      $whProduct = WarehouseProduct::getRow(['product_id' => $stock->product_id, 'warehouse_id' => $stock->warehouse_id]);

      $items[] = [
        'id'          => $stock->product_id,
        'code'        => $stock->product_code,
        'name'        => $stock->product_name,
        'quantity'    => $stock->adjustment_qty,
        'current_qty' => $whProduct->quantity
      ];
    }

    $this->data['adjustment'] = $adjustment;
    $this->data['items']      = $items;
    $this->data['title'] = lang('App.editstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/edit', $this->data)]);
  }

  protected function stockadjustment_view($id = null)
  {
    checkPermission('StockAdjustment.View');

    $adjustment = StockAdjustment::getRow(['id' => $id]);

    if (!$adjustment) {
      $this->response(404, ['message' => 'Stock Adjustment is not found.']);
    }

    $this->data['adjustment'] = $adjustment;
    $this->data['title']      = lang('App.viewstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/view', $this->data)]);
  }

  /**
   * Stock Opname.
   * Stock Qty == Input Qty => 'Excellent'.
   * Stock Qty < Input Qty  => 'Good' + Auto Adjustment Plus.
   * Stock Qty > Input Qty  => 'Checked'.
   * 'Checked' => First SO Qty == Update SO Qty.
   */
  public function stockopname()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('StockOpname.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.stockopname'), 'slug' => 'stockopname', 'url' => '#']
      ],
      'content' => 'Inventory/StockOpname/index',
      'title' => lang('App.stockopname')
    ];

    return $this->buildPage($this->data);
  }

  protected function stockopname_add()
  {
    checkPermission('StockOpname.Add');

    if (requestMethod() == 'POST') {
      $itemIds    = getPost('item[id]');
      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');
      $itemReject = getPost('item[reject]');

      $data = [
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'cycle'         => getPost('cycle'),
        'note'          => stripTags(getPost('note')),
        'created_by'    => getPost('pic')
      ];

      if (empty($data['cycle'])) { // Default to cycle 1.
        $data['cycle'] = 1;
      }

      if (!is_array($itemIds) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a]) && $itemQty[$a] != 0) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
          'quantity'  => $itemQty[$a],
          'reject'    => $itemReject[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = StockOpname::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Stock Opname has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addstockopname');

    $this->response(200, ['content' => view('Inventory/StockOpname/add', $this->data)]);
  }

  protected function stockopname_delete($id = null)
  {
    $opname = StockOpname::getRow(['id' => $id]);
    $soItems = StockOpnameItem::get(['opname_id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock Opname is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      Attachment::delete(['hashname' => $opname->attachment]);

      if ($opname->adjustment_plus_id) {
        StockAdjustment::delete(['id' => $opname->adjustment_plus_id]);
        Stock::delete(['adjustment_id' => $opname->adjustment_plus_id]);
      }

      if ($opname->adjustment_min_id) {
        StockAdjustment::delete(['id' => $opname->adjustment_min_id]);
        Stock::delete(['adjustment_id' => $opname->adjustment_min_id]);
      }

      foreach ($soItems as $soItem) {
        Product::sync(['id' => $soItem->product_id]);
      }

      $res = StockOpname::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Stock Opname has been deleted.']);
      }
    }

    $this->response(400, ['message' => getLastError()]);
  }

  protected function stockopname_edit($id = null)
  {
    $this->response(400, ['message' => 'Not implemented.']);
  }

  protected function stockopname_status($id = null)
  {
    $opname = StockOpname::getRow(['id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock opname is not found.']);
    }

    $soItems = StockOpnameItem::get(['opname_id' => $id]);

    if (!$soItems) {
      $this->response(404, ['message' => 'Stock Opname items are not found.']);
    }

    $note       = getPost('note');
    $status     = getPost('status');
    $itemId     = getPost('item[id]');
    $itemLast   = getPost('item[last]');
    $itemLost   = [];

    if (empty($status)) {
      dbgprint('Status empty');
      return false;
    }

    $data = [
      'total_lost'    => 0,
      'total_plus'    => 0,
      'total_edited'  => 0,
      'status'        => $status,
      'note'          => $note
    ];

    DB::transStart();

    foreach ($soItems as $soItem) {
      for ($a = 0; $a < count($itemId); $a++) {
        if ($soItem->product_id == $itemId[$a]) {
          if ($status == 'confirmed') {
            $itemData = [
              'last_qty'  => $itemLast[$a],
              'subtotal'  => (($soItem->quantity - $itemLast[$a]) * $soItem->price)
            ];

            if (!StockOpnameItem::update((int)$soItem->id, $itemData)) {
              $this->response(400, ['message' => getLastError()]);
            }
          }

          if ($status == 'verified') {
            if (StockOpnameItem::isLost((int)$soItem->id)) {
              $itemLost[] = [
                'id'        => $soItem->product_id,
                'quantity'  => ($soItem->last_qty ?? $soItem->first_qty)
              ];

              $data['total_lost'] += floatval($soItem->subtotal);
            }

            if (StockOpnameItem::isModified((int)$soItem->id)) {
              $data['total_edited'] += 1;
            }
          }

          break;
        }
      }
    }

    if ($itemLost) {
      $lostId = StockAdjustment::add([
        'date'          => $opname->date,
        'warehouse_id'  => $opname->warehouse_id,
        'mode'          => 'overwrite',
        'note'          => $opname->reference
      ], $itemLost);

      if (!$lostId) {
        return false;
      }

      $data['adjustment_min_id'] = $lostId;
    }

    $data = $this->useAttachment($data, null, function ($upload) use ($status) {
      if ($status == 'confirmed' && !$upload->has('attachment')) {
        $this->response(400, ['message' => 'Attachment is required.']);
      }
    });

    if (!StockOpname::update((int)$id, $data)) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Status has been changed.']);
    }

    $this->response(400, ['message' => 'Failed']);
  }

  protected function stockopname_suggestion()
  {
    $userId = getGet('pic');
    $warehouseId = getGet('warehouse');
    $items = [];

    $user = User::getRow(['id' => $userId]);

    if (!$user) {
      $this->response(404, ['message' => 'User is not found.']);
    }

    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    // Get new SO Cycle.
    $soCycle = getNewSOCycle((int)$userId, (int)$warehouseId);

    $items = getStockOpnameSuggestion((int)$user->id, (int)$warehouse->id, $soCycle);

    if (!$items) {
      $this->response(400, ['message' => 'No items to be check.']);
    }

    foreach ($items as $item) {
      Product::sync(['id' => $item->id]);
    }

    // Updated items quantity.
    $items = getStockOpnameSuggestion((int)$user->id, (int)$warehouse->id, $soCycle);

    $this->response(200, ['data' => [
      'cycle' => $soCycle,
      'items' => $items,
    ]]);
  }

  protected function stockopname_view($id = null)
  {
    checkPermission('StockOpname.View');

    $opname = StockOpname::getRow(['id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock Opname is not found.']);
    }

    $this->data['opname'] = $opname;
    $this->data['title']  = lang('App.viewstockopname');

    $this->response(200, ['content' => view('Inventory/StockOpname/view', $this->data)]);
  }

  /**
   * Product Transfer
   * status:
   *  - packing
   *  - sent
   *  - received / received_partial
   */
  public function transfer()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductTransfer.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.producttransfer'), 'slug' => 'transfer', 'url' => '#']
      ],
      'content' => 'Inventory/Transfer/index',
      'title' => lang('App.producttransfer')
    ];

    return $this->buildPage($this->data);
  }

  protected function transfer_add()
  {
    checkPermission('ProductTransfer.Add');

    if (requestMethod() == 'POST') {
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'warehouse_id_from' => $warehouseIdFrom,
        'warehouse_id_to'   => $warehouseIdTo,
        'note'              => stripTags(getPost('note')),
      ];

      $itemId           = getPost('item[id]');
      $itemCode         = getPost('item[code]');
      $itemMarkonPrice  = getPost('item[markon_price]');
      $itemSpec         = getPost('item[spec]');
      $itemQty          = getPost('item[quantity]');

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'            => intval($itemId[$a]),
          'quantity'      => floatval($itemQty[$a]),
          'markon_price'  => filterNumber($itemMarkonPrice[$a]),
          'spec'          => stripTags($itemSpec[$a])
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = ProductTransfer::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Product Transfer has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproducttransfer');

    $this->response(200, ['content' => view('Inventory/Transfer/add', $this->data)]);
  }

  protected function transfer_delete($id = null)
  {
    checkPermission('ProductTransfer.Delete');

    $pt = ProductTransfer::getRow(['id' => $id]);

    if (!$pt) {
      $this->response(404, ['message' => 'Product Transfer is not found.']);
    }

    DB::transStart();

    if (!ProductTransfer::delete(['id' => $id])) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product Transfer has been deleted.']);
    }

    $this->response(400, ['message' => 'Failed to delete Product Transfer.']);
  }

  protected function transfer_edit($id = null)
  {
    checkPermission('ProductTransfer.Edit');

    $pt = ProductTransfer::getRow(['id' => $id]);

    if (requestMethod() == 'POST' && isAJAX()) {
      $createdAt        = dateTimePHP(getPost('date'));
      $createdBy        = getPost('created_by');
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');
      $note             = getPost('note');
      $item             = getPost('item');
      $status           = $pt->status;

      $items = [];
      $productSize = count($item['id']);

      for ($a = 0; $a < $productSize; $a++) {
        $items[] = [
          'id'            => floatval($item['id'][$a]),
          'markon_price'  => filterNumber($item['markon_price'][$a]),
          'quantity'      => filterNumber($item['quantity'][$a]),
          'received_qty'  => filterNumber($item['received'][$a]),
          'spec'          => stripTags($item['spec'][$a]),
          'status'        => $status
        ];
      }

      $data = [
        'created_at'        => $createdAt,
        'created_by'        => $createdBy,
        'warehouse_id_from' => $warehouseIdFrom,
        'warehouse_id_to'   => $warehouseIdTo,
        'status'            => $status,
        'note'              => stripTags($note)
      ];

      DB::transStart();

      $this->useAttachment($data);

      if (!ProductTransfer::update((int)$id, $data, $items)) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Transfer has been updated.']);
      }

      $this->response(400, ['message' => 'Failed to update Product Transfer.']);
    }

    $ptitems = ProductTransferItem::get(['transfer_id' => $id]);
    $items = [];

    foreach ($ptitems as $ptitem) {
      $product      = Product::getRow(['id' => $ptitem->product_id]);
      $whProductFr  = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $pt->warehouse_id_from]);
      $whProductTo  = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $pt->warehouse_id_to]);
      $unit         = Unit::getRow(['id' => $product->unit]);

      $items[] = [
        'id'              => $ptitem->product_id,
        'code'            => $ptitem->product_code,
        'name'            => $product->name,
        'markon_price'    => $ptitem->markon_price,
        'quantity'        => $ptitem->quantity,
        'current_qty'     => $whProductFr->quantity,
        'destination_qty' => $whProductTo->quantity,
        'received_qty'    => $ptitem->received_qty,
        'unit'            => $unit->code,
        'spec'            => ($ptitem->spec ?? ''), // Fix spec null/undefined on JS.
      ];
    }

    $this->data['transfer'] = $pt;
    $this->data['items']    = $items;
    $this->data['title']    = lang('App.editproducttransfer');

    $this->response(200, ['content' => view('Inventory/Transfer/edit', $this->data)]);
  }

  protected function transfer_plan()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $warehouses = getPost('check');

      if ($warehouses) {
        $failed  = 0;
        $success = 0;

        DB::transStart();

        foreach ($warehouses as $whId) {
          // Add product transfer by each warehouse id.
          if (ProductTransfer::addByWarehouseId($whId)) {
            $success++;
          } else {
            $failed++;
          }
        }

        DB::transComplete();

        if (DB::transStatus()) {
          $this->response(201, ['message' => "{$success} Product Transfer berhasil ditambahkan. {$failed} gagal ditambahkan."]);
        }

        $this->response(400, ['message' => getLastError()]);
      }

      $this->response(400, ['message' => 'Pilih warehouse untuk menambah Transfer Plan.']);
    }

    $this->data['title'] = lang('App.transferplan');

    $this->response(200, ['content' => view('Inventory/Transfer/plan', $this->data)]);
  }

  protected function transfer_status($id = null)
  {
    $transfer = ProductTransfer::getRow(['id' => $id]);

    if (!$transfer) {
      $this->response(404, ['message' => 'Product Transfer is not found.']);
    }

    $transferItems = ProductTransferItem::get(['transfer_id' => $transfer->id]);

    if (!$transferItems) {
      $this->response(404, ['message' => 'Product Transfer items are not found.']);
    }

    $items        = [];
    $receivedQty  = 0;
    $status       = getPost('status');
    $note         = stripTags(getPost('note'));

    foreach ($transferItems as $transferItem) {
      if ($status == 'received') {
        $receivedQty = $transferItem->quantity;
      }

      $items[] = [
        'id'            => $transferItem->product_id,
        'quantity'      => $transferItem->quantity,
        'received_qty'  => $receivedQty,
        'markon_price'  => $transferItem->markon_price,
        'spec'          => $transferItem->spec,
      ];
    }

    DB::transStart();

    $res = ProductTransfer::update((int)$id, ['status' => $status, 'note' => $note], $items);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product Transfer status has been updated.']);
    }

    $this->response(400, ['message' => 'Failed to update status.']);
  }

  protected function transfer_view($id = null)
  {
    checkPermission('ProductTransfer.View');

    $transfer = ProductTransfer::getRow(['id' => $id]);

    if (!$transfer) {
      $this->response(404, ['message' => 'Product Transfer is not found.']);
    }

    $this->data['transfer'] = $transfer;
    $this->data['title']    = lang('App.viewproducttransfer');

    $this->response(200, ['content' => view('Inventory/Transfer/view', $this->data)]);
  }

  public function usagehistory()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Product.History');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.usagehistory'), 'slug' => 'usagehistory', 'url' => '#']
      ],
      'content' => 'Inventory/UsageHistory/index',
      'title' => lang('App.usagehistory')
    ];

    return $this->buildPage($this->data);
  }
}
