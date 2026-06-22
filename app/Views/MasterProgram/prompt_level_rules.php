<div class="card border card-border-primary mb-3">
    <div class="card-header">
        <h5 class="card-title">Traffic Light Probes Rules for Combination - <?= esc($combinationName) ?> </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-nowrap">
                <thead>
                    <tr>
                        <th>Phase Name</th>
                        <th>Consecutive Criteria</th>
                        <th>Same Day Check</th>
                        <th>If Phase Meets Criteria</th>
                        <th>If Phase Fails Criteria</th>
                        <th>Program Change Required</th>
                        <th>Session Limit</th>
                        <th>Days to Appear After</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule) : ?>
                        <tr>
                            <td><?= esc($rule['phase_name']) ?></td>
                            <td><?= esc($rule['json_rules']['consecutive_criteria'] ?? '') ?></td>
                            <td><?= esc($rule['json_rules']['same_day_check']) ? "Yes" : '' ?></td>
                            <td><?= esc($rule['p_phase_name'] ?? '') ?></td>
                            <td><?= esc($rule['f_phase_name'] ?? '') ?></td> 
                            <td><?= esc($rule['json_rules']['program_change'] ? "Yes" : '') ?></td>
                            <td><?= esc($rule['json_rules']['session_limit'] ??  '') ?></td>
                            <td><?= esc($rule['json_rules']['activation_days'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>