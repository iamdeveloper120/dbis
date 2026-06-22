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
            <form id="addStepForm" class="d-flex gap-2 align-items-start flex-wrap">
                <input type="hidden" name="target_id" value="<?= esc($target_id) ?>">
                <input type="hidden" name="client_id" value="<?= esc($client_id) ?>">
                <input type="hidden" name="goal_id" value="<?= esc($goal_id) ?>">

                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control form-control-sm" name="sd_text" id="sd_text" placeholder="SD Text" required>
                    <label for="sd_text">SD</label>
                </div>
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control form-control-sm" name="response_text" id="response_text" placeholder="Response">
                    <label for="response_text">Response</label>
                </div>
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control form-control-sm" name="c_text" id="c_text" placeholder="Consequence">
                    <label for="c_text">Consequence</label>
                </div>
                
                <div>
                    <button type="submit" class="btn btn-primary btn">
                        <i class="ri-add-line"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Steps List -->
    <ul class="list-group sortable-steps" id="stimulusStepList">
        <?php foreach ($steps as $index => $step): ?>
            <li class="list-group-item d-flex align-items-start justify-content-between step-item" data-id="<?= esc($step->id) ?>">
                <div class="step-content w-100">
                    <div class="step-header d-flex justify-content-between align-items-center">
                        <strong class="step-number me-2"><i class="ri-drag-move-fill align-bottom handle"></i> Step #<?= $index + 1 ?></strong>
                        <span class="text-muted">ID: <?= esc($step->id) ?></span>
                    </div>
                    <div class="step-fields mt-2 d-flex gap-2 align-items-start">
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control form-control-sm sd-text" value="<?= esc($step->sd_text) ?>" disabled>
                            <label>SD</label>
                        </div>
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control form-control-sm response-text" value="<?= esc($step->response_text) ?>" disabled>
                            <label>Response</label>
                        </div>
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control form-control-sm c-text" value="<?= esc($step->c_text) ?>" disabled>
                            <label>Consequence</label>
                        </div>
                       
                    </div>
                </div>
                <div class="btn-group btn-group-sm ms-2 mt-3">
                    <button class="btn btn-outline-secondary btn-edit"><i class="ri-pencil-line"></i></button>
                    <button class="btn btn-outline-success btn-save d-none"><i class="ri-save-line"></i></button>
                    <button class="btn btn-outline-danger btn-delete"><i class="ri-delete-bin-line"></i></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
 
<script>
   

    function initSortableSteps() {
        const stepList = document.getElementById('stimulusStepList');
        new Sortable(stepList, {
            animation: 150,
            handle: ".handle",
            onEnd: function() {
                updateStepNumbers();
                const orderedIds = [];
                $('#stimulusStepList .step-item').each(function(i) {
                    orderedIds.push({
                        id: $(this).data('id'),
                        step_number: i + 1
                    });
                });

                $.post('/client-program/target/reorder-stimulus-steps', {
                    steps: JSON.stringify(orderedIds),
                }, function(res) {
                    if (res.status !== 'success') {
                        alert('Failed to save new order!');
                    }
                }).fail(function() {
                    alert('Server error while saving order!');
                });
            }
        });
    }

    function updateStepNumbers() {
        $('#stimulusStepList .step-item').each(function(i) {
            $(this).find('.step-number').html(`<i class="ri-drag-move-fill align-bottom handle"></i> Step #${i + 1}`);
        });
    }

    // Initial sortable init
    $(document).ready(function() {
        initSortableSteps();
    });

    // Handle add
    // Handle add
    $(document).on('submit', '#addStepForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();

        $.post('/client-program/target/add-stimulus-step', formData, function(res) {
            if (res.status === 'success') {
                $('#stimulusStepList').append(res.step_html); // Append new row
                form[0].reset(); // Clear inputs
                updateStepNumbers(); // Refresh step numbers
            } else {
                alert('Failed to add step');
            }
        });
    });


    // Handle edit
    $(document).on('click', '#stimulusStepList .btn-edit', function() {
        const item = $(this).closest('.step-item');
        item.find('input').prop('disabled', false);
        item.find('.btn-edit').addClass('d-none');
        item.find('.btn-save').removeClass('d-none');
    });

    // Handle save
    $(document).on('click', '#stimulusStepList .btn-save', function() {
        const item = $(this).closest('.step-item');
        const id = item.data('id');
        const sd = item.find('.sd-text').val();
        const c = item.find('.c-text').val();
        const r = item.find('.response-text').val();

        $.post('/client-program/target/update-stimulus-step', {
            id: id,
            sd_text: sd,
            c_text: c,
            response_text: r,
        }, function(res) {
            if (res.status === 'success') {
                item.find('input').prop('disabled', true);
                item.find('.btn-edit').removeClass('d-none');
                item.find('.btn-save').addClass('d-none');
            }
        });
    });

    // Handle delete
    // Handle delete with SweetAlert2
    $(document).on('click', '#stimulusStepList .btn-delete', function() {
        const item = $(this).closest('.step-item');
        const id = item.data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This step will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/client-program/target/delete-stimulus-step', {
                    id: id,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                }, function(res) {
                    if (res.status === 'success') {
                        item.remove();
                        updateStepNumbers();

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Step has been removed.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', 'Failed to delete the step.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Server error occurred.', 'error');
                });
            }
        });
    });
</script>