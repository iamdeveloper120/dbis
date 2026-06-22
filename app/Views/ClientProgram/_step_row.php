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
