<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?> (<?=  $product->code ?>)</h5>
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
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="created_by"><?= lang('App.createdby') ?></label>
                  <select id="created_by" name="created_by" class="select-creator" data-placeholder="<?= lang('App.createdby') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="condition"><?= lang('App.condition') ?> *</label>
                  <select id="condition" name="condition" class="select" data-placeholder="<?= lang('App.condition') ?>" style="width:100%">
                    <option value=""></option>
                    <option value="good"><?= lang('Status.good') ?></option>
                    <option value="off"><?= lang('Status.off') ?></option>
                    <option value="solved"><?= lang('Status.solved') ?></option>
                    <option value="trouble"><?= lang('Status.trouble') ?></option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input id="attachment" name="attachment" class="custom-file-input" type="file">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-center">
                <div class="form-group">
                  <img class="attachment-preview" src="<?= $report->attachment ? base_url('attachment/' . $report->attachment) : '' ?>" style="max-width:300px">
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
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="editor2"><?= lang('App.techsupportnote') ?></label>
                  <div id="editor2"></div>
                  <input type="hidden" name="note_ts">
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
    if (!hasAccess('MaintenanceReport.Edit')) {
      erp.select2.creator.id = [erp.user.id];
    }

    if (erp.warehouse.id) {
      erp.select2.warehouse.id = [erp.warehouse.id];
      preSelect2('warehouse', '#warehouse', erp.warehouse.id).catch(err => console.warn(err));
    }

    $('#attachment').change(function() {
      let src = '';

      if (this.files.length) {
        src = URL.createObjectURL(this.files[0]);
      } else {
        src = base_url + '/assets/app/images/picture.png';
      }

      $('.attachment-preview').prop('src', src);
    });

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    let editor2 = new Quill('#editor2', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    editor2.on('text-change', (delta, oldDelta, source) => {
      $('[name="note_ts"]').val(editor2.root.innerHTML);
    });

    $('#date').val('<?= dateTimeJS($report->created_at) ?>');
    $('#condition').val('<?= $report->condition ?>').trigger('change');
    preSelect2('user', '#created_by', '<?= $report->created_by ?>');
    preSelect2('warehouse', '#warehouse', '<?= $report->warehouse_id ?>');
    editor.root.innerHTML = `<?= $report->note ?>`;
    editor2.root.innerHTML = `<?= $report->pic_note ?>`;

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/maintenance/report/edit/<?= $report->id ?>'
    });
  });
</script>