<?php
if ($chain !== null) {
    $existingMethod = $chain['method'] ?? null;
    $chainRules = !empty($chain['rule_override']) ? json_decode($chain['rule_override'], true) : [];
} else {
    $existingMethod = null;
    $chainRules = [];
}
?>

<div class="container-fluid">
    <!-- Add New Step Form -->
    <div class="card border mb-3">
        <div class="card-header">
            <span class="card-title">
                [<strong>Domain:</strong> <?= esc($target->domain_name) ?> (<?= esc($target->domain_code) ?>)]
                [<strong>Goal:</strong> <?= esc($target->goal_name) ?> (<?= esc($target->goal_code) ?>)]
                [<strong>Target:</strong> <?= esc($target->name) ?>]

            </span>
        </div>
        <div class="card-body">
            <form id="stimulusChainForm">
                <input type="hidden" name="target_id" value="<?= esc($target_id) ?>">

                <div class="row g-3">

                    <!-- Forward Chaining -->
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input class="form-check-input chaining-radio" type="radio" name="chaining_method" id="method_forward" value="forward" <?= $existingMethod === 'forward' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="method_forward">Forward Chaining</label>
                                </div>
                            </div>
                            <div class="card-body chaining-rule-section" data-method="forward">
                                <!-- Forward Chaining -->
                                <?= view('ClientProgram/_chain_rule_section', [
                                    'method' => 'forward',
                                    'rule' => $chainRules['forward'] ?? null
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Backward Chaining -->
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input class="form-check-input chaining-radio" type="radio" name="chaining_method" id="method_backward" value="backward" <?= $existingMethod === 'backward' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="method_backward">Backward Chaining</label>
                                </div>
                            </div>
                            <div class="card-body chaining-rule-section" data-method="backward">
                                <!-- Backward Chaining -->
                                <?= view('ClientProgram/_chain_rule_section', [
                                    'method' => 'backward',
                                    'rule' => $chainRules['backward'] ?? null
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Total Task Chaining -->
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input class="form-check-input chaining-radio" type="radio" name="chaining_method" id="method_total_task" value="total_task" <?= $existingMethod === 'total_task' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="method_total_task">Total Task Chaining</label>
                                </div>
                            </div>
                            <div class="card-body chaining-rule-section" data-method="total_task">
                                <!-- Total Task Chaining -->
                                <?= view('ClientProgram/_chain_rule_section', [
                                    'method' => 'total_task',
                                    'rule' => $chainRules['total_task'] ?? null
                                ]) ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save Chaining</button>
                </div>
            </form>

            <script>
                $(document).ready(function() {
                    function toggleRuleInputs() {
                        const selectedMethod = $('input[name="chaining_method"]:checked').val();
                        $('.chaining-rule-section').each(function() {
                            const method = $(this).data('method');
                            const inputs = $(this).find('input, select');
                            if (method === selectedMethod) {
                                inputs.prop('disabled', false);
                            } else {
                                inputs.prop('disabled', true);
                            }
                        });
                    }

                    // Trigger toggle on load and change
                    toggleRuleInputs();
                    $('.chaining-radio').on('change', toggleRuleInputs);
                });
                $(document).on('submit', '#stimulusChainForm', function(e) {
                    e.preventDefault();
                    const formData = $(this).serialize();

                    $.post('/client-program/target/save-stimulus-chain', formData, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Saved!', res.message, 'success');
                            // Optionally: close the offcanvas
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                });
            </script>

        </div>
    </div>
</div>