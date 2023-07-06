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
              <?php if (hasAccess('ProductPurchase.Edit')) : ?>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="date"><?= lang('App.date') ?></label>
                    <input id="date" name="date" type="datetime-local" class="form-control form-control-border form-control-sm">
                  </div>
                </div>
              <?php endif; ?>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="created_by"><?= lang('App.createdby') ?></label>
                  <select id="created_by" name="created_by" class="select-user" style="width:100%" data-placeholder="<?= lang('App.createdby') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="supplier"><?= lang('App.supplier') ?> *</label>
                  <select id="supplier" name="supplier" class="select-supplier" style="width:100%" data-placeholder="<?= lang('App.supplier') ?>">
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="category"><?= lang('App.category') ?></label>
                  <select id="category" name="category" class="select-expense-category" style="width:100%" data-placeholder="<?= lang('App.category') ?>">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header bg-gradient-primary">
                    <?= lang('App.warehouse') ?>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="biller"><?= lang('App.biller') ?> *</label>
                          <select id="biller" name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                          <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
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
                <table id="table-productpurchase" class="table">
                  <thead>
                    <tr>
                      <th class="col-md-3"><?= lang('App.name') ?></th>
                      <th class="col-md-6"><?= lang('App.option') ?></th>
                      <th class="col-md-1"><?= lang('App.unit') ?></th>
                      <th class="col-md-1"><?= lang('App.currentstock') ?></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-success">
            <?= lang('App.misc') ?>
          </div>
          <div class="card-body">
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
    ProductPurchase
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    erp.select2.product = {
      type: ['standard']
    };
    erp.select2.biller = {};
    erp.select2.warehouse = {};

    if (!hasAccess('ProductPurchase.Edit')) {
      erp.select2.user.id = [erp.user.id];
    }

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#product').change(function() {
      if (!this.value) return false;

      let biller = $('#biller').val();
      let supplier = $('#supplier').val();
      let warehouse = $('#warehouse').val();

      if (!biller) {
        toastr.error('Biller is required.');

        $(this).val('').trigger('change');

        return false;
      }

      if (!supplier) {
        toastr.error('Supplier is required.');

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
          id: this.value,
          warehouse: warehouse
        },
        success: (data) => {
          let item = data.data[0];

          ProductPurchase.table('#table-productpurchase').addItem({
            id: item.id,
            code: item.code,
            name: item.name,
            unit: item.unit,
            cost: item.cost,
            quantity: 0,
            spec: '',
            current_qty: item.quantity
          });

          initControls();

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    preSelect2('biller', '#biller', erp.biller.id).catch(err => console.warn(err));
    preSelect2('user', '#created_by', erp.user.id).catch(err => console.warn(err));
    preSelect2('warehouse', '#warehouse', erp.warehouse.id).catch(err => console.warn(err));

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/purchase/add'
    });
  });
</script>