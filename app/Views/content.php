<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="author" content="Riyan Widiyanto">
  <meta name="company" content="Ridintek Industri">
  <meta name="developer" content="Riyan Widiyanto">
  <meta name="website" content="https://ridintek.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
  <title>PrintERP 3</title>
  <link rel="icon" href="<?= base_url(); ?>/favicon.ico">
  <!-- Third party -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/alertifyjs/css/alertify.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/datatables/datatables.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/flag-icon/css/flag-icon.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fullcalendar/lib/main.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/icheck/skins/all.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/jquery-ui/jquery-ui.min.css">
  <!-- <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/overlayscrollbars/css/OverlayScrollbars.min.css"> -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/quill/quill.snow.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
  <!-- Custom style -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/app.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/common.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/loader.css?v=<?= $resver ?>">
  <script>
    const lang = JSON.parse(atob('<?= $lang64 ?>'));
    const <?= csrf_token() ?> = '<?= csrf_hash() ?>';
    const base_url = '<?= base_url(); ?>';
    const langId = '<?= session('login')->lang ?>';
    const permissions = JSON.parse(atob('<?= $permission64 ?>'));
    // ERP namespace.
    window.erp = {
      biller: {
        code: <?= session('login')->biller ? "'" . session('login')->biller . "'" : 'null' ?>,
        id: <?= session('login')->biller_id ?? 'null' ?>
      },
      chart: {},
      debug: false,
      echart: {},
      http: {
        callback: null,
        get: {},
        post: {}
      },
      modal: [], // Stackable modal.
      qms: {
        counter: {
          showTimer: true
        }
      },
      sale: {
        customer: null
      },
      select2: {
        bank: {},
        bankfrom: {},
        bankto: {},
        biller: {},
        creator: {},
        customer: {},
        machine: {},
        operator: {},
        product: {},
        supplier: {},
        techsupport: {},
        user: {},
        warehouse: {}
      },
      table: null,
      table: null,
      tableModal: null,
      user: {
        id: <?= session('login')->user_id ?>
      },
      warehouse: {
        code: <?= session('login')->warehouse ? "'" . session('login')->warehouse . "'" : 'null' ?>,
        id: <?= session('login')->warehouse_id ?? 'null' ?>
      }
    };
  </script>
</head>

<body class="hold-transition layout-fixed layout-navbar-fixed sidebar-mini text-sm<?= session('login')->dark_mode ? ' dark-mode' : '' ?> <?= session('login')->collapse ? ' sidebar-collapse' : '' ?>">
  <div class="page-loader-wrapper">
    <div class="page-loader">
      <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
      </svg>
    </div>
  </div>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand<?= session('login')->dark_mode ? ' navbar-dark bg-gradient-dark' : ' navbar-light bg-gradient-white' ?>">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
          <a class="nav-link use-tooltip" data-action="darkmode" title="<?= lang('App.toggledarklightmode') ?>" href="#" role="button"><i class="fad <?= session('login')->dark_mode ? 'fa-sun' : 'fa-moon' ?>"></i></a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fad fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fad fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link use-tooltip" href="#" data-action="clear-notification" title="<?= lang('App.clearnotification') ?>">
            <i class="fad fa-exclamation-circle"></i>
          </a>
        </li>

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item">
          <a class="nav-link use-tooltip" href="<?= base_url('profile/notification') ?>" data-toggle="modal" data-target="#ModalDefault" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable" title="<?= lang('App.notification') ?>">
            <i class="fad fa-bell"></i>
          </a>
        </li>

        <!-- Locale -->
        <li class="nav-item dropdown">
          <a class="nav-link use-tooltip" data-toggle="dropdown" href="#" title="<?= lang('App.language') ?>">
            <i class="flag-icon flag-icon-<?= App\Models\Locale::getRow(['code' => session('login')->lang])->flag ?>"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <?php
            foreach (App\Models\Locale::get() as $locale) :
              $active = '';

              if (session('login')->lang == $locale->code) $active = ' active'; ?>
              <a href="<?= base_url('lang/' . $locale->code) ?>" class="dropdown-item<?= $active ?> change-locale">
                <i class="flag-icon flag-icon-<?= $locale->flag ?> mr-2"></i> <?= $locale->name ?>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link use-tooltip" data-action="logout" href="#" title="<?= lang('App.logout') ?>">
            <i class="fad fa-door-open"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar elevation-4<?= session('login')->dark_mode ? ' sidebar-dark-primary' : ' sidebar-light-primary' ?>">
      <!-- Brand Logo -->
      <a href="<?= base_url() ?>" class="brand-link" data-action="link">
        <img src="<?= base_url() ?>/assets/dist/img/AdminLTELogo.png" alt="PrintERP 3" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">PrintERP 3</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="<?= base_url() ?>/attachment/<?= session('login')->avatar ?>" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="<?= base_url('profile') ?>" class="d-block" data-action="link"><?= session('login')->fullname ?></a>
          </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="true">
            <!-- Dashboard -->
            <li class="nav-item">
              <a href="<?= base_url() ?>" class="nav-link active" data-action="link" data-slug="dashboard">
                <i class="nav-icon fad fa-dashboard"></i>
                <p><?= lang('App.dashboard') ?></p>
              </a>
            </li>
            <?php if (hasAccess('All')) : ?>
              <!-- Debug -->
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="debug">
                  <i class="nav-icon fad fa-debug"></i>
                  <p>Debug <i class="fad fa-angle-right right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?= base_url('debug/invoice') ?>" class="nav-link" data-toggle="modal" data-target="#ModalDefault" data-modal-class="modal-xl">
                      <i class="nav-icon fad fa-window"></i>
                      <p>Invoice</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url('debug/modal') ?>" class="nav-link" data-toggle="modal" data-target="#ModalDefault">
                      <i class="nav-icon fad fa-window"></i>
                      <p>Modal</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url('debug/page') ?>" class="nav-link" data-action="link" data-slug="page">
                      <i class="nav-icon fad fa-page"></i>
                      <p>Page</p>
                    </a>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (hasAccess('Biller.View') || hasAccess('Warehouse.View')) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="division">
                  <i class="nav-icon fad fa-building" style="color:#ff4040"></i>
                  <p><?= lang('App.division') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Biller.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('division/biller') ?>" class="nav-link" data-action="link" data-slug="biller">
                        <i class="nav-icon fad fa-warehouse" style="color:#40ff40"></i>
                        <p><?= lang('App.biller') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Warehouse.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('division/warehouse') ?>" class="nav-link" data-action="link" data-slug="warehouse">
                        <i class="nav-icon fad fa-warehouse-alt" style="color:#4040ff"></i>
                        <p><?= lang('App.warehouse') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (hasAccess([
              'BankAccount.View', 'BankMutation.View', 'BankReconciliation.View', 'CashOnHand.Add',
              'Expense.View', 'Income.View', 'PaymentValidation.View'
            ])) : ?>
              <!-- Finance -->
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="finance">
                  <i class="nav-icon fad fa-usd" style="color:#00ff00"></i>
                  <p><?= lang('App.finance') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('BankAccount.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/bank') ?>" class="nav-link" data-action="link" data-slug="bank">
                        <i class="nav-icon fad fa-landmark" style="color:#ff8040;"></i>
                        <p><?= lang('App.bankaccount') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('BankMutation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/mutation') ?>" class="nav-link" data-action="link" data-slug="mutation">
                        <i class="nav-icon fad fa-box-usd" style="color:#ff00ff"></i>
                        <p><?= lang('App.bankmutation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('BankReconciliation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/reconciliation') ?>" class="nav-link" data-action="link" data-slug="reconciliation">
                        <i class="nav-icon fad fa-sync" style="color:#8040ff"></i>
                        <p><?= lang('App.bankreconciliation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('CashOnHand.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/cashonhand') ?>" class="nav-link" data-action="link" data-slug="cashonhand">
                        <i class="nav-icon fad fa-hand-holding-dollar" style="color:#ffff00"></i>
                        <p><?= lang('App.cashonhand') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Expense.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/expense') ?>" class="nav-link" data-action="link" data-slug="expense">
                        <i class="nav-icon fad fa-arrow-alt-left" style="color:#ff8080"></i>
                        <p><?= lang('App.expense') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Income.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/income') ?>" class="nav-link" data-action="link" data-slug="income">
                        <i class="nav-icon fad fa-arrow-alt-right" style="color:#8080ff"></i>
                        <p><?= lang('App.income') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('PaymentValidation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/validation') ?>" class="nav-link" data-action="link" data-slug="validation">
                        <i class="nav-icon fad fa-check" style="color:#80ff80"></i>
                        <p><?= lang('App.paymentvalidation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Human Resource -->
            <?php if (hasAccess(['Customer.View', 'CustomerGroup.View', 'User.View', 'UserGroup.View', 'Supplier.View'])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="humanresource">
                  <i class="nav-icon fad fa-users-cog" style="color:#4040ff"></i>
                  <p><?= lang('App.humanresource') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Customer.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('humanresource/customer') ?>" class="nav-link" data-action="link" data-slug="customer">
                        <i class="nav-icon fad fa-user-tie-hair" style="color:#80ffff"></i>
                        <p><?= lang('App.customer') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('CustomerGroup.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('humanresource/customergroup') ?>" class="nav-link" data-action="link" data-slug="customergroup">
                        <i class="nav-icon fad fa-users" style="color:#40ff80"></i>
                        <p><?= lang('App.customergroup') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('User.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('humanresource/user') ?>" class="nav-link" data-action="link" data-slug="user">
                        <i class="nav-icon fad fa-user" style="color:#ff8040"></i>
                        <p><?= lang('App.user') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('UserGroup.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('humanresource/usergroup') ?>" class="nav-link" data-action="link" data-slug="usergroup">
                        <i class="nav-icon fad fa-users" style="color:#8080ff"></i>
                        <p><?= lang('App.usergroup') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Supplier.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('humanresource/supplier') ?>" class="nav-link" data-action="link" data-slug="supplier">
                        <i class="nav-icon fad fa-user-tie-hair" style="color:#e0e040"></i>
                        <p><?= lang('App.supplier') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Inventory -->
            <?php if (hasAccess([
              'InternalUse.View', 'Product.CloudSync', 'Product.History', 'Product.View',
              'ProductCategory.View', 'ProductMutation.View', 'ProductPurchase.View', 'ProductTransfer.View',
              'StockAdjustment.View', 'StockOpname.View'
            ])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="inventory">
                  <i class="nav-icon fad fa-box-open-full" style="color:#e0e040"></i>
                  <p><?= lang('App.inventory') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Product.CloudSync')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/cloudsync') ?>" class="nav-link" data-action="link" data-slug="cloudsync">
                        <i class="nav-icon fad fa-cloud-check"></i>
                        <p><?= lang('App.cloudsync') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('InternalUse.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/internaluse') ?>" class="nav-link" data-action="link" data-slug="internaluse">
                        <i class="nav-icon fad fa-hand-holding-box" style="color:#ff8040"></i>
                        <p><?= lang('App.internaluse') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('ProductCategory.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/category') ?>" class="nav-link" data-action="link" data-slug="category">
                        <i class="nav-icon fad fa-boxes-packing" style="color:#ff0040"></i>
                        <p><?= lang('App.category') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('ProductMutation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/mutation') ?>" class="nav-link" data-action="link" data-slug="mutation">
                        <i class="nav-icon fad fa-cart-flatbed-boxes" style="color:#ff80ff"></i>
                        <p><?= lang('App.mutation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Product.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/product') ?>" class="nav-link" data-action="link" data-slug="product">
                        <i class="nav-icon fad fa-box-up" style="color:#40ffff"></i>
                        <p><?= lang('App.product') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('StockAdjustment.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/stockadjustment') ?>" class="nav-link" data-action="link" data-slug="stockadjustment">
                        <i class="nav-icon fad fa-sliders" style="color:#e0e040"></i>
                        <p><?= lang('App.stockadjustment') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('StockOpname.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/stockopname') ?>" class="nav-link" data-action="link" data-slug="stockopname">
                        <i class="nav-icon fad fa-box-check" style="color:#4040ff"></i>
                        <p><?= lang('App.stockopname') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('ProductPurchase.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/purchase') ?>" class="nav-link" data-action="link" data-slug="purchase">
                        <i class="nav-icon fad fa-cart-plus" style="color:#ff4040"></i>
                        <p><?= lang('App.purchase') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('ProductTransfer.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/transfer') ?>" class="nav-link" data-action="link" data-slug="transfer">
                        <i class="nav-icon fad fa-exchange" style="color:#80ff40"></i>
                        <p><?= lang('App.transfer') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Product.History')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('inventory/usagehistory') ?>" class="nav-link" data-action="link" data-slug="usagehistory">
                        <i class="nav-icon fad fa-box-ballot" style="color:#8040ff"></i>
                        <p><?= lang('App.usagehistory') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Maintenance -->
            <?php if (hasAccess(['MaintenanceReport.View', 'MaintenanceReview.View', 'MaintenanceSchedule.View'])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="maintenance">
                  <i class="nav-icon fad fa-cog" style="color:#ff0040"></i>
                  <p><?= lang('App.maintenance') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('MaintenanceReport.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('maintenance/report') ?>" class="nav-link" data-action="link" data-slug="maintenancereport">
                        <i class="nav-icon fad fa-check-to-slot" style="color:#80ff40"></i>
                        <p><?= lang('App.maintenancereport') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('MaintenanceLog.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('maintenance/log') ?>" class="nav-link" data-action="link" data-slug="maintenancelog">
                        <i class="nav-icon fad fa-th" style="color:#80ffff"></i>
                        <p><?= lang('App.maintenancelog') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('MaintenanceSchedule.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('maintenance/schedule') ?>" class="nav-link" data-action="link" data-slug="maintenanceschedule">
                        <i class="nav-icon fad fa-calendar" style="color:#e0e040"></i>
                        <p><?= lang('App.maintenanceschedule') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Notification -->
            <?php if (hasAccess('Notification.View')) : ?>
              <li class="nav-item">
                <a href="<?= base_url('notification') ?>" class="nav-link" data-action="link" data-slug="notification">
                  <i class="nav-icon fad fa-bell" style="color:darkorange"></i>
                  <p><?= lang('App.notification') ?>
                  </p>
                </a>
              </li>
            <?php endif; ?>
            <!-- Production -->
            <?php if (hasAccess(['Sale.Complete', 'TrackingPOD.View'])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="production">
                  <i class="nav-icon fad fa-scissors" style="color:#8080ff"></i>
                  <p><?= lang('App.production') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Sale.Complete')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('production') ?>" class="nav-link" data-action="link" data-slug="saleitem">
                        <i class="nav-icon fad fa-box-check" style="color:#80ff80"></i>
                        <p><?= lang('App.saleitem') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('TrackingPOD.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('production/trackingpod') ?>" class="nav-link" data-action="link" data-slug="trackingpod">
                        <i class="nav-icon fad fa-list" style="color:#ff0040"></i>
                        <p>TrackingPOD</p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- QMS -->
            <?php if (hasAccess('QMS.View')) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="qms">
                  <i class="nav-icon fad fa-users-class" style="color:#ff80ff"></i>
                  <p>QMS <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('QMS.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('qms') ?>" class="nav-link" data-action="link" data-slug="queue">
                        <i class="nav-icon fad fa-list" style="color:#40ffff"></i>
                        <p><?= lang('App.queue') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('QMS.Counter')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('qms/counter') ?>" class="nav-link" data-action="link" data-slug="counter">
                        <i class="nav-icon fad fa-user-headset" style="color:#e0e040"></i>
                        <p>Counter</p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('QMS.Display')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('qms/display?active=1') ?>" class="nav-link" target="_blank">
                        <i class="nav-icon fad fa-desktop" style="color:#ff8080"></i>
                        <p><?= lang('App.display') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('QMS.Registration')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('qms/registration') ?>" class="nav-link" target="_blank">
                        <i class="nav-icon fad fa-file-alt" style="color:#80ff40"></i>
                        <p><?= lang('App.registration') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Report -->
            <?php if (hasAccess(['Report.DailyPerformance', 'Report.Debt', 'Report.IncomeStatement', 'Report.InventoryBalance', 'Report.Maintenance', 'Report.Payment', 'Report.Receivable'])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="report">
                  <i class="nav-icon fad fa-file-chart-pie" style="color:#80ff40"></i>
                  <p><?= lang('App.report') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Report.DailyPerformance')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/dailyperformance') ?>" class="nav-link" data-action="link" data-slug="dailyperformance">
                        <i class="nav-icon fad fa-chart-mixed" style="color:#ff0040"></i>
                        <p><?= lang('App.dailyperformance') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.Debt')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/debt') ?>" class="nav-link" data-action="link" data-slug="debt">
                        <i class="nav-icon fad fa-receipt" style="color:#ff8040"></i>
                        <p><?= lang('App.debt') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.IncomeStatement')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/incomestatement') ?>" class="nav-link" data-action="link" data-slug="incomestatement">
                        <i class="nav-icon fad fa-money-bill-trend-up" style="color:#4040ff"></i>
                        <p><?= lang('App.incomestatement') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.InventoryBalance')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/inventorybalance') ?>" class="nav-link" data-action="link" data-slug="inventorybalance">
                        <i class="nav-icon fad fa-box-dollar" style="color:#80ffff"></i>
                        <p><?= lang('App.inventorybalance') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.Payment')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/payment') ?>" class="nav-link" data-action="link" data-slug="payment">
                        <i class="nav-icon fad fa-money-bill-wave" style="color:#80ff40"></i>
                        <p><?= lang('App.payment') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.PrintERP')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/printerp') ?>" class="nav-link" data-action="link" data-slug="printerp">
                        <i class="nav-icon fad fa-money-bill-wave" style="color:#4040ff"></i>
                        <p>PrintERP</p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Report.Receivable')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('report/receivable') ?>" class="nav-link" data-action="link" data-slug="receivable">
                        <i class="nav-icon fad fa-file-invoice-dollar" style="color:#8080ff"></i>
                        <p><?= lang('App.receivable') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Sales -->
            <?php if (hasAccess(['Sale.View', 'Voucher.View'])) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="sale">
                  <i class="nav-icon fad fa-cash-register" style="color:#40ffff"></i>
                  <p><?= lang('App.sale') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Sale.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('sale') ?>" class="nav-link" data-action="link" data-slug="invoice">
                        <i class="nav-icon fad fa-file-invoice" style="color:#ff8040"></i>
                        <p><?= lang('App.invoice') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Voucher.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('sale/voucher') ?>" class="nav-link" data-action="link" data-slug="voucher">
                        <i class="nav-icon fad fa-file-invoice" style="color:#80ff40"></i>
                        <p><?= lang('App.voucher') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Setting -->
            <?php if (hasAccess('Setting.Edit')) : ?>
              <li class="nav-item">
                <a href="<?= base_url('setting') ?>" class="nav-link" data-slug="setting">
                  <i class="nav-icon fad fa-cogs" style="color:#80ff40"></i>
                  <p><?= lang('App.setting') ?> <i class="fad fa-angle-right right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('All')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('setting/permission') ?>" class="nav-link" data-action="link" data-slug="permission">
                        <i class="nav-icon fad fa-user-lock" style="color:#ff4040"></i>
                        <p><?= lang('App.permission') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>

            <!-- Ticket -->
            <?php if (hasAccess('Ticket.View')) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-action="-link" data-slug="ticket">
                  <i class="nav-icon fad fa-ticket"></i>
                  <p><?= lang('App.ticket') ?></p>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </aside>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0" data-type="title"></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right" data-type="breadcrumb">
              </ol>
            </div>
          </div>
        </div>
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12" data-type="notification">
              <?php foreach (\App\Models\Notification::select('*')->orderBy('created_at', 'DESC')->get(['pinned' => 1, 'status' => 'active']) as $notif) : ?>
                <?php if (!hasNotificationAccess(getJSON($notif->scope))) continue; ?>
                <div class="alert alert-<?= $notif->type ?> alert-dismissible">
                  <button class="close" data-dismiss="alert">&times;</button>
                  <h5><?= $notif->title ?></h5>
                  <?= $notif->note ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <div class="content" data-type="content">
        <div class="content-loader">
          <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
          </svg>
        </div>
      </div>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        <a href="https://www.ridintek.com" target="_blank">PrintERP version 3.0</a>
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; <?= date('Y') ?> <a href="https://www.indoprinting.co.id">INDOPRINTING</a>.</strong>
    </footer>
    <div class="modal fade" id="ModalDefault">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalDefault2">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalStatic" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalStatic2" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="<?= base_url() ?>/assets/modules/jquery/jquery.min.js"></script>
  <!-- AdminLTE App -->
  <script src="<?= base_url() ?>/assets/dist/js/adminlte.min.js"></script>
  <!-- Application -->
  <script src="<?= base_url() ?>/assets/modules/alertifyjs/alertify.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/bootstrap-validate/bootstrap-validate.js"></script>
  <script src="<?= base_url() ?>/assets/modules/bs-custom-file-input/bs-custom-file-input.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/echarts/echarts.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/datatables/datatables.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/fullcalendar/lib/main.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/icheck/icheck.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/jquery-ui/jquery-ui.min.js"></script>
  <!-- <script src="<?= base_url() ?>/assets/modules/overlayscrollbars/js/OverlayScrollbars.min.js"></script> -->
  <script src="<?= base_url() ?>/assets/modules/quill/quill.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/select2/js/select2.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/sweetalert2/sweetalert2.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/toastr/toastr.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/socket.io/socket.io.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="<?= base_url() ?>/assets/modules/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    /**
     * Not Used. Required by google maps.
     */
    function initmap() {

    }

    (function() {
      // let socket = io.connect(':3000');

      // socket.on('connect', () => {
      //   console.log('Socket.io connected: ' + socket.id);
      // });

      // socket.on('notify', (message) => {
      //   toastr.success(message);
      // });

      // socket.on('session', (data) => {
      //   localStorage.setItem('user_id', data.userId);
      //   socket.userId = data.userId;
      // });
    })();
  </script>
  <script async src="https://maps.googleapis.com/maps/api/js?key=<?= env('API_GMAPS') ?>&libraries=places&v=weekly&callback=initmap"></script>
  <!-- Custom -->
  <script src="<?= base_url() ?>/assets/app/js/common.js?v=<?= $resver ?>"></script>
  <script src="<?= base_url() ?>/assets/app/js/app.js?v=<?= $resver ?>"></script>
  <script>
    typing('nopgboss', () => {
      if (erp.debug) {
        erp.debug = false;
        toastr.info('Debug has been deactivated.');
      } else {
        erp.debug = true;
        toastr.info('Debug has been activated.');
      }
    });

    if (window.innerWidth <= 480 && window.innerHeight <= 700) {
      console.log('Mobile Version:' + window.innerWidth + ':' + window.innerHeight);

      if (erp.user.id == 1) {
        toastr.error('Mobile Version:' + window.innerWidth + ':' + window.innerHeight);
      }
    }
  </script>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-Y8FVKN22WM"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-Y8FVKN22WM');
  </script>
</body>

</html>
