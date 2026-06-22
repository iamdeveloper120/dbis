<?php
$modalId = $modal_id ?? 'mandsHelpModal';
$modalLabelId = $modalId . 'Label';
?>
<div class="modal fade" id="<?= esc($modalId); ?>" tabindex="-1" aria-labelledby="<?= esc($modalLabelId); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title mands-help-modal-title" id="<?= esc($modalLabelId); ?>">Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 mands-help-modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="ri-close-line align-bottom me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
