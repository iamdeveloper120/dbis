<?php
$targetId = $target['target_id'];
$phaseId = $target['current_phase_id'];
$steps = $target['steps'] ?? [];
$prefillMap = $target['prefill_step_inputs'][$phaseId]['backward'] ?? [];
$choices = $teaching_choices;

// 🔢 Sort steps in forward order for display
$orderedSteps = $steps;
usort($orderedSteps, fn($a, $b) => $a['step_number'] <=> $b['step_number']);

// 🧠 Identify mastered steps (specific to backward method) 
$masteredSteps = array_map(
    fn($s) => $s['step_id'],
    array_filter($steps, fn($s) =>
        $s['is_mastered'] &&
        in_array($s['mastered_with_chain'] ?? '', ['backward', 'baseline'])
    )
);

// 🔍 Identify last unmastered step for input
$unmasteredSteps = array_filter(
    $orderedSteps,
    fn($s) =>
        !$s['is_mastered'] // only unmastered steps
);

$unmasteredSteps = array_reverse($unmasteredSteps);

$lastUnmasteredStepId = !empty($unmasteredSteps)
    ? reset($unmasteredSteps)['step_id']
    : null;
?>

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
            $canEdit = ($stepId === $lastUnmasteredStepId);
            $existingValue = $prefillMap[$stepId][0] ?? null;
        ?>
        <div class="row g-0 border-bottom align-items-center text-center py-2">
            <div class="col-1"><?= esc($step['step_number']) ?></div>
            <div class="col-4 text-start">
                <b>S:</b> <?= esc($step['sd_text']) ?><br>
                <b>C:</b> <?= esc($step['c_text']) ?>
            </div>
            <div class="col-4 text-start"><?= esc($step['response_text']) ?></div>
            <div class="col-3">
                <?php if ($isMastered): ?>
                    <span class="badge border border-primary text-primary" style="font-size: 12px;">Mastered</span>
                <?php elseif ($canEdit): ?>
                    <div class="btn-group btn-group-sm">
                        <?php foreach ($choices as $choice):
                            $name = "stimulus[{$targetId}][{$stepId}]";
                            $id = "{$name}_{$choice['value']}";
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
                <?php else: ?>
                    <span class="text-muted">Locked</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="text-end">
    <button class="btn btn-primary save-stimulus-button"
        data-target-id="<?= $targetId ?>"
        data-phase-id="<?= $phaseId ?>"
        data-attempt="0"
        data-goal-id="<?= $goal->id ?>"
        data-domain-id="<?= $domain->id ?>"
        data-session-id="<?= $session_id ?>"
        data-client-id="<?= $client_id ?>"
        data-probe-set-id="<?= $target['client_probe_set_id'] ?>"
        data-method="backward">
        <i class="ri-save-line me-1"></i> Save Backward Chain
    </button>
</div>
