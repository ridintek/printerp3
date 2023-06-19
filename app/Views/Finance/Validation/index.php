<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
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
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.total'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
                <th><?= lang('App.expireddate'); ?></th>
                <th><?= lang('App.transactiondate'); ?></th>
                <th><?= lang('App.verifiedat'); ?></th>
                <th><?= lang('App.uniquecode'); ?></th>
                <th><?= lang('App.description'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.total'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
                <th><?= lang('App.expireddate'); ?></th>
                <th><?= lang('App.transactiondate'); ?></th>
                <th><?= lang('App.verifiedat'); ?></th>
                <th><?= lang('App.uniquecode'); ?></th>
                <th><?= lang('App.description'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
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
                <label for="filter-bank"><?= lang('App.bankaccount') ?></label>
                <select id="filter-bank" class="select-bank" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%" multiple>
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
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value=""></option>
                  <option value="expired"><?= lang('Status.expired') ?></option>
                  <option value="pending"><?= lang('Status.pending') ?></option>
                  <option value="verified"><?= lang('Status.verified') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="checkbox" id="filter-manual" value="1">
                <label for="filter-manual"><?= lang('App.manual') ?></label>
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
    $('#filter-bank').val([]).trigger('change');
    $('#filter-biller').val([]).trigger('change');
    $('#filter-category').val([]).trigger('change');
    $('#filter-status').val([]).trigger('change');
    $('#filter-manual').iCheck('uncheck');
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

          let bank = $('#filter-bank').val();
          let biller = $('#filter-biller').val();
          let customer = $('#filter-customer').val();
          let status = $('#filter-status').val();
          let manual = $('#filter-manual');
          let createdBy = $('#filter-createdby').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (bank) {
            data.bank = bank;
          }

          if (biller) {
            data.biller = biller;
          }

          if (customer) {
            data.customer = customer;
          }

          if (status) {
            data.status = status;
          }

          if (manual.is(':checked')) {
            data.manual = manual.val();
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
        url: base_url + '/finance/getPaymentValidations'
      },
      columnDefs: [{
        targets: [0, 10],
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