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
          <td><?= $mutation->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.date') ?></td>
          <td><?= formatDateTime($mutation->date) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.reference') ?></td>
          <td><?= $mutation->reference ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biller') ?></td>
          <td><?= \App\Models\Biller::getRow(['id' => $mutation->biller_id])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.bankaccountfrom') ?></td>
          <?php $bankfrom = \App\Models\Bank::getRow(['id' => $mutation->bankfrom_id]) ?>
          <td><?= ($bankfrom->number ? $bankfrom->name . " ({$bankfrom->number})" : $bankfrom->name) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.bankaccountto') ?></td>
          <?php $bankto = \App\Models\Bank::getRow(['id' => $mutation->bankto_id]) ?>
          <td><?= ($bankto->number ? $bankto->name . " ({$bankto->number})" : $bankto->name) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.amount') ?></td>
          <td><?= formatCurrency($mutation->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.note') ?></td>
          <td><?= $mutation->note ?></td>
        </tr>
        <tr>
          <td><?= lang('App.status') ?></td>
          <td><?= renderStatus($mutation->status) ?></td>
        </tr>
        <?php if ($paymentValidation = \App\Models\PaymentValidation::getRow(['mutation_id' => $mutation->id])) : ?>
          <tr>
            <td><?= lang('App.paymentvalidation') ?></td>
            <td>
              <div class="card shadow">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6"><?= lang('App.expireddate') ?> :</div>
                    <div class="col-md-6"><?= formatDateTime($paymentValidation->expired_at) ?></div>
                  </div>
                  <div class="row">
                    <div class="col-md-6"><?= lang('App.amount') ?> :</div>
                    <div class="col-md-6"><?= formatCurrency($paymentValidation->amount) ?></div>
                  </div>
                  <div class="row">
                    <div class="col-md-6"><?= lang('App.uniquecode') ?> :</div>
                    <div class="col-md-6"><?= formatCurrency($paymentValidation->unique) ?></div>
                  </div>
                  <div class="row">
                    <div class="col-md-6"><?= lang('App.transfer') ?> :</div>
                    <div class="col-md-6"><?= formatCurrency($paymentValidation->amount + $paymentValidation->unique) ?></div>
                  </div>
                  <div class="row">
                    <div class="col-md-6"><?= lang('App.status') ?> :</div>
                    <div class="col-md-6"><?= renderStatus($paymentValidation->status) ?></div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDateTime($mutation->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $mutation->created_by])->fullname ?></td>
        </tr>
        <?php if ($mutation->updated_at) : ?>
          <tr>
            <td><?= lang('App.updatedat') ?></td>
            <td><?= formatDateTime($mutation->updated_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($mutation->updated_by) : ?>
          <tr>
            <td><?= lang('App.updatedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $mutation->created_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($mutation->attachment) : ?>
          <tr>
            <td><?= lang('App.attachment') ?></td>
            <td><img src="<?= base_url('attachment/' . $mutation->attachment) ?>" style="max-width:300px; width:100%"></td>
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