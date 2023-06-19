<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-head-fixed-main table-hover table-striped" style="width:100%;">
            <thead>
              <tr>
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.subcategory'); ?></th>
                <th><?= lang('App.assignedat'); ?></th>
                <th><?= lang('App.assignedby'); ?></th>
                <th><?= lang('App.fixedat'); ?></th>
                <th><?= lang('App.pic'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.note'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.subcategory'); ?></th>
                <th><?= lang('App.assignedat'); ?></th>
                <th><?= lang('App.assignedby'); ?></th>
                <th><?= lang('App.fixedat'); ?></th>
                <th><?= lang('App.pic'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.note'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    "use strict";

    erp.table = $('#Table').DataTable({
      ajax: {
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/maintenance/getMaintenanceLogs'
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
        [5, 'desc']
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