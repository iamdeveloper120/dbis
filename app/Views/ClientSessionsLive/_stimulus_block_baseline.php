<?php
$targetId = $target['target_id'];
$phaseId = $target['current_phase_id'];
$steps = $target['steps'] ?? [];
$orderedSteps = $steps;
usort($orderedSteps, fn($a, $b) => $a['step_number'] <=> $b['step_number']);
$masteredSteps = array_map(fn($s) => $s['step_id'], array_filter($steps, fn($s) => $s['is_mastered']));
$attempts = 3;
$prefillMap = $target['prefill_step_inputs'][$phaseId]['baseline'] ?? [];
?>

<div class="row">
    <div class="col-md-1">
        <div class="nav flex-column nav-pills custom-verti-nav-pills" id="baseline-tab-<?= $targetId ?>" role="tablist" aria-orientation="vertical">
            <?php for ($i = 0; $i < $attempts; $i++): ?>
                <a class="nav-link <?= $i === 0 ? 'active show' : '' ?>"
                    id="baseline-tab-<?= $targetId ?>-<?= $i ?>"
                    data-bs-toggle="pill"
                    href="#baseline-content-<?= $targetId ?>-<?= $i ?>"
                    role="tab"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                    Baseline <?= $i + 1 ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <div class="col-md-11">
        <div class="tab-content mt-0" id="baseline-tabContent-<?= $targetId ?>">
            <?php for ($i = 0; $i < $attempts; $i++): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                    id="baseline-content-<?= $targetId ?>-<?= $i ?>"
                    role="tabpanel">

                    <div class="table-responsive mb-3">
                        <div class="table border rounded">
                            <div class="row g-0 bg-light border-bottom fw-bold text-center py-2">
                                <div class="col-1">#</div>
                                <div class="col-4 text-start">SD / Consequence</div>
                                <div class="col-4 text-start">Response</div>
                                <div class="col-3">Input</div>
                            </div>

                            <?php foreach ($orderedSteps as $step):
                                $stepId = $step['step_id'];
                                $isMastered = in_array($stepId, $masteredSteps);
                                $choices = $baseline_choices;
                            ?>
                                <div class="row g-0 border-bottom align-items-center text-center py-2">
                                    <div class="col-1"><?= esc($step['step_number']) ?></div>
                                    <div class="col-4 text-start"><b>S:</b> <?= esc($step['sd_text']) ?><br><b>C:</b> <?= esc($step['c_text']) ?></div>
                                    <div class="col-4 text-start"><?= esc($step['response_text']) ?></div>
                                    <div class="col-3">
                                        <div class="btn-group btn-group-sm">
                                            <?php foreach ($choices as $choice):
                                                $name = "stimulus[{$targetId}][{$stepId}][{$i}]";
                                                $id = "{$name}_{$choice['value']}";
                                            ?>
                                                <?php
                                                $existingValue = $prefillMap[$stepId][$i] ?? null;
                                                $isChecked = ($existingValue === $choice['value']) ? 'checked' : '';
                                                ?>
                                                <input type="radio"
                                                    class="btn-check"
                                                    name="<?= $name ?>"
                                                    id="<?= $id ?>"
                                                    value="<?= $choice['value'] ?>"
                                                    <?= $isChecked ?>>
                                                <label class="btn btn-outline-primary" for="<?= $id ?>" title="<?= esc($choice['label']) ?>">
                                                    <?= $choice['label'] ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary save-stimulus-button"
                            data-target-id="<?= $targetId ?>"
                            data-phase-id="<?= $phaseId ?>"
                            data-attempt="<?= $i ?>"
                            data-goal-id="<?= $goal->id ?>"
                            data-domain-id="<?= $domain->id ?>"
                            data-session-id="<?= $session_id ?>"
                            data-client-id="<?= $client_id ?>"
                            data-probe-set-id="<?= $target['client_probe_set_id'] ?>"
                            data-method="baseline">
                            <i class="ri-save-line me-1"></i> Save Baseline <?= $i + 1 ?>
                        </button>
                    </div>

                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>