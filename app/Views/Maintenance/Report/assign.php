<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?> (<?= $product->code ?>)</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label for="techsupport"><?= lang('App.techsupport') ?></label>
          <select id="techsupport" name="techsupport" class="select-tech-support" data-placeholder="<?= lang('App.techsupport') ?>" style="width:100%">
          </select>
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
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/maintenance/report/assign/<?= $product->id ?>'
    });
  });
</script>