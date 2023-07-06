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
              <div class="col-md-4 standard">
                <div class="form-group">
                  <label for="purchasedate">Purchase Date</label>
                  <input id="purchasedate" name="purchasedate" class="form-control form-control-border form-control-sm" placeholder="Purchase Date">
                </div>
              </div>
              <div class="col-md-4 standard">
                <div class="form-group">
                  <label for="purchasesource">Purchase Source</label>
                  <select id="purchasesource" name="purchasesource" class="select" data-placeholder="Purchase Source" style="width:100%">
                    <option value="import">Import</option>
                    <option value="local">Local</option>
                  </select>
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
              <div class="col-md-6"></div>
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
      subcategory: {}
    };

    $('#category').change(function() {
      erp.select2.product.subcategory.parent = [this.value];
    });

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