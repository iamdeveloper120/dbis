<?php
$prefix = $method;
$rule = $rule ?? [];
$stepValue = $rule['step_mastery']['value'] ?? '';
$overallValue = $rule['overall_mastery']['value'] ?? '';
$overallCheck = $rule['overall_mastery']['check'] ?? '';
?>

<div class="mb-2">
    <?php if (in_array($method, ['forward', 'backward'])): ?>
        <strong>Step Mastery:</strong> Step is mastered after <em><?= esc($stepValue) ?></em> consecutive IND responses.<br>
        <strong>Overall Mastery:</strong> Target is mastered when <em>all steps</em> are marked as mastered.
    <?php elseif ($method === 'total_task'): ?>
        <strong>Overall Mastery:</strong> Target is mastered after <em><?= esc($overallValue) ?></em> consecutive days with <em><?= esc($overallCheck) ?>%</em> IND across all steps.
    <?php endif; ?>
</div>

<?php if (in_array($method, ['forward', 'backward'])): ?>
    <div class="mb-2">
        <label class="form-label">Step Mastery Consecutive Days<small class="text-muted">(IND × days)</small></label>
        <div class="input-group input-group-sm">
            <input
                type="number"
                name="<?= $prefix ?>[step_mastery][value]"
                class="form-control"
                placeholder="e.g. 3"
                value="<?= esc($stepValue) ?>"
                min="1"
                required
            >
            
        </div>
    </div>

     
<?php endif; ?>

<?php if ($method === 'total_task'): ?>
  
    <div class="mb-2">
        <label class="form-label">Overall Mastery Consecutive Days and IND %</label>
        <div class="input-group input-group-sm">
            <input
                type="number"
                name="<?= $prefix ?>[overall_mastery][value]"
                class="form-control"
                placeholder="Days (e.g. 3)"
                value="<?= esc($overallValue) ?>"
                min="1"
                required
            >
            <input
                type="number"
                name="<?= $prefix ?>[overall_mastery][check]"
                class="form-control"
                placeholder="% IND (1-100)"
                value="<?= esc($overallCheck) ?>"
                min="1"
                max="100"
                required
            >
            
        </div>
    </div>
<?php endif; ?>
