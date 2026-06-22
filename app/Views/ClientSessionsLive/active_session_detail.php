<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Active Session Detail</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><?= $activeSession->client_first_name . ' ' . $activeSession->client_last_name . '( ' . $activeSession->internal_mrn . ' )' ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Session Date & Time:</strong> <?= app_date($activeSession->session_date) . ' at ' . date('H:i:s', strtotime($activeSession->start_time)); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Instructor & Supervisor:</strong>
                        <?= $activeSession->instructor_first_name . ' ' . $activeSession->instructor_last_name; ?>,
                        <?= $activeSession->supervisor_first_name . ' ' . $activeSession->supervisor_last_name; ?>
                    </li>
                </ul>
                <div class="mt-4">
                    <div class="d-flex gap-3">
                        <form method="post" action="<?= base_url('sessions/live/client/' . encodeValue($activeSession->client_id)); ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="continue">
                            <button type="submit" class="btn btn-success">Continue Active Session</button>
                        </form>
                    </div>
                    <hr class="my-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmNewSession">
                        <label class="form-check-label" for="confirmNewSession">
                            I understand that starting a new session will end the active session and I want to proceed.
                        </label>
                    </div>
                    <div class="d-flex gap-3">
                        <form method="post" action="<?= base_url('sessions/live/client/' . encodeValue($activeSession->client_id)); ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="new">
                            <button type="submit" class="btn btn-danger" id="startNewSessionBtn" disabled>Start New Session</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        $('#confirmNewSession').change(function() {
            $('#startNewSessionBtn').prop('disabled', !this.checked);
        });
    });
</script>
<?= $this->endSection() ?>