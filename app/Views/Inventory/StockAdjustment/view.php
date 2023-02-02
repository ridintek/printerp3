<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form id="form">
    <?= csrf_field() ?>
    <table class="table table-hover table-sm table-striped">
      <tbody>
        <tr>
          <td><?= lang('App.id') ?></td>
          <td><?= $adjustment->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.date') ?></td>
          <td><?= formatDate($adjustment->date) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.reference') ?></td>
          <td><?= $adjustment->reference ?></td>
        </tr>
        <tr>
          <td><?= lang('App.warehouse') ?></td>
          <td><?= \App\Models\Warehouse::getRow(['code' => $adjustment->warehouse])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.note') ?></td>
          <td><?= $adjustment->note ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDate($adjustment->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $adjustment->created_by])->fullname ?></td>
        </tr>
        <?php if ($adjustment->updated_at) : ?>
          <tr>
            <td><?= lang('App.updatedat') ?></td>
            <td><?= formatDate($adjustment->updated_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($adjustment->updated_by) : ?>
          <tr>
            <td><?= lang('App.updatedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $adjustment->created_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
  });
</script>