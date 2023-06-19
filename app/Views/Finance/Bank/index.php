<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success use-tooltip" href="<?= base_url('finance/bank/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-dialog-centered modal-dialog-scrollable" title="<?= lang('App.addbankaccount') ?>">
              <i class="fad fa-plus-circle"></i>
            </a>
            <a class="btn btn-tool bg-gradient-indigo use-tooltip" href="<?= base_url('finance/bank/sync') ?>" data-action="confirm" title="Sync Bank Account">
              <i class="fad fa-sync"></i>
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
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.holder'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.biccode'); ?></th>
                <th class="col-sm-2"><?= lang('App.status'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.holder'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.biccode'); ?></th>
                <th><?= lang('App.status'); ?></th>
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
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value="1"><?= lang('Status.active') ?></option>
                  <option value="0"><?= lang('Status.inactive') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-type"><?= lang('App.type') ?></label>
                <select id="filter-type" class="select-allow-clear" data-placeholder="<?= lang('App.type') ?>" style="width:100%" multiple>
                  <option value="Cash"><?= lang('App.cash') ?></option>
                  <option value="EDC"><?= lang('App.edc') ?></option>
                  <option value="Transfer"><?= lang('App.transfer') ?></option>
                </select>
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
    $('#filter-status').val([]).trigger('change');
    $('#filter-type').val([]).trigger('change');
  });
</script>
<script>
  $(document).ready(function() {
    "use strict";

    erp.table = $('#Table').DataTable({
      ajax: {
        data: (data) => {
          data.__ = __;

          let billers = $('#filter-biller').val();
          let status = $('#filter-status').val();
          let type = $('#filter-type').val();

          if (billers) {
            data.biller = billers;
          }

          if (status) {
            data.status = status;
          }

          if (type) {
            data.type = type;
          }

          return data;
        },
        method: 'POST',
        url: base_url + '/finance/getBanks'
      },
      columnDefs: [{
        targets: [0],
        orderable: false
      }],
      fixedHeader: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [1, 'asc']
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