<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card" id="">
            <div class="card-header">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">The percentage of supervisor's clients that met their target rate per month</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?= view_cell('ClientsPercentageMetTargetBySupervisorCell', []) ?>



<?= $this->endSection() ?>
<?= $this->section("page_js") ?>

<?= $this->endSection() ?>