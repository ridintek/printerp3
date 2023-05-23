<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <?php if (hasAccess('ProductPurchase.Add')) : ?>
              <a class="btn btn-tool bg-gradient-success use-tooltip" href="<?= base_url('inventory/purchase/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable" title="<?= lang('App.addproductpurchase') ?>">
                <i class="fad fa-plus-circle"></i>
              </a>
            <?php endif; ?>
            <?php if (hasAccess('ProductPurchase.Plan')) : ?>
              <a class="btn btn-tool bg-gradient-info use-tooltip" href="<?= base_url('inventory/purchase/plan') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable" title="<?= lang('App.purchaseplan') ?>">
                <i class="fad fa-calendar-arrow-down"></i>
              </a>
            <?php endif; ?>
            <a class="btn btn-tool bg-gradient-warning use-tooltip" href="#" data-widget="control-sidebar" title="<?= lang('App.filter') ?>" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.supplier'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.paymentstatus'); ?></th>
                <th><?= lang('App.grandtotal'); ?></th>
                <th><?= lang('App.paid'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.supplier'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.paymentstatus'); ?></th>
                <th><?= lang('App.grandtotal'); ?></th>
                <th><?= lang('App.paid'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Control Sidebar -->
<aside class="control-sidebar">
  <!-- Control sidebar content goes here -->
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-indigo">
          <div class="card-title"><i class="fad fa-filter"></i> <?= lang('App.filter') ?></div>
        </div>
        <div class="card-body control-sidebar-content" style="max-height:400px">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-biller"><?= lang('App.biller') ?></label>
                <select id="filter-biller" class="select-allow-clear" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
                  <option value=""></option>
                  <?php foreach (\App\Models\Biller::get(['active' => 1]) as $bl) : ?>
                    <option value="<?= $bl->code ?>"><?= $bl->name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-warehouse"><?= lang('App.warehouse') ?></label>
                <select id="filter-warehouse" class="select-allow-clear" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" multiple>
                  <option value=""></option>
                  <?php foreach (\App\Models\Warehouse::get(['active' => 1]) as $wh) : ?>
                    <option value="<?= $wh->code ?>"><?= $wh->name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value=""></option>
                  <option value="completed"><?= lang('Status.completed') ?></option>
                  <option value="completed_partial"><?= lang('Status.completed_partial') ?></option>
                  <option value="delivered"><?= lang('Status.delivered') ?></option>
                  <option value="finished"><?= lang('Status.finished') ?></option>
                  <option value="inactive"><?= lang('Status.inactive') ?></option>
                  <option value="need_payment"><?= lang('Status.need_payment') ?></option>
                  <option value="preparing"><?= lang('Status.preparing') ?></option>
                  <option value="waiting_production"><?= lang('Status.waiting_production') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-paymentstatus"><?= lang('App.paymentstatus') ?></label>
                <select id="filter-paymentstatus" class="select-allow-clear" data-placeholder="<?= lang('App.paymentstatus') ?>" style="width:100%" multiple>
                  <option value=""></option>
                  <option value="due"><?= lang('Status.due') ?></option>
                  <option value="due_partial"><?= lang('Status.due_partial') ?></option>
                  <option value="expired"><?= lang('Status.expired') ?></option>
                  <option value="paid"><?= lang('Status.paid') ?></option>
                  <option value="partial"><?= lang('Status.partial') ?></option>
                  <option value="pending"><?= lang('Status.pending') ?></option>
                  <option value="waiting_transfer"><?= lang('Status.waiting_transfer') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-createdby"><?= lang('App.createdby') ?></label>
                <select id="filter-createdby" class="select-user" data-placeholder="<?= lang('App.createdby') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-customer"><?= lang('App.customer') ?></label>
                <select id="filter-customer" class="select-customer" data-placeholder="<?= lang('App.customer') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="checkbox" id="filter-receivable" value="1">
                <label for="filter-receivable"><?= lang('App.receivable') ?></label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-startdate"><?= lang('App.startdate') ?></label>
                <input type="date" id="filter-startdate" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-enddate"><?= lang('App.enddate') ?></label>
                <input type="date" id="filter-enddate" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-warning filter-clear"><?= lang('App.clear') ?></button>
          <button class="btn btn-primary filter-apply"><?= lang('App.apply') ?></button>
        </div>
      </div>
    </div>
  </div>
</aside>
<!-- /.control-sidebar -->

<script type="module">
  import {
    TableFilter
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  TableFilter.bind('apply', '.filter-apply');
  TableFilter.bind('clear', '.filter-clear');

  TableFilter.on('clear', () => {
    $('#filter-warehouse').val([]).trigger('change');
    $('#filter-status').val([]).trigger('change');
    $('#filter-createdby').val([]).trigger('change');
    $('#filter-startdate').val('');
    $('#filter-enddate').val('');
  });
</script>
<script>
  $(document).ready(function() {
    "use strict";

    erp.table = $('#Table').DataTable({
      ajax: {
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/inventory/getProductPurchases'
      },
      columnDefs: [{
        targets: [0, 1],
        orderable: false
      }],
      fixedHeader: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [1, 'desc']
      ],
      processing: true,
      responsive: true,
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });
  });
</script>