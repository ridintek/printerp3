<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="date"><?= lang('App.date') ?></label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" placeholder="<?= lang('App.biller') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" placeholder="<?= lang('App.warehouse') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="created_by"><?= lang('App.createdby') ?></label>
                  <select id="created_by" name="created_by" class="select-user" data-placeholder="<?= lang('App.createdby') ?>" style="width:100%" placeholder="<?= lang('App.createdby') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="customer"><?= lang('App.customer') ?> *</label>
                  <div class="input-group">
                    <?php if (hasAccess('Customer.Add')) : ?>
                      <div class="input-group-prepend">
                        <a href="<?= base_url('humanresource/customer/add') ?>" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalDefault"><i class="fad fa-user-plus"></i></a>
                      </div>
                    <?php endif; ?>
                    <select id="customer" name="customer" class="select-customer" data-placeholder="<?= lang('App.customer') ?>" style="width:100%" placeholder="<?= lang('App.customer') ?>">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="duedate"><?= lang('App.duedate') ?></label>
                  <input type="datetime-local" id="duedate" name="duedate" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-4">
                <?php if (hasAccess('Sale.Discount')) : ?>
                  <div class="form-group">
                    <label for="discount"><?= lang('App.discount') ?></label>
                    <input id="discount" name="discount" class="form-control form-control-border form-control-sm currency">
                  </div>
                <?php endif; ?>
              </div>
              <div class="col-md-4">
                <?php if (hasAccess('Sale.RawMaterial')) : ?>
                  <div class="form-group">
                    <input type="checkbox" id="rawmaterial" name="rawmaterial">
                    <label for="rawmaterial"><?= lang('App.rawmaterial') ?></label>
                  </div>
                <?php endif; ?>
                <?php if (hasAccess('Sale.Approve')) : ?>
                  <div class="form-group">
                    <input type="checkbox" id="approved" name="approved" value="1">
                    <label for="approved"><?= lang('Status.approved') ?></label>
                  </div>
                <?php endif; ?>
              </div>
              <div class="col-md-4">
                <?php if (hasAccess('Sale.Payment')) : ?>
                  <div class="form-group">
                    <input type="checkbox" id="transfer" name="transfer" value="1">
                    <label for="transfer"><?= lang('App.transfer') ?></label>
                  </div>
                <?php endif; ?>
                <?php if (hasAccess('Sale.Draft')) : ?>
                  <div class="form-group">
                    <input type="checkbox" id="draft" name="draft" value="1">
                    <label for="draft"><?= lang('App.draft') ?></label>
                  </div>
                <?php endif; ?>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="cashier"><?= lang('App.cashier') ?></label>
                  <select id="cashier" name="cashier" class="select-user" data-placeholder="<?= lang('App.cashier') ?>" style="width:100%" placeholder="<?= lang('App.cashier') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <label for="voucher"><?= lang('App.voucher') ?></label>
                <select id="voucher" name="voucher[]" class="select-voucher" data-placeholder="<?= lang('App.voucher') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary"><?= lang('App.product') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <select id="product" class="select-product" data-placeholder="<?= lang('App.product') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <div class="table-responsive">
                  <table id="table-sale" class="table table-bordered table-hover table-sm">
                    <thead>
                      <tr>
                        <th><?= lang('App.name') ?></th>
                        <th><?= lang('App.option') ?></th>
                        <th><?= lang('App.subtotal') ?></th>
                        <th><i class="fad fa-trash"></i></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2"><span class="float-right"><?= lang('App.grandtotal') ?></span></td>
                        <td><span class="float-right sale-grandtotal">Rp 0</span></td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-success"><?= lang('App.misc') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input type="file" id="attachment" name="attachment" class="custom-file-input">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-center">
                <div class="form-group">
                  <img class="attachment-preview" src="<?= $sale->attachment ? base_url('attachment/' . $sale->attachment) : base_url('assets/app/images/picture.png') ?>" style="max-width:300px">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="editor"><?= lang('App.note') ?></label>
                  <div id="editor"></div>
                  <input type="hidden" name="note">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <a href="<?= base_url('sale/preview') ?>" class="btn bg-gradient-info" data-toggle="modal" data-target="#ModalDefault" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
    <i class="fad fa-fw fa-magnifying-glass"></i> <?= lang('App.preview') ?>
  </a>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>
<script type="module">
  import {
    Sale
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    erp.select2.product = {};
    erp.select2.user = {};
    erp.select2.operator = {};
    erp.select2.customer = {};

    erp.select2.product.type = ['combo', 'service'];

    if (erp.biller.id) {
      erp.select2.biller.id = [erp.biller.id];
    }

    if (<?= $saleJS->approved ?>) {
      $('#approved').iCheck('check');
    }

    if (<?= ($sale->status == 'draft' ? 1 : 0) ?>) {
      $('#draft').iCheck('check');
    }

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    editor.root.innerHTML = `<?= $sale->note ?>`;

    $('#attachment').change(function() {
      let src = '';

      if (this.files.length) {
        src = URL.createObjectURL(this.files[0]);
      } else {
        src = base_url + '/assets/app/images/picture.png';
      }

      $('.attachment-preview').prop('src', src);
    });

    $('#biller').change(function() {
      erp.select2.user.biller = [this.value];
    });

    $('#warehouse').change(function() {
      erp.select2.operator.warehouse = [this.value];
    });

    $('#draft').on('change', function() {
      if (this.checked) {
        $('#approve, #transfer').iCheck('uncheck').prop('disabled', true);
      } else {
        $('#approve, #transfer').prop('disabled', false);
      }
    });

    $('#rawmaterial').on('change', function() {
      if (this.checked) {
        erp.select2.product.type.push('standard');
      } else {
        erp.select2.product.type.pop();
      }
    });

    $('#product').change(function() {
      if (!this.value) return false;

      let customerId = $('#customer').val();
      let warehouse = $('#warehouse').val();

      if (!customerId) {
        toastr.error('Customer is required.');

        $(this).val('').trigger('change');

        return false;
      }

      if (!warehouse) {
        toastr.error('Warehouse is required.');

        $(this).val('').trigger('change');

        return false;
      }

      $.ajax({
        data: {
          active: 1,
          id: this.value,
          customer: customerId,
          warehouse: warehouse
        },
        success: (data) => {
          let item = data.data[0];

          Sale.table('#table-sale').addItem({
            id: item.id,
            code: item.code,
            name: item.name,
            category: item.category,
            length: 1,
            prices: item.prices,
            quantity: 1,
            ranges: item.ranges,
            spec: '',
            type: item.type,
            width: 1
          }, true);

          initControls();

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    $('#date').val('<?= dateTimeJS($sale->date, false) ?>');
    $('#duedate').val('<?= dateTimeJS($sale->due_date ?? null, false) ?>');
    $('#discount').val('<?= formatCurrency($sale->discount) ?>');

    preSelect2('biller', '#biller', '<?= $sale->biller_id ?>').catch(err => console.warn(err));
    preSelect2('warehouse', '#warehouse', '<?= $sale->warehouse_id ?>').catch(err => console.warn(err));
    preSelect2('user', '#cashier', '<?= $saleJS->cashier_by ?>').catch(err => console.warn(err));
    preSelect2('customer', '#customer', '<?= $sale->customer_id ?>').catch(err => console.warn(err));
    preSelect2('user', '#created_by', '<?= $sale->created_by ?>').catch(err => console.warn(err));
    preSelect2('voucher', '#voucher', JSON.parse('<?= json_encode($saleJS->vouchers ?? '[]') ?>')).catch(err => console.warn(err));

    let items = JSON.parse(`<?= json_encode($items) ?>`);

    for (let item of items) {
      Sale.table('#table-sale').addItem({
        id: item.id,
        code: item.code,
        name: item.name,
        category: item.category,
        complete: item.complete,
        completed_at: item.completed_at,
        finished_qty: item.finished_qty,
        length: item.length,
        operator: item.operator,
        prices: item.prices,
        quantity: item.quantity,
        ranges: item.ranges,
        spec: item.spec,
        status: item.status,
        type: item.type,
        width: item.width
      }, true);

      initControls();
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/sale/edit/<?= $sale->id ?>'
    });
  });
</script>