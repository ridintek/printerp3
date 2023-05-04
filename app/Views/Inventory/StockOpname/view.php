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
          <table class="table table-hover table-sm table-striped">
            <tbody>
              <tr>
                <td><?= lang('App.id') ?></td>
                <td><?= $opname->id ?></td>
              </tr>
              <tr>
                <td><?= lang('App.date') ?></td>
                <td><?= formatDateTime($opname->date) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.reference') ?></td>
                <td><?= $opname->reference ?></td>
              </tr>
              <tr>
                <td><?= lang('App.warehouse') ?></td>
                <td><?= \App\Models\Warehouse::getRow(['id' => $opname->warehouse_id])->name ?></td>
              </tr>
              <tr>
                <td><?= lang('App.status') ?></td>
                <td><?= renderStatus($opname->status) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.note') ?></td>
                <td><?= $opname->note ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdat') ?></td>
                <td><?= formatDateTime($opname->created_at) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdby') ?></td>
                <td><?= \App\Models\User::getRow(['id' => $opname->created_by])->fullname ?></td>
              </tr>
              <?php if ($opname->updated_at) : ?>
                <tr>
                  <td><?= lang('App.updatedat') ?></td>
                  <td><?= formatDateTime($opname->updated_at) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($opname->updated_by) : ?>
                <tr>
                  <td><?= lang('App.updatedby') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $opname->updated_by])->fullname ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-warning"><?= lang('App.item') ?></div>
        <div class="card-body">
          <form id="form">
            <?= csrf_field() ?>
            <input id="status" name="status" type="hidden" value="">
            <table class="table table-hover table-sm table-striped">
              <thead>
                <tr class="text-center">
                  <th>No</th>
                  <th>Name</th>
                  <th>Unit</th>
                  <th>Stock Qty</th>
                  <th>First SO Qty</th>
                  <th>Reject SO Qty</th>
                  <th>Update SO Qty</th>
                  <th>Difference Qty</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php $items = \App\Models\StockOpnameItem::get(['opname_id' => $opname->id]) ?>
                <?php $ro = ($opname->status != 'checked' ? 'readonly' : '') ?>
                <?php $no = 1 ?>
                <?php foreach ($items as $item) : ?>
                  <?php $product    = \App\Models\Product::getRow(['id' => $item->product_id]); ?>
                  <?php $restQty    = ($item->last_qty ?? $item->first_qty) - $item->quantity + $item->reject_qty ?>
                  <?php $isEqual    = ($restQty == 0) ?>
                  <?php $isMinus    = ($restQty < 0) ?>
                  <?php $isPlus     = ($restQty > 0) ?>
                  <?php $isChanged  = ($item->last_qty != null && $item->first_qty != $item->last_qty) ?>
                  <?php $color      = ($isChanged ? 'warning' : ($isEqual ? 'success' : ($isMinus ? 'danger' : ($isPlus ? 'primary' : '')))) ?>
                  <?php if ($opname->status == 'checked' && !$isMinus) : ?>
                    <?php continue ?>
                  <?php endif; ?>
                  <tr class="bg-gradient-<?= $color ?>">
                    <td>
                      <input name="item[id][]" type="hidden" value="<?= $product->id ?>">
                      <input name="item[code][]" type="hidden" value="<?= $product->code ?>">
                      <span class="float-right"><?= $no ?></span>
                    </td>
                    <td><?= "($product->code) " . $product->name ?></td>
                    <td><?= \App\Models\Unit::getRow(['id' => $product->unit])->code ?></td>
                    <td class="text-center"><?= formatNumber($item->quantity) ?></td>
                    <td class="text-center"><?= formatNumber($item->first_qty) ?></td>
                    <td class="text-center"><?= formatNumber($item->reject_qty) ?></td>
                    <?php if ($isMinus && $opname->status == 'checked') : ?>
                      <td><input type="number" name="item[last][]" class="form-control form-control-border form-control-sm" value="<?= filterDecimal($item->first_qty) ?>"></td>
                    <?php else : ?>
                      <td class="text-center"><?= ($item->last_qty != null ? formatNumber($item->last_qty) : '-') ?></td>
                    <?php endif; ?>
                    <td class="text-center"><?= round($restQty, 6) ?></td>
                    <td><span class="float-right"><?= formatNumber($item->price) ?></span></td>
                    <td><span class="float-right"><?= formatNumber($item->subtotal) ?></span></td>
                  </tr>
                  <?php $no++ ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <?php if ($opname->status == 'checked' && hasAccess('StockOpname.Confirm')) : ?>
    <button type="button" class="btn bg-gradient-primary commit-status status-confirm"><i class="fad fa-fw fa-check-circle"></i> <?= lang('App.confirm') ?></button>
  <?php elseif ($opname->status == 'confirmed' && hasAccess('StockOpname.Verify')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-verify"><i class="fad fa-fw fa-box-open-full"></i> <?= lang('App.verify') ?></button>
  <?php endif; ?>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    $('.commit-status').click(function() {
      let status = '';

      if (this.classList.contains('status-confirm')) {
        status = 'confirm';
      } else if (this.classList.contains('status-verify')) {
        status = 'verify';
      }

      $('#status').val(status);
    });

    initModalForm({
      form: '#form',
      submit: '.commit-status',
      url: base_url + '/inventory/stockopname/status/<?= $opname->id ?>'
    });
  });
</script>