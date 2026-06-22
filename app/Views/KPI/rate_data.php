<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
 
<?= view_cell('ClientsTargetAndMonthlyRateCell', []) ?>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>

<?= $this->endSection() ?>