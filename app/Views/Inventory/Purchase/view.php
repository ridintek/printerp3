<?php $biller = \App\Models\Biller::getRow(['id' => $purchase->biller_id]); ?>
<?php $supplier = \App\Models\Supplier::getRow(['id' => $purchase->supplier_id]); ?>
<?php $warehouse = \App\Models\Warehouse::getRow(['id' => $purchase->warehouse_id]); ?>
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
                <td><?= $purchase->id ?></td>
              </tr>
              <tr>
                <td><?= lang('App.date') ?></td>
                <td><?= formatDateTime($purchase->date) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.biller') ?></td>
                <td><?= ($biller ? $biller->name : '-') ?></td>
              </tr>
              <tr>
                <td><?= lang('App.warehouse') ?></td>
                <td><?= ($warehouse ? $warehouse->name : '-') ?></td>
              </tr>
              <tr>
                <td><?= lang('App.supplier') ?></td>
                <td><?= $supplier->name . ($supplier->company ? " ({$supplier->company})" : '') ?></td>
              </tr>
              <tr>
                <td><?= lang('App.status') ?></td>
                <td><?= renderStatus($purchase->status) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.paymentstatus') ?></td>
                <td><?= renderStatus($purchase->payment_status) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.receiveddate') ?></td>
                <td><?= ($purchase->received_date ? formatDateTime($purchase->received_date) : '-') ?></td>
              </tr>
              <tr>
                <td><?= lang('App.receivedvalue') ?></td>
                <td><?= formatCurrency($purchase->received_value) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.note') ?></td>
                <td><?= $purchase->note ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdat') ?></td>
                <td><?= formatDateTime($purchase->created_at) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdby') ?></td>
                <td><?= \App\Models\User::getRow(['id' => $purchase->created_by])->fullname ?></td>
              </tr>
              <?php if ($purchase->updated_at) : ?>
                <tr>
                  <td><?= lang('App.updatedat') ?></td>
                  <td><?= formatDateTime($purchase->updated_at) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($purchase->updated_by) : ?>
                <tr>
                  <td><?= lang('App.updatedby') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $purchase->created_by])->fullname ?></td>
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
                  <th>Spec</th>
                  <th>Cost</th>
                  <th>Purchased Qty</th>
                  <th>Received Qty</th>
                  <th>Rest Qty</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php $items = \App\Models\Stock::get(['purchase_id' => $purchase->id]) ?>
                <?php $ro = ($purchase->status != 'checked' ? 'readonly' : '') ?>
                <?php $no = 1 ?>
                <?php foreach ($items as $item) : ?>
                  <?php $product  = \App\Models\Product::getRow(['id' => $item->product_id]); ?>
                  <?php $restQty  = ($item->purchased_qty - $item->quantity); ?>
                  <tr>
                    <td>
                      <input name="item[id][]" type="hidden" value="<?= $product->id ?>">
                      <input name="item[code][]" type="hidden" value="<?= $product->code ?>">
                      <span class="float-right"><?= $no ?></span>
                    </td>
                    <td><?= "($product->code) " . $product->name ?></td>
                    <td><?= \App\Models\Unit::getRow(['id' => $product->unit])->code ?></td>
                    <?php if ($restQty && inStatus($purchase->status, ['ordered', 'received_partial'])) : ?>
                      <td><input name="item[spec][]" class="form-control form-control-border form-control-sm" value="<?= $item->spec ?>"></td>
                    <?php else : ?>
                      <td class="text-center"><?= $item->spec ?></td>
                    <?php endif; ?>
                    <td class="text-center"><?= formatNumber($item->cost) ?></td>
                    <td class="text-center"><?= formatNumber($item->purchased_qty) ?></td>
                    <td class="text-center"><?= formatNumber($item->quantity) ?></td>
                    <?php if ($restQty && inStatus($purchase->status, ['ordered', 'received_partial'])) : ?>
                      <td><input type="number" name="item[rest][]" class="form-control form-control-border form-control-sm" value="<?= $restQty ?>"></td>
                    <?php else : ?>
                      <td class="text-center"><?= $restQty ?></td>
                    <?php endif; ?>
                    <td><span class="float-right"><?= formatNumber($item->cost * $item->purchased_qty) ?></span></td>
                  </tr>
                  <?php $no++ ?>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php if ($purchase->status == 'checked') : ?>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="attachment"><?= lang('App.attachment') ?></label>
                    <div class="custom-file">
                      <input type="file" id="attachment" name="attachment" class="custom-file-input">
                      <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <?php if ($purchase->status == 'need_approval' && hasAccess('ProductPurchase.Approve')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-approved"><i class="fad fa-fw fa-check-circle"></i> <?= lang('App.approve') ?></button>
  <?php endif; ?>
  <?php if ($purchase->status != 'need_approval' && hasAccess('ProductPurchase.Disapprove')) : ?>
    <button type="button" class="btn bg-gradient-warning commit-status status-need_approval"><i class="fad fa-fw fa-undo"></i> <?= lang('Status.need_approval') ?></button>
  <?php endif; ?>
  <?php if ($purchase->status == 'approved' && hasAccess('ProductPurchase.Order')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-ordered"><i class="fad fa-fw fa-box-full"></i> <?= lang('App.order') ?></button>
  <?php endif; ?>
  <?php if (inStatus($purchase->status, ['ordered', 'received_partial']) && hasAccess('ProductPurchase.Receive')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-received"><i class="fad fa-fw fa-box-check"></i> <?= lang('App.receive') ?></button>
  <?php endif; ?>
  <?php if ($purchase->payment_status == 'need_approval' && hasAccess('ProductPurchase.ApprovePayment')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-approve_payment"><i class="fad fa-fw fa-box-check"></i> <?= lang('App.approvepayment') ?></button>
  <?php endif; ?>
  <?php if ($purchase->payment_status != 'need_approval' && hasAccess('ProductPurchase.DisapprovePayment')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-approve_payment"><i class="fad fa-fw fa-box-check"></i> <?= lang('App.approvepayment') ?></button>
  <?php endif; ?>
  <?php if ($purchase->payment_status == 'approved' && hasAccess('ProductPurchase.CommitPayment')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-commit_payment"><i class="fad fa-fw fa-box-check"></i> <?= lang('App.commitpayment') ?></button>
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

      if (this.classList.contains('status-need_approval')) {
        status = 'need_approval';
      } else if (this.classList.contains('status-approved')) {
        status = 'approved';
      } else if (this.classList.contains('status-approve_payment')) {
        status = 'approve_payment';
      } else if (this.classList.contains('status-commit_payment')) {
        status = 'commit_payment';
      } else if (this.classList.contains('status-ordered')) {
        status = 'ordered';
      } else if (this.classList.contains('status-received')) {
        status = 'received';
      }

      $('#status').val(status);
    });

    initModalForm({
      form: '#form',
      submit: '.commit-status',
      url: base_url + '/inventory/purchase/status/<?= $purchase->id ?>'
    });
  });
</script>