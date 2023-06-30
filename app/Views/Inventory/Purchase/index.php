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
            <div class="btn-group btn-tool">
              <a class="btn bg-gradient-info dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="fad fa-download"></i>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="<?= base_url('report/export/purchase') ?>" data-action="export" data-param='{"bni_format": true}'>
                  <i class="fad fa-download"></i> BNI Format
                </a>
                <a class="dropdown-item" href="<?= base_url('report/export/purchase') ?>" data-action="export">
                  <i class="fad fa-download"></i> Excel
                </a>
              </div>
            </div>
            <a class="btn btn-tool bg-gradient-warning use-tooltip" href="#" data-widget="control-sidebar" title="<?= lang('App.filter') ?>" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-head-fixed-main table-hover table-striped" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><input class="checkbox-parent" type="checkbox"></th>
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
                <th><input class="checkbox-parent" type="checkbox"></th>
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
                <select id="filter-biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-warehouse"><?= lang('App.warehouse') ?></label>
                <select id="filter-warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value="approved"><?= lang('Status.approved') ?></option>
                  <option value="ordered"><?= lang('Status.ordered') ?></option>
                  <option value="need_approval"><?= lang('Status.need_approval') ?></option>
                  <option value="received"><?= lang('Status.received') ?></option>
                  <option value="received_partial"><?= lang('Status.received_partial') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-paymentstatus"><?= lang('App.paymentstatus') ?></label>
                <select id="filter-paymentstatus" class="select-allow-clear" data-placeholder="<?= lang('App.paymentstatus') ?>" style="width:100%" multiple>
                  <option value="approved"><?= lang('Status.approved') ?></option>
                  <option value="need_approval"><?= lang('Status.need_approval') ?></option>
                  <option value="paid"><?= lang('Status.paid') ?></option>
                  <option value="partial"><?= lang('Status.partial') ?></option>
                  <option value="pending"><?= lang('Status.pending') ?></option>
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
    $('#filter-biller').val([]).trigger('change');
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
        data: (data) => {
          data.__ = __;

          let biller = $('#filter-biller').val();
          let status = $('#filter-status').val();
          let paymentStatus = $('#filter-paymentstatus').val();
          let createdBy = $('#filter-createdby').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (biller) {
            data.biller = biller;
          }

          if (status) {
            data.status = status;
          }

          if (paymentStatus) {
            data.payment_status = paymentStatus;
          }

          if (createdBy) {
            data.created_by = createdBy;
          }

          if (startDate) {
            data.start_date = startDate;
          }

          if (endDate) {
            data.end_date = endDate;
          }

          return data;
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
      pageLength: 50,
      processing: true,
      responsive: true,
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });
  });
</script>