<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <table id="ModalTable" class="table table-bordered table-hover">
      <thead>
        <tr>
          <th class="col-sm-1"><input class="checkbox-parent" type="checkbox"></th>
          <th><?= lang('App.warehouse') ?></th>
          <th><?= lang('App.visitday') ?></th>
          <th><?= lang('App.visitweek') ?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th><input class="checkbox-parent" type="checkbox"></th>
          <th><?= lang('App.warehouse') ?></th>
          <th><?= lang('App.visitday') ?></th>
          <th><?= lang('App.visitweek') ?></th>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-plus-circle"></i> <?= lang('App.create') ?></button>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
  $(document).ready(function() {
    'use strict';

    erp.tableModal = $('#ModalTable').DataTable({
      ajax: {
        data: {
          __: __
        },
        method: 'POST',
        url: base_url + '/inventory/getProductPurchasePlans'
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
      processing: true,
      responsive: true,
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/purchase/plan'
    });
  });
</script>