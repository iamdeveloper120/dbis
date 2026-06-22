<?= $this->extend("layout/master-profile") ?>

<?= $this->section("page_content") ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Individualised ABC Data</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border card-border-primary h-100 mb-0">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Antecedent</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($antecedents)): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($antecedents as $item): ?>
                                    <li><?= esc($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No antecedent data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border card-border-primary h-100 mb-0">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Behavior</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($behaviors)): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($behaviors as $item): ?>
                                    <li><?= esc($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No behavior data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border card-border-primary h-100 mb-0">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Consequence</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($consequences)): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($consequences as $item): ?>
                                    <li><?= esc($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No consequence data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
