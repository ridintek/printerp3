<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?> (<?= $warehouse->name ?>)</h5>
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
              <div class="col-md-12">
                <table class="table table-head-fixed table-hover table-sm table-striped">
                  <thead>
                    <tr>
                      <th><?= lang('App.group') ?></th>
                      <th><?= lang('App.pic') ?></th>
                      <th><?= lang('App.autoassign') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $x = 1 ?>
                    <?php foreach (\App\Models\ProductCategory::select('*')->orderBy('name', 'ASC')->get(['parent_code' => 'AST']) as $category) : ?>
                      <tr>
                        <input type="hidden" name="group[<?= $x ?>][category]" value="<?= $category->code ?>">
                        <td><?= $category->name ?></td>
                        <td>
                          <select class="select-tech-support" id="pic_<?= strtolower($category->code) ?>" name="group[<?= $x ?>][pic]" data-placeholder="Pilih TS" style="width:100%">
                          </select>
                        </td>
                        <td>
                          <div class="form-group">
                            <input type="checkbox" id="assign_<?= strtolower($category->code) ?>" name="group[<?= $x ?>][auto_assign]" value="1">
                          </div>
                        </td>
                      </tr>
                      <?php $x++; ?>
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
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let whJS = JSON.parse('<?= $warehouse->json ?>');
    let maintenances = (whJS.maintenances ?? []);

    if (maintenances) {
      for (let schedule of maintenances) {
        $(`#pic_${schedule.category.toLowerCase()}`).val(schedule.pic).trigger('change');
        preSelect2('user', `#pic_${schedule.category.toLowerCase()}`, schedule.pic).catch(err => console.warn(err));

        if (schedule.auto_assign == 1) {
          $(`#assign_${schedule.category.toLowerCase()}`).iCheck('check');
        }
      }
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/maintenance/schedule/edit/<?= $warehouse->id ?>'
    });
  });
</script>