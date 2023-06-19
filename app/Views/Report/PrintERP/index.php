<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/machine') ?>" class="btn btn-block btn-danger" data-action="export">
                  <i class="fad fa-download"></i> Report Machine & Maintenance
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/qms') ?>" class="btn btn-block btn-success" data-action="export">
                  <i class="fad fa-download"></i> Report QMS
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/production') ?>" class="btn btn-block btn-primary" data-action="export">
                  <i class="fad fa-download"></i> Report Sales Production
                </a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/trackingpod') ?>" class="btn btn-block btn-warning" data-action="export">
                  <i class="fad fa-download"></i> Report Tracking POD
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/producttransfer') ?>" class="btn btn-block btn-info" data-action="export">
                  <i class="fad fa-download"></i> Report Product Transfer
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/coh') ?>" class="btn btn-block btn-danger" data-action="export">
                  <i class="fad fa-download"></i> Report Setoran COH
                </a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/balancesheet') ?>" class="btn btn-block btn-success" data-action="export">
                  <i class="fad fa-download"></i> Report Balance Sheet
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/solditem') ?>" class="btn btn-block btn-primary" data-action="export">
                  <i class="fad fa-download"></i> Report Sold Items
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/usability') ?>" class="btn btn-block btn-warning" data-action="export">
                  <i class="fad fa-download"></i> Report Sparepart Usability
                </a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <a href="<?= base_url('report/export/rawcost') ?>" class="btn btn-block btn-info" data-action="export">
                  <i class="fad fa-download"></i> Report Sale RAW Cost
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="#" class="btn btn-block btn-danger">-</a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <a href="#" class="btn btn-block btn-success">-</a>
              </div>
            </div>
          </div>
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
                <label for="filter-warehouse"><?= lang('App.warehouse') ?></label>
                <select id="filter-warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" multiple>
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
    TableFilter
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  TableFilter
    .bind('apply', '.filter-apply')
    .bind('clear', '.filter-clear')
    .on('clear', () => {
      $('#filter-biller').val([]).trigger('change');
      $('#filter-warehouse').val([]).trigger('change');
      $('#filter-startdate').val('');
      $('#filter-enddate').val('');
    });
</script>
<script>
  $(document).ready(function() {
    "use strict";
  });
</script>