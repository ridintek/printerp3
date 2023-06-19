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
          <td><?= $pv->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.date') ?></td>
          <td><?= formatDateTime($pv->date) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.reference') ?></td>
          <td><?= $pv->reference ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biller') ?></td>
          <td><?= \App\Models\Biller::getRow(['id' => $pv->biller_id])->name ?></td>
        </tr>
        <?php if ($pv->bank_id) : ?>
          <tr>
            <td><?= lang('App.bankaccount') ?></td>
            <?php $bank = \App\Models\Bank::getRow(['id' => $pv->bank_id]) ?>
            <td><?= ($bank->number ? $bank->name . " ({$bank->number})" : $bank->name) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><?= lang('App.amount') ?></td>
          <td><?= formatCurrency($pv->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.uniquecode') ?></td>
          <td><?= formatCurrency($pv->unique) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.transfer') ?></td>
          <td><?= formatCurrency($pv->amount + $pv->unique) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.note') ?></td>
          <td><?= $pv->note ?></td>
        </tr>
        <tr>
          <td><?= lang('App.status') ?></td>
          <td><?= renderStatus($pv->status) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.expireddate') ?></td>
          <td><?= formatDateTime($pv->expired_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDateTime($pv->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $pv->created_by])->fullname ?></td>
        </tr>
        <?php if ($pv->attachment) : ?>
          <tr>
            <td><?= lang('App.attachment') ?></td>
            <td><img src="<?= base_url('attachment/' . $pv->attachment) ?>" style="max-width:300px; width:100%"></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>