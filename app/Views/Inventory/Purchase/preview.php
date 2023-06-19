<?php $biller = \App\Models\Biller::getRow(['id' => $purchase->biller_id]); ?>
<?php $creator = \App\Models\User::getRow(['id' => $purchase->created_by]) ?>
<?php $supplier = \App\Models\Supplier::getRow(['id' => $purchase->supplier_id]); ?>
<?php $warehouse = \App\Models\Warehouse::getRow(['id' => $purchase->warehouse_id]); ?>
<?php $grandTotal = 0 ?>
<style>
  @media print {
    .watermark {
      z-index: 1;
    }
  }

  .watermark {
    left: 10%;
    opacity: 0.1;
    position: absolute;
    width: 80%;
  }
</style>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12">
      <h2 class="page-header">
        <?= lang('App.invoice') ?>
      </h2>
    </div>
  </div>
  <div class="row pb-2">
    <div class="col-md-8">
      <div class="row">
        <div class="col-md-4 text-bold"><?= lang('App.invoice') ?></div>
        <div class="col-md-8">: <?= $purchase->reference ?></div>
      </div>
      <div class="row">
        <div class="col-md-4 text-bold"><?= lang('App.date') ?></div>
        <div class="col-md-8">: <?= formatDateTime($purchase->date) ?></div>
      </div>
      <div class="row">
        <div class="col-md-4 text-bold"><?= lang('App.status') ?></div>
        <div class="col-md-8">: <?= lang('Status.' . $purchase->status) ?></div>
      </div>
      <div class="row">
        <div class="col-md-4 text-bold"><?= lang('App.paymentstatus') ?></div>
        <div class="col-md-8">: <?= lang('Status.' . $purchase->payment_status) ?></div>
      </div>
    </div>
  </div>
  <div class="row pb-5">
    <div class="col-md-6">
      <span class="text-bold"><?= lang('App.from') ?>:</span>
      <address>
        <div class="font-italic text-bold text-decoration-underline"><?= $biller->name ?></div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.address') ?></div>
          <div class="col-md-10">: <?= $biller->address ?></div>
        </div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.phone') ?></div>
          <div class="col-md-10">: <?= $biller->phone ?></div>
        </div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.email') ?></div>
          <div class="col-md-10">: <?= $biller->email ?></div>
        </div>
      </address>
    </div>
    <div class="col-md-6">
      <span class="text-bold"><?= lang('App.to') ?>:</span>
      <address>
        <div class="font-italic text-bold text-decoration-underline">
          <?= $supplier->name . ($supplier->company ? " ({$supplier->company})" : '') ?>
        </div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.address') ?></div>
          <div class="col-md-10">: <?= $supplier->address ?></div>
        </div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.phone') ?></div>
          <div class="col-md-10">: <?= $supplier->phone ?></div>
        </div>
        <div class="row">
          <div class="col-md-2"><?= lang('App.email') ?></div>
          <div class="col-md-10">: <?= $supplier->email ?></div>
        </div>
      </address>
    </div>
  </div>
  <div class="row pb-2 text-center">
    <div class="col-md-12">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="col-md-4"><?= lang('App.pic') ?></th>
            <th class="col-md-4"><?= lang('App.note') ?></th>
            <th class="col-md-4"><?= lang('App.paymentdue') ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?= $creator->fullname ?></td>
            <td><?= htmlRemove($purchase->note) ?></td>
            <td><?= ($purchase->due_date ? formatDate($purchase->due_date) : '-') ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <img class="watermark" src="<?= base_url('assets/app/images/logo-lucretia.png') ?>">
    <div class="col-md-12">
      <table class="table table-bordered table-striped text-center">
        <thead>
          <tr>
            <th><?= lang('App.product') ?></th>
            <th><?= lang('App.spec') ?></th>
            <th><?= lang('App.quantity') ?></th>
            <th><?= lang('App.received') ?></th>
            <th><?= lang('App.cost') ?></th>
            <th><?= lang('App.subtotal') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item) : ?>
            <?php $total = $item->cost * $item->purchased_qty ?>
            <?php $grandTotal += $total ?>
            <tr>
              <td><span class="float-left"><?= "({$item->product_code}) $item->product_name" ?></span></td>
              <td><?= $item->spec ?></td>
              <td><?= formatNumber($item->purchased_qty) ?></td>
              <td><?= formatNumber($item->quantity) ?></td>
              <td><span class="float-right"><?= formatNumber($item->cost) ?></span></td>
              <td><span class="float-right"><?= formatNumber($total) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row pb-5">
    <div class="col-md-8">
    </div>
    <div class="col-md-4">
      <div class="table-responsive">
        <table class="table table-hover table-sm table-striped">
          <tr>
            <th style="width:50%"><?= lang('App.discount') ?>:</th>
            <td><span class="float-right"><?= formatCurrency($purchase->discount) ?></span></td>
          </tr>
          <tr>
            <th><?= lang('App.grandtotal') ?>:</th>
            <td><span class="float-right"><?= formatCurrency($grandTotal) ?></span></td>
          </tr>
          <tr>
            <th><?= lang('App.paid') ?>:</th>
            <td><span class="float-right"><?= formatCurrency($purchase->paid) ?></span></td>
          </tr>
          <tr>
            <th><?= lang('App.debt') ?>:</th>
            <td><span class="float-right"><?= formatCurrency($purchase->balance) ?></span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="row pb-5 text-center">
    <div class="col-md-6">
      <?= lang('App.procurement') ?>
    </div>
    <div class="col-md-6">
      <?= lang('App.supplier') ?>
    </div>
  </div>
  <div class="row text-center pb-4">
    <div class="col-md-6">..............................</div>
    <div class="col-md-6">..............................</div>
  </div>
</div>