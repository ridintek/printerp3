<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title . " ({$product->name})" ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <table id="ModalTable" class="table table-head-fixed table-hover table-striped dataTable">
    <thead>
      <tr>
        <th class="col-sm-1">Stock ID</th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.reference') ?></th>
        <th><?= lang('App.warehouse') ?></th>
        <th><?= lang('App.category') ?></th>
        <th><?= lang('App.createdby') ?></th>
        <th><?= lang('App.increase'); ?></th>
        <th><?= lang('App.decrease'); ?></th>
        <th><?= lang('App.balance'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
      $totalBalance = filterNumber($beginningQty);
      $totalDecrease = 0.0;
      $totalIncrease = 0.0;
      $oldBalance = 0.0;
      $oldDecrease = 0.0;
      $oldIncrease = 0.0;
      $oldDate = '';
      $iOldDate = 0.0;
      ?>

      <tr>
        <td>-</td>
        <td><strong><?= $start_date . ' 00:00:00'; ?></strong></td>
        <td colspan="6" class="text-center"><strong>BEGINNING</strong></td>
        <td class="text-right"><strong><?= formatQuantity($beginningQty); ?></strong></td>
      </tr>

      <?php if (!empty($rows)) {
        foreach ($rows as $row) {
          if ($row->status != 'sent' && $row->status != 'received') continue;

          $iDate = strtotime($row->date);
          $quantity = filterNumber($row->quantity);

          if ($iOldDate && (date('m', $iDate) != date('m', $iOldDate))) { // Monthly Summary 
      ?>
            <tr>
              <td>-</td>
              <td><strong><?= $oldDate . ' 23:59:59'; ?></strong></td>
              <td class="text-center" colspan="4"><strong>SUMMARY <?= strtoupper(getMonthName(date('m', $iOldDate))); ?></strong></td>
              <td class="text-right"><strong><?= formatQuantity($oldIncrease); ?></strong></td>
              <td class="text-right"><strong><?= formatQuantity($oldDecrease); ?></strong></td>
              <td class="text-right"><strong><?= formatQuantity($oldBalance); ?></strong></td>
            </tr>
          <?php
            $oldBalance = 0.0;
            $oldDecrease = 0.0;
            $oldIncrease = 0.0;
          }
          ?>
          <tr>
            <td><?= $row->id; ?></td>
            <td><?= $row->date; ?></td>
            <?php
            $reference = '';

            if ($row->adjustment_id != NULL) {
              if ($adjustment = \App\Models\StockAdjustment::getRow(['id' => $row->adjustment_id])) {
                $reference = $adjustment->reference;
              } else {
                $reference = '[ DELETED ]';
              }
            } else if ($row->internal_use_id != NULL) {
              if ($iuse = \App\Models\InternalUse::getRow(['id' => $row->internal_use_id])) {
                $reference = $iuse->reference;
              } else {
                $reference = ' [ DELETED ]';
              }
            } else if ($row->purchase_id != NULL) {
              if ($purchase = \App\Models\ProductPurchase::getRow(['id' => $row->purchase_id])) {
                $reference = $purchase->reference;
              } else {
                $reference = ' [ DELETED ]';
              }
            } else if ($row->sale_id != NULL) {
              if ($sale = \App\Models\Sale::getRow(['id' => $row->sale_id])) {
                $reference = $sale->reference;
              } else {
                $reference = '[ DELETED ]';
              }
            } else if ($row->transfer_id != NULL) {
              if ($transfer = \App\Models\ProductTransfer::getRow(['id' => $row->transfer_id])) {
                $reference = $transfer->reference;
              } else {
                $reference = '[ DELETED ]';
              }
            }
            ?>
            <td><?= $reference; ?></td>
            <td><?= $row->warehouse_name; ?></td>
            <td><?= $row->category_code; ?></td>
            <?php
            if ($row->created_by) {
              $user = \App\Models\User::getRow(['id' => $row->created_by]);
              $created_by = ($user ? $user->fullname : '');
            } else {
              $created_by = '-';
            }
            ?>
            <td><?= $created_by; ?></td>
            <?php
            $dec = 0;
            $inc = 0;

            if ($row->status == 'received') {
              $inc = $quantity;
              $totalIncrease += $inc;
            } else if ($row->status == 'sent') {
              $dec = $quantity;
              $totalDecrease += $dec;
            }
            ?>
            <td class="text-right" data-desc="increase"><?= ($inc ? formatQuantity($inc) : ''); ?></td>
            <td class="text-right" data-desc="decrease"><?= ($dec ? formatQuantity($dec) : ''); ?></td>
            <?php

            if ($row->status == 'received') {
              $totalBalance += $quantity;
            } else if ($row->status == 'sent') {
              $totalBalance -= $quantity;
            }

            $iOldDate = $iDate;
            $oldDate = date('Y-m-d', $iOldDate);
            $oldBalance = $totalBalance;
            $oldDecrease += $dec;
            $oldIncrease += $inc;
            ?>
            <td class="text-right" data-desc="balance"><?= formatQuantity($totalBalance); ?></td>
          </tr>
        <?php } // foreach ($rows as $row)
        ?>
        <tr>
          <td>-</td>
          <td><strong><?= ($end_date ? $end_date . date(' H:i:s') : ''); ?></strong></td>
          <td class="text-center" colspan="4"><strong>SUMMARY <?= strtoupper(getMonthName(date('m', $iOldDate))); ?></strong></td>
          <td class="text-right"><strong><?= formatQuantity($oldIncrease); ?></strong></td>
          <td class="text-right"><strong><?= formatQuantity($oldDecrease); ?></strong></td>
          <td class="text-right"><strong><?= formatQuantity($oldBalance); ?></strong></td>
        </tr>
      <?php } else { // ! empty($rows)
      ?>
        <tr>
          <td colspan="9" class="dataTables_empty">No Data</td>
        </tr>
      <?php } // ! empty($rows)
      ?>
    </tbody>
    <tfoot>
      <tr>
        <th class="col-sm-1">Stock ID</th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.reference') ?></th>
        <th><?= lang('App.warehouse') ?></th>
        <th><?= lang('App.category') ?></th>
        <th><?= lang('App.createdby') ?></th>
        <th><?= lang('App.increase'); ?></th>
        <th><?= lang('App.decrease'); ?></th>
        <th><?= lang('App.balance'); ?></th>
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
  });
</script>