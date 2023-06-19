<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a id="export" class="btn btn-tool bg-gradient-success" href="<?= base_url('report/export/incomestatement') ?>" data-action="export">
              <i class="fad fa-download"></i>
            </a>
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-head-fixed-main table-hover table-striped dataTable" style="width:100%;">
            <thead>
              <tr>
                <th><?= lang('App.reference') ?></th>
                <th><?= lang('App.value') ?></th>
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

<!-- Control Sidebar -->
<aside class="control-sidebar">
  <!-- Control sidebar content goes here -->
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-indigo">
          <div class="card-title"><i class="fad fa-filter"></i> <?= lang('App.filter') ?></div>
        </div>
        <div class="card-body control-sidebar-content" style="max-height:400px">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-biller"><?= lang('App.biller') ?></label>
                <select id="filter-biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-startdate"><?= lang('App.startdate') ?></label>
                <input type="date" id="filter-startdate" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-enddate"><?= lang('App.enddate') ?></label>
                <input type="date" id="filter-enddate" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-warning filter-clear"><?= lang('App.clear') ?></button>
          <button class="btn btn-primary filter-apply"><?= lang('App.apply') ?></button>
        </div>
      </div>
    </div>
  </div>
</aside>
<!-- /.control-sidebar -->
<script type="module">
  import {
    IncomeStatement,
    TableFilter
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  // Initialize to non Lucretia Biller.
  IncomeStatement.table('#Table').load();

  TableFilter
    .bind('apply', '.filter-apply')
    .bind('clear', '.filter-clear')
    .on('apply', () => {
      let opt = {};
      let biller = $('#filter-biller').val();
      let startDate = $('#filter-startdate').val();
      let endDate = $('#filter-enddate').val();

      if (biller) {
        opt.biller = biller;
      }

      if (startDate) {
        opt.start_date = startDate;
      }

      if (endDate) {
        opt.end_date = endDate;
      }

      IncomeStatement.table('#Table').clean().load(opt);
    })
    .on('clear', () => {
      $('#filter-biller').val([]).trigger('change');
      $('#filter-startdate').val('');
      $('#filter-enddate').val('');

      IncomeStatement.table('#Table').clean().load();
    });
</script>