<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a id="export" class="btn btn-tool bg-gradient-success" href="<?= base_url('report/export/getDebts') ?>" data-action="export">
              <i class="fad fa-download"></i>
            </a>
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
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
                <th><?= lang('App.duedate'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
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
                <th><?= lang('App.duedate'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
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
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value="need_approval"><?= lang('Status.need_approval') ?></option>
                  <option value="ordered"><?= lang('Status.ordered') ?></option>
                  <option value="received"><?= lang('Status.received') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-paymentstatus"><?= lang('App.paymentstatus') ?></label>
                <select id="filter-paymentstatus" class="select-allow-clear" data-placeholder="<?= lang('App.paymentstatus') ?>" style="width:100%" multiple>
                  <option value="need_approval"><?= lang('Status.need_approval') ?></option>
                  <option value="pending"><?= lang('Status.pending') ?></option>
                  <option value="paid"><?= lang('Status.paid') ?></option>
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
                <label for="filter-supplier"><?= lang('App.supplier') ?></label>
                <select id="filter-supplier" class="select-supplier" data-placeholder="<?= lang('App.supplier') ?>" style="width:100%" multiple>
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

  TableFilter
    .bind('apply', '.filter-apply')
    .bind('clear', '.filter-clear')
    .on('clear', () => {
      $('#filter-status').val([]).trigger('change');
      $('#filter-supplier').val([]).trigger('change');
      $('#filter-paymentstatus').val([]).trigger('change');
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

          let createdBy = $('#filter-createdby').val();
          let suppliers = $('#filter-supplier').val();
          let status = $('#filter-status').val();
          let paymentStatus = $('#filter-paymentstatus').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (createdBy) {
            data.created_by = createdBy;
          }

          if (suppliers) {
            data.supplier = suppliers;
          }

          if (status) {
            data.status = status;
          }

          if (paymentStatus) {
            data.payment_status = paymentStatus;
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
        url: base_url + '/report/getDebts'
      },
      columnDefs: [{
        targets: [0, 12],
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