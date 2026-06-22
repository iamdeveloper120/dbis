<?= $this->extend("layout/master-profile") ?>

<?= $this->section("page_content") ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Selected Reinforcers</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($defaultReinforcers)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Order</th>
                            <th>Reinforcer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($defaultReinforcers as $reinforcer): ?>
                            <tr>
                                <td><?= esc((string) ($reinforcer['order'] ?? 0)) ?></td>
                                <td><?= esc($reinforcer['name'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                No default reinforcers are configured for this client.
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
