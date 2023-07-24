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
                  <label for="type"><?= lang('App.type') ?> *</label>
                  <select id="type" name="type" class="select" data-placeholder="<?= lang('App.type') ?>" style="width:100%">
                    <option value="standard">Standard</option>
                    <option value="combo">Combo</option>
                    <option value="service">Service</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="code"><?= lang('App.code') ?> *</label>
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.code') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?> *</label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.name') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="cost"><?= lang('App.cost') ?> *</label>
                  <input id="cost" name="cost" class="form-control form-control-border form-control-sm currency" placeholder="<?= lang('App.cost') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="price"><?= lang('App.price') ?></label>
                  <input id="price" name="price" class="form-control form-control-border form-control-sm currency" placeholder="<?= lang('App.price') ?>">
                </div>
              </div>
              <div class="col-md-4 standard">
                <div class="form-group">
                  <label for="minorder">Min. Order</label>
                  <input id="minorder" name="minorder" class="form-control form-control-border form-control-sm" placeholder="Min. Order">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="warehouses"><?= lang('App.warehouse') ?></label>
                  <input id="warehouses" name="warehouses" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.warehouse') ?>">
                </div>
              </div>
              <div class="col-md-4 standard">
                <div class="form-group">
                  <label for="priority">Priority</label>
                  <select id="priority" name="priority" class="select" data-placeholder="Priority" style="width:100%">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 standard">
                <div class="form-group">
                  <label for="serial">Serial Number</label>
                  <input id="serial" name="serial" class="form-control form-control-border form-control-sm" placeholder="Serial Number">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <input type="checkbox" name="active" value="1" checked>
                  <label for="active"><?= lang('App.active') ?></label>
                </div>
              </div>
              <div class="col-md-4 standard">
                <div class="form-group">
                  <input type="checkbox" name="autocomplete" value="1" checked>
                  <label for="autocomplete">Auto-Complete Production</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-gradient-info">Category</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="category">Category</label>
                  <select id="category" name="category" class="select-product-category" data-placeholder="Category" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="subcategory">Sub-Category</label>
                  <select id="subcategory" name="subcategory" class="select-product-subcategory" data-placeholder="Category" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-gradient-info">Unit</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="unit">Unit</label>
                  <select id="unit" name="unit" class="select-product-unit" data-placeholder="Unit" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 combo" style="display:none">
        <div class="card">
          <div class="card-header bg-gradient-info">Combo Items</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="comboitem">Combo Item</label>
                  <select id="comboitem" class="select-product" data-placeholder="Item" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <table id="comboitemlist" class="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Quantity</th>
                      <th><i class="fad fa-trash"></i></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Internal Use Type</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="iuse_type">Type</label>
                  <select id="iuse_type" name="iuse_type" class="select-allow-clear" data-placeholder="Unit" style="width:100%">
                    <option value="consumable">Consumable</option>
                    <option value="sparepart">Sparepart</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Purchase</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="purchasedate">Purchase Date</label>
                  <input id="purchasedate" name="purchasedate" class="form-control form-control-border form-control-sm" placeholder="Purchase Date" type="date">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="purchasesource">Purchase Source</label>
                  <select id="purchasesource" name="purchasesource" class="select" data-placeholder="Purchase Source" style="width:100%">
                    <option value="import">Import</option>
                    <option value="local">Local</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Supplier</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="supplier">Supplier</label>
                  <select id="supplier" name="supplier" class="select-supplier" data-placeholder="Supplier" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Mark-On Price</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="markon">Mark-On (%)</label>
                  <input name="markon" class="form-control form-control-border form-control-sm" min="0" type="number">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="markon_price">Mark-On Price</label>
                  <input name="markon_price" class="form-control form-control-border form-control-sm currency" type="text">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Stock Opname</div>
          <div class="card-body">
            <?php foreach (\App\Models\Warehouse::get(['active' => 1]) as $warehouse) : ?>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>PIC <?= $warehouse->name ?></label>
                    <select name="so[pic][]" class="select-user" data-placeholder="PIC" style="width:100%"></select>
                    <input name="so[warehouse][]" type="hidden" value="<?= $warehouse->id ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Sequence Cycle</label>
                    <input name="so[cycle][]" class="form-control form-control-border form-control-sm" min="0" type="number">
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-md-6 standard">
        <div class="card">
          <div class="card-header bg-gradient-info">Warehouse Stock</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Warehouse</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach (\App\Models\Warehouse::get(['active' => 1]) as $warehouse) : ?>
                      <tr>
                        <td><?= $warehouse->name ?></td>
                        <td>0</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
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

  $(document).ready(function() {
    erp.select2.product = {
      category: {},
      subcategory: {},
      type: ['standard']
    };

    $('#category').change(function() {
      $('#subcategory').val('').trigger('change');

      erp.select2.product.subcategory.parent = [this.value];
    });

    $('#comboitem').change(function() {
      if (!this.value) return false;

      let tbody = $('#comboitemlist').find('tbody');

      $.ajax({
        data: {
          active: 1,
          id: this.value,
        },
        success: (response) => {
          let item = response.data[0];

          console.log(response);
          tbody.prepend(`
            <tr>
              <td>${item.name}
                <input name="combo[item][]" type="hidden" value="${item.id}">
              </td>
              <td><input name="combo[quantity][]" class="form-control form-control-sm" value="0"></td>
              <td><a href="#" class="table-row-delete"><i class="fad fa-times"></i></a></td>
            </tr>
          `);
          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      })
    })

    $('#type').change(function() {
      if (this.value == 'standard') {
        $('.combo').slideUp();
        $('.service').slideUp();
        $('.standard').slideDown();
      }

      if (this.value == 'combo') {
        $('.combo').slideDown();
        $('.service').slideUp();
        $('.standard').slideUp();
      }

      if (this.value == 'service') {
        $('.combo').slideUp();
        $('.service').slideDown();
        $('.standard').slideUp();
      }
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/product/add'
    });
  });
</script>