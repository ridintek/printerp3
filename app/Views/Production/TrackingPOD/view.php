<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <form id="form">
            <?= csrf_field() ?>
            <table class="table table-head-fixed table-hover table-striped">
              <tbody>
                <tr>
                  <td><?= lang('App.pic') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $tpod->created_by])->fullname ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.warehouse') ?></td>
                  <td><?= \App\Models\Warehouse::getRow(['id' => $tpod->warehouse_id])->name ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.category') ?></td>
                  <td><?= \App\Models\Product::getRow(['id' => $tpod->pod_id])->code ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.currentclick') ?></td>
                  <td><?= formatNumber($currentClick) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.startclick') ?></td>
                  <td><?= formatNumber($tpod->start_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.endclick') ?></td>
                  <td><?= formatNumber($tpod->end_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.usageclick') ?></td>
                  <td><?= formatNumber($tpod->usage_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.todayclick') ?></td>
                  <td><?= formatNumber($tpod->today_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.rejectmachine') ?></td>
                  <td><?= formatNumber($tpod->mc_reject) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.rejectoperator') ?></td>
                  <td><?= formatNumber($tpod->op_reject) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.totalreject') ?></td>
                  <td><?= formatNumber($tpod->mc_reject + $tpod->op_reject) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.tolerance') ?> (%)</td>
                  <td><?= formatNumber($tpod->tolerance) ?>%</td>
                </tr>
                <tr>
                  <td><?= lang('App.toleranceclick') ?></td>
                  <td><?= formatNumber($tpod->tolerance_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.costclick') ?></td>
                  <td><?= formatCurrency($tpod->cost_click) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.balance') ?></td>
                  <td><?= formatNumber($tpod->balance) ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.totalpenalty') ?></td>
                  <?php if ($tpod->total_penalty < 0) : ?>
                    <td>
                      <div class="badge bg-gradient-red p-2"><?= formatCurrency($tpod->total_penalty) ?></div>
                    </td>
                  <?php else : ?>
                    <td>
                      <div class="badge bg-gradient-green p-2"><?= formatCurrency($tpod->total_penalty) ?></div>
                    </td>
                  <?php endif; ?>
                </tr>
                <tr>
                  <td><?= lang('App.note') ?></td>
                  <td><?= htmlDecode($tpod->note) ?></td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/production/trackingpod/<?= $tpod->id ?>'
    });
  });
</script>