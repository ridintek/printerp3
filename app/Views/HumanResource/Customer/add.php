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
                  <label for="group"><?= lang('App.group') ?> *</label>
                  <select class="select" name="group" data-placeholder="<?= lang('App.group') ?>" style="width:100%">
                    <?php foreach (\App\Models\CustomerGroup::get() as $group) : ?>
                      <?php if (strcasecmp($group->name, 'Privilege') == 0 && !hasAccess('Customer.Privilege')) : ?>
                        <?php continue; ?>
                      <?php endif; ?>
                      <?php if (strcasecmp($group->name, 'TOP') == 0 && !hasAccess('Customer.TOP')) : ?>
                        <?php continue; ?>
                      <?php endif; ?>
                      <option value="<?= $group->id ?>"><?= $group->name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <?php if (hasAccess('Customer.PriceGroup')) : ?>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="pricegroup"><?= lang('App.pricegroup') ?></label>
                    <select class="select-allow-clear" name="pricegroup" data-placeholder="<?= lang('App.pricegroup') ?>" style="width:100%">
                      <option value=""></option>
                      <?php foreach (\App\Models\PriceGroup::get() as $group) : ?>
                        <option value="<?= $group->id ?>"><?= $group->name ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?> *</label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.name') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.company') ?></label>
                  <input id="company" name="company" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.company') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.phone') ?> *</label>
                  <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.phone') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.email') ?></label>
                  <input id="email" name="email" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.email') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.address') ?></label>
                  <input id="address" name="address" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.address') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.city') ?></label>
                  <input id="city" name="city" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.city') ?>">
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
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/customer/add'
    });
  });
</script>