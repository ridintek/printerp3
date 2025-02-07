<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('finance/expense/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-plus-circle"></i>
            </a>
            <div class="btn-group btn-tool">
              <a class="btn bg-gradient-info dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="fad fa-download"></i>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="<?= base_url('report/export/expense') ?>" data-action="export" data-param='{"bni_format": true}'>
                  <i class="fad fa-download"></i> BNI Format
                </a>
                <a class="dropdown-item" href="<?= base_url('report/export/expense') ?>" data-action="export">
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
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.category'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.supplier'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.paymentstatus'); ?></th>
                <th><?= lang('App.paymentdate'); ?></th>
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
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.category'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.supplier'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.paymentstatus'); ?></th>
                <th><?= lang('App.paymentdate'); ?></th>
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
                <label for="filter-bank"><?= lang('App.bankaccount') ?></label>
                <select id="filter-bank" class="select-bank" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-category"><?= lang('App.category') ?></label>
                <select id="filter-category" class="select-income-category" data-placeholder="<?= lang('App.category') ?>" style="width:100%" multiple>
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
                  <option value="approved"><?= lang('Status.approved') ?></option>
                  <option value="need_approval"><?= lang('Status.need_approval') ?></option>
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
                  <option value="paid"><?= lang('Status.paid') ?></option>
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
    $('#filter-bank').val([]).trigger('change');
    $('#filter-biller').val([]).trigger('change');
    $('#filter-category').val([]).trigger('change');
    $('#filter-status').val([]).trigger('change');
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

          let bank = $('#filter-bank').val();
          let biller = $('#filter-biller').val();
          let category = $('#filter-category').val();
          let status = $('#filter-status').val();
          let paymentStatus = $('#filter-paymentstatus').val();
          let createdBy = $('#filter-createdby').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (bank) {
            data.bank = bank;
          }

          if (biller) {
            data.biller = biller;
          }

          if (category) {
            data.category = category;
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
        url: base_url + '/finance/getExpenses'
      },
      columnDefs: [{
        targets: [0, 1, 13],
        orderable: false
      }],
      fixedHeader: false,
      footerCallback: function(row, data, start, end, display) {
        let api = this.api();
        let columns = api.columns([6]).data();
        let grandTotal = 0;

        for (let a = 0; a < columns[0].length; a++) {
          grandTotal += filterNumber(columns[0][a]);
        }

        $(api.column(6).footer()).html(`<span class="float-right">${formatNumber(grandTotal)}</span>`);
      },
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [2, 'desc']
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