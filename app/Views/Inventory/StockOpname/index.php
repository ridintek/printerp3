<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('inventory/stockopname/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-plus-circle"></i>
            </a>
            <a id="sync" class="btn btn-tool bg-gradient-indigo use-tooltip" href="<?= base_url('inventory/stockopname/sync') ?>" data-action="confirm" title="<?= lang('App.sync') ?>">
              <i class="fad fa-sync"></i>
            </a>
            <a id="export" class="btn btn-tool bg-gradient-primary use-tooltip" href="#" title="<?= lang('App.export') ?>">
              <i class="fad fa-download"></i>
            </a>
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
                <th><?= lang('App.adjustmentplusref'); ?></th>
                <th><?= lang('App.adjustmentminref'); ?></th>
                <th><?= lang('App.pic'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.totallost'); ?></th>
                <th><?= lang('App.totalplus'); ?></th>
                <th><?= lang('App.totaledited'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.adjustmentplusref'); ?></th>
                <th><?= lang('App.adjustmentminref'); ?></th>
                <th><?= lang('App.pic'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.totallost'); ?></th>
                <th><?= lang('App.totalplus'); ?></th>
                <th><?= lang('App.totaledited'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
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
                  <option value=""></option>
                  <option value="checked"><?= lang('Status.checked') ?></option>
                  <option value="confirmed"><?= lang('Status.confirmed') ?></option>
                  <option value="good"><?= lang('Status.good') ?></option>
                  <option value="verified"><?= lang('Status.verified') ?></option>
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

          let warehouses = $('#filter-warehouse').val();
          let createdBy = $('#filter-createdby').val();
          let status = $('#filter-status').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (warehouses) {
            data.warehouse = warehouses;
          }

          if (createdBy) {
            data.created_by = createdBy;
          }

          if (status) {
            data.status = status;
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
        url: base_url + '/inventory/getStockOpnames'
      },
      columnDefs: [{
        targets: [0, 13],
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

    $('#export').click(() => {
      let warehouses = $('#filter-warehouse').val();
      let createdBy = $('#filter-createdby').val();
      let status = $('#filter-status').val();
      let startDate = $('#filter-startdate').val();
      let endDate = $('#filter-enddate').val();

      let data = {};

      if (warehouses) {
        data.warehouse = warehouses;
      }

      if (createdBy) {
        data.created_by = createdBy;
      }

      if (status) {
        data.status = status;
      }

      if (startDate) {
        data.start_date = startDate;
      }

      if (endDate) {
        data.end_date = endDate;
      }

      console.log(data);

      location.href = base_url + '/inventory/getStockOpnames?xls=1&' + serialize(data)
    });
  });
</script>