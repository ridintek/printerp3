<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success use-tooltip" href="<?= base_url('inventory/mutation/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable" title="<?= lang('App.addproductmutation') ?>">
              <i class="fad fa-plus-circle"></i>
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
                <th><?= lang('App.warehouse') . ' (' . lang('App.from') . ')'; ?></th>
                <th><?= lang('App.warehouse') . ' (' . lang('App.to') . ')'; ?></th>
                <th><?= lang('App.item'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.status'); ?></th>
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
                <th><?= lang('App.warehouse') . ' (' . lang('App.from') . ')'; ?></th>
                <th><?= lang('App.warehouse') . ' (' . lang('App.to') . ')'; ?></th>
                <th><?= lang('App.item'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.status'); ?></th>
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
<script>
  $(document).ready(function() {
    "use strict";

    erp.table = $('#Table').DataTable({
      ajax: {
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/inventory/getProductMutations'
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