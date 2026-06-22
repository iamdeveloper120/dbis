<div class="card border card-border-primary mb-3">
    <div class="card-header">
        <h5 class="card-title">Stimulus Program Chain Probes Rules for Combination - <?= esc($combinationName) ?> </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-nowrap">
                <thead>
                    <tr>
                        <th>Phase Name</th>
                        <th>If Phase Meets Criteria</th>
                        <th>If Phase Fails Criteria</th>
                        <th>Rules</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule) : ?>
                        <tr>
                            <td><?= esc($rule['phase_name']) ?></td>
                            <td><?= esc($rule['p_phase_name'] ?? '') ?></td>
                            <td><?= esc($rule['f_phase_name'] ?? '') ?></td>
                            <td>
                                <?php
                                $rulesJson = json_decode($rule['rules'], true);
                                if (!$rulesJson) {
                                    echo '<em>Invalid rule format</em>';
                                } else {
                                    // Display baseline-specific logic if it's a baseline rule
                                    $isBaseline = (strtolower($rule['phase_name']) === 'baseline');

                                    if ($isBaseline) {
                                        if (!empty($rulesJson['consecutive_criteria'])) {
                                            echo "- Baseline Rule: Each step must have <strong>{$rulesJson['consecutive_criteria']}</strong> IND responses.<br>";
                                        }
                                        if (isset($rulesJson['same_day_check']) && $rulesJson['same_day_check']) {
                                            echo "- Responses must be collected on the <strong>same day</strong> for each step.<br>";
                                        }
                                        echo "- The target will be considered baseline mastered only if <strong>every step has 3 IND responses on the same day</strong> (100% IND).<br><hr class='my-2'>";
                                    }
                                    

                                    // Show chaining-type specific mastery rules
                                    foreach (['forward', 'backward', 'total_task'] as $method) {
                                        if (empty($rulesJson[$method])) continue;

                                        $methodLabel = ucfirst(str_replace('_', ' ', $method));
                                        echo "<u><strong>{$methodLabel} Chaining</strong></u><br>";

                                        // Step Mastery
                                        if (!empty($rulesJson[$method]['step_mastery'])) {
                                            $m = $rulesJson[$method]['step_mastery'];
                                            if (!empty($m['type']) && $m['type'] === 'consecutive') {
                                                echo "- Step Mastery: Step is mastered after <strong>{$m['value']}</strong> consecutive <strong>{$m['check']}</strong> responses.<br>";
                                            }
                                        }

                                        // Overall Mastery
                                        if (!empty($rulesJson[$method]['overall_mastery'])) {
                                            $m = $rulesJson[$method]['overall_mastery'];
                                            if ($m['type'] === 'all_steps_mastered') {
                                                echo "- Overall Mastery: Target is mastered when <strong>all steps are marked as mastered</strong>.<br>";
                                            } elseif ($m['type'] === 'consecutive' && is_numeric($m['check'])) {
                                                echo "- Overall Mastery: Target is mastered after <strong>{$m['value']}</strong> consecutive days with <strong>{$m['check']}%</strong> IND responses across all steps.<br>";
                                            }
                                        }

                                        echo "<hr class='my-2'>";
                                    }
                                }
                                ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>