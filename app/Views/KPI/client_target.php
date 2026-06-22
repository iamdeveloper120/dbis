<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card" id="">
            <div class="card-header">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">Client's Target KPI</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?= view_cell('OverallPercentageOfClientsMetTargetCell', []) ?>
<?= view_cell('ClientsPercentageMetTargeMonthViseCell', []) ?>




<?= $this->endSection() ?>
<?= $this->section("page_js") ?>

<?= $this->endSection() ?>