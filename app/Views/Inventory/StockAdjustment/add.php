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
              <?php if (hasAccess('StockAdjustment.Edit')) : ?>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="date"><?= lang('App.date') ?></label>
                    <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                  </div>
                </div>
              <?php endif; ?>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="mode"><?= lang('App.mode') ?> *</label>
                  <select class="select" name="mode" data-placeholder="<?= lang('App.mode') ?>" style="width:100%">
                    <option value="overwrite"><?= lang('App.overwrite') ?></option>
                    <option value="formula"><?= lang('App.formula') ?></option>
                  </select>
                </div>
              </div>
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
                <table id="table-stockadjustment" class="table">
                  <thead>
                    <tr>
                      <th><?= lang('App.name') ?></th>
                      <th><?= lang('App.quantity') ?></th>
                      <th><?= lang('App.currentstock') ?></th>
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
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>
<script type="module">
  import {
    StockAdjustment
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    erp.select2.product.type = ['service', 'standard'];

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#product').change(function() {
      if (!this.value) return false;

      let warehouse = $('#warehouse').val();

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

          StockAdjustment.table('#table-stockadjustment').addItem({
            id: item.id,
            code: item.code,
            name: item.name,
            quantity: 0,
            current_qty: item.quantity
          });

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/stockadjustment/add'
    });
  });
</script>