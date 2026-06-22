<div class="card border card-border-primary mb-3">
    <div class="card-header">
        <h5 class="card-title">Duration Probes Rules for Combination - <?= esc($combinationName) ?> </h5>
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
                        <?php $phaseId = esc($rule['phase_id']); ?>
                        <?php if ($phaseId == 1 || $phaseId == 3 || $phaseId == 4) : ?>
                            <tr>
                                <td><?= esc($rule['phase_name']) ?></td>
                                <td><?= esc($rule['json_rules']['consecutive_criteria'] ?? '') ?></td>
                                <td><?= esc($rule['json_rules']['same_day_check'] ? 'Yes' : '') ?></td>
                                <td><?= esc($rule['p_phase_name']) ?></td>
                                <td><?= esc($rule['f_phase_name']) ?></td>
                                <td><?= esc($rule['json_rules']['program_change'] ? 'Yes' : '') ?></td>
                                <td><?= esc($rule['json_rules']['session_limit'] ?? '') ?></td>
                                <?php if ($phaseId == 3) : ?>
                                    <td><input class="form-control form-control-sm" type="number" name="activation_days_<?= $phaseId ?>" value="<?= esc($rule['json_rules']['activation_days'] ?? '') ?>"></td>
                                <?php else : ?>
                                    <td><?= esc($rule['json_rules']['activation_days'] ?? '') ?></td>
                                <?php endif ?>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td><?= esc($rule['phase_name']) ?></td>
                                <td><input type="number" class="form-control form-control-sm" name="consecutive_criteria_<?= $phaseId ?>" value="<?= esc($rule['json_rules']['consecutive_criteria'] ?? '') ?>"></td>
                                <td><?= esc($rule['json_rules']['same_day_check'] ? 'Yes' : '') ?></td>
                                <td><?= esc($rule['p_phase_name']) ?></td>
                                <td><?= esc($rule['f_phase_name']) ?></td>
                                <td>
                                    <div class="form-check form-switch form-switch-lg" style="padding-left: 10px !important;">
                                        <input class="form-check-input" type="checkbox" role="switch" name="program_change_<?= $phaseId ?>" <?= isset($rule['json_rules']['program_change']) && $rule['json_rules']['program_change'] ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td><input class="form-control form-control-sm" type="number" name="session_limit_<?= $phaseId ?>" value="<?= esc($rule['json_rules']['session_limit'] ?? '') ?>"></td>
                                <td><?= esc($rule['json_rules']['activation_days'] ?? '') ?></td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>