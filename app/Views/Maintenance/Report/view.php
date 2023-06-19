<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title . " ({$modeLang})" ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <table id="ModalTable" class="table table-head-fixed table-hover table-striped">
    <thead>
      <tr>
        <th class="col-sm-1"></th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.condition') ?></th>
        <th><?= lang('App.note') ?></th>
        <th><?= lang('App.techsupportnote') ?></th>
        <th><?= lang('App.createdby') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th></th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.condition') ?></th>
        <th><?= lang('App.note') ?></th>
        <th><?= lang('App.techsupportnote') ?></th>
        <th><?= lang('App.createdby') ?></th>
      </tr>
    </tfoot>
  </table>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    'use strict';

    let tableData = JSON.parse(`<?= json_encode($param) ?>`);

    tableData.__ = __;

    erp.tableModal = $('#ModalTable').DataTable({
      ajax: {
        data: tableData,
        method: 'POST',
        url: base_url + '/maintenance/getProductReport'
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