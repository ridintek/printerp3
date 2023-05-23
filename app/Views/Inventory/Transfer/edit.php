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
              <div class="col-md-6">
                <div class="form-group">
                  <label for="date"><?= lang('App.date') ?></label>
                  <input id="date" name="date" type="datetime-local" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="created_by"><?= lang('App.createdby') ?></label>
                  <select id="created_by" name="created_by" class="select-user" style="width:100%" data-placeholder="<?= lang('App.createdby') ?>">
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
                          <label for="warehousefrom"><?= lang('App.from') ?> *</label>
                          <select id="warehousefrom" name="warehousefrom" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="warehouseto"><?= lang('App.to') ?> *</label>
                          <select id="warehouseto" name="warehouseto" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
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
                <table id="table-producttransfer" class="table">
                  <thead>
                    <tr>
                      <th class="col-md-3"><?= lang('App.name') ?></th>
                      <th class="col-md-6"><?= lang('App.option') ?></th>
                      <th class="col-md-1"><?= lang('App.unit') ?></th>
                      <th class="col-md-1"><?= lang('App.sourcestock') ?></th>
                      <th class="col-md-1"><?= lang('App.destinationstock') ?></th>
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
    ProductTransfer
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    erp.select2.product = {};
    erp.select2.product.type = ['standard'];

    let items = JSON.parse('<?= json_encode($items) ?>');

    if (items) {
      for (let item of items) {
        ProductTransfer.table('#table-producttransfer').mode('edit').addItem(item);
      }
    } else {
      console.warn('Items are empty.');
    }

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#product').change(async function() {
      if (!this.value) return false;

      let warehouseFrom = $('#warehousefrom').val();
      let warehouseTo = $('#warehouseto').val();

      if (!warehouseFrom) {
        toastr.error('Warehouse From is required.');

        $(this).val('').trigger('change');

        return false;
      }

      if (!warehouseTo) {
        toastr.error('Warehouse To is required.');

        $(this).val('').trigger('change');

        return false;
      }

      $.ajax({
        data: {
          id: this.value,
          warehouse: warehouseTo
        },
        success: (data) => {
          let item = data.data[0];
          let itemQtyTo = parseFloat(item.quantity);

          $.ajax({
            data: {
              id: this.value,
              warehouse: warehouseFrom
            },
            success: (data) => {
              let item = data.data[0];

              ProductTransfer.table('#table-producttransfer').addItem({
                id: item.id,
                code: item.code,
                name: item.name,
                unit: item.unit,
                markon_price: item.markon_price,
                quantity: 0,
                spec: item.spec,
                current_qty: item.quantity,
                destination_qty: itemQtyTo,
                received_qty: 0,
                rest_qty: 0
              });

              initControls();

              $(this).val('').trigger('change');
            },
            url: base_url + '/api/v1/product'
          });
        },
        url: base_url + '/api/v1/product'
      });
    });

    $('#warehousefrom').change(function() {
      erp.select2.product.warehouse = this.value;
    });

    $('#warehouseto').change(function() {
      $.ajax({
        data: {
          active: 1,
          machine: 1,
          warehouse: this.value
        },
        success: (response) => {
          erp.machine = response.data;
        },
        url: base_url + '/api/v1/product'
      });
    });

    editor.root.innerHTML = `<?= $transfer->note ?>`;
    $('#date').val('<?= dateTimeJS($transfer->date) ?>').trigger('change');
    preSelect2('user', '#created_by', '<?= $transfer->created_by ?>').catch(err => console.warn(err));
    preSelect2('warehouse', '#warehousefrom', '<?= $transfer->warehouse_id_from ?>').catch(err => console.warn(err));
    preSelect2('warehouse', '#warehouseto', '<?= $transfer->warehouse_id_to ?>').catch(err => console.warn(err));

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/transfer/edit/<?= $transfer->id ?>'
    });
  });
</script>