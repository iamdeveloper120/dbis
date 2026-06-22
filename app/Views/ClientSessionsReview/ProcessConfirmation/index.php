<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {

        --vz-offcanvas-width: 100%;

    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<?= view('ClientSessionsReview/_common_header', ['section_name' => 'Process Data Confirmation']) ?>
<?php
// Function to convert session rating numbers to text
function getSessionRatingText($rating)
{
    $ratings = [
        '1' => 'Poor',
        '2' => 'Good',
        '3' => 'Excellent'
    ];
    return isset($ratings[$rating]) ? $ratings[$rating] : 'Not Rated';
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('ClientSessionsReview/_tabs', ['tab' => 'process_data']) ?>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="info-tab">
                        <div class="text-center">

                        </div>
                        <?php if ($session->status == 2 || $session->status == 4): ?>
                            <div class="row">
                                <!-- Left Column: Instructions -->
                                <div class="col-lg-4 col-12">
                                    <!-- Dropdown for Session Quality Rating -->
                                    <div class="mb-3">
                                        <label for="session_rating" class="form-label">Session Quality Rating (1-3)</label>
                                        <select id="session_rating" class="form-select">
                                            <option value="">Select Rating</option>
                                            <option value="1" <?= isset($session->session_rating) && $session->session_rating == 1 ? 'selected' : '' ?>>Poor</option>
                                            <option value="2" <?= isset($session->session_rating) && $session->session_rating == 2 ? 'selected' : '' ?>>Good</option>
                                            <option value="3" <?= isset($session->session_rating) && $session->session_rating == 3 ? 'selected' : '' ?>>Excellent</option>
                                        </select>
                                    </div>

                                    <!-- Text Area -->
                                    <div class="mb-3">
                                        <label for="instructor_comments" class="form-label">Comments</label>
                                        <textarea id="instructor_comments" class="form-control" rows="5"><?= isset($session->instructor_comments) ? htmlspecialchars($session->instructor_comments, ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                                    </div>


                                </div>
                                <div class="col-lg-4 col-12">
                                    <!-- Text Area -->
                                    <div class="mb-3">
                                        <label for="wow_comments" class="form-label">Wow moments!</label>
                                        <textarea id="wow_comments" class="form-control" rows="9"><?= isset($session->comments) ? htmlspecialchars($session->comments, ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                                    </div>
                                </div>
                                <!-- Right Column: Session Quality Rating Dropdown, Text Area, and Process Button -->
                                <div class="col-lg-4 col-12 d-flex align-items-center justify-content-center text-center py-5">
                                    <div>
                                        <h5>Make sure you have reviewed all session data.</h5>
                                        <p class="text-muted">Once processed, you will not be able to reverse this.</p>
                                        <div id="alert-container"></div>
                                        <!-- Process Button -->
                                        <button type="button" id="process-btn" class="btn btn-primary btn-label waves-effect waves-light" onclick="processData(<?= $session->id; ?>)">
                                            <i class="ri-check-double-line label-icon align-middle fs-16 me-2"></i> Process all session data
                                        </button>
                                    </div>

                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($lastProcessingLog): ?>
                            <h4 class="mt-4">Last Processed Session Information</h4>
                            <div class="card border">
                                <div class="card-body">
                                    <table class="table table-bordered table-info mt-2">
                                        <thead>
                                            <tr>
                                                <th>Processed At</th>
                                                <th>Processed By</th>
                                                <th>Total Targets</th>
                                                <th>Success</th>
                                                <th>Conflicts</th>
                                                <th>Deleted</th>
                                                <th>Session Status</th>
                                                <th>Session Rating</th>
                                                <th>Instructor Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?= app_date($lastProcessingLog['processed_at'], true); ?></td>
                                                <td><?= htmlspecialchars($lastProcessingLog['first_name'] . ' ' . $lastProcessingLog['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $lastProcessingLog['total_targets']; ?></td>
                                                <td><?= $lastProcessingLog['processed_success']; ?></td>
                                                <td><?= $lastProcessingLog['conflicted_targets']; ?></td>
                                                <td><?= $lastProcessingLog['deleted_targets']; ?></td>
                                                <td><?= $lastProcessingLog['session_status']; ?></td>
                                                <td><?= $sessionDetails ? getSessionRatingText($sessionDetails['session_rating']) : ''; ?></td>
                                                <td><?= $sessionDetails ? $sessionDetails['instructor_comments'] : ''; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php if (!empty($conflictedTargets)): ?>
                                        <h5 class="mt-3 text-warning">Conflicted Targets</h5>
                                        <table class="table table-bordered mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Domain</th>
                                                    <th>Goal</th>
                                                    <th>Target</th>
                                                    <th>Phase</th>
                                                    <th>Result</th>
                                                    <th>Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($conflictedTargets as $target): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($target['data']['domain_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['goal_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['phase_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['result'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>

                                    <?php if (!empty($deletedTargets)): ?>
                                        <h5 class="mt-3 text-danger">Deleted Targets</h5>
                                        <table class="table table-bordered mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Domain</th>
                                                    <th>Goal</th>
                                                    <th>Target</th>
                                                    <th>Phase</th>
                                                    <th>Result</th>
                                                    <th>Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($deletedTargets as $target): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($target['data']['domain_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['goal_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['phase_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['data']['result'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?= htmlspecialchars($target['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <!-- Left Column: Instructions -->
                            <div class="col-lg-12">
                                <!-- Session Processing History -->
                                <h4 class="mt-4">Processing History</h4>
                                <table class="table table-bordered mt-2">
                                    <thead>
                                        <tr>
                                            <th>Processed At</th>
                                            <th>Processed By</th>
                                            <th>Total Targets</th>
                                            <th>Success</th>
                                            <th>Conflicts</th>
                                            <th>Deleted</th>
                                            <th>Session Status</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($processingLogs as $log): ?>
                                            <tr>
                                                <td><?= app_date($log['processed_at'], true); ?></td>
                                                <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $log['total_targets']; ?></td>
                                                <td><?= $log['processed_success']; ?></td>
                                                <td><?= $log['conflicted_targets']; ?></td>
                                                <td><?= $log['deleted_targets']; ?></td>
                                                <td><?= $log['session_status']; ?></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" onclick="showDetails(<?= htmlspecialchars(json_encode($log['id']), ENT_QUOTES, 'UTF-8') ?>)">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="manuallyTargetCanvas" aria-labelledby="offcanvasRightLabel">

     
    <div class="offcanvas-header border-bottom  bg-info-subtle">
        <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Processed Session Detail</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='manuallyTargetCanvasDetail'> 
        ...
    </div>
    <div class="offcanvas-footer border p-3 text-center bg-dark-subtle">
        <a href="javascript:void(0);" class="link-primary" data-bs-dismiss="offcanvas">Go Back <i class="ri-arrow-right-s-line align-middle ms-1"></i></a>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
    });

    function showStatus(status, message, type) {
        var alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <strong>${status}:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alert-container').html(alertHTML);
    }

    function processData(id) {
        // Get rating and comments values
        var session_rating = $('#session_rating').val();
        var instructor_comments = $('#instructor_comments').val();
        var comments = $('#wow_comments').val();

        // Validation: Ensure rating is selected
        if (!session_rating) {
            showStatus('Error', 'Please select a session quality rating.', 'danger');
            return;
        }

        // If rating is "Poor" (1), ensure comments are filled
        if (session_rating === "1" && !instructor_comments) {
            showStatus('Error', 'Please provide comments for a "Poor" rating.', 'danger');
            return;
        }

        var ajaxRequest = $.ajax({
            url: '<?php echo base_url() ?>sessions/process/all',
            type: 'post',
            data: {
                "id": id,
                "session_rating": session_rating,
                "instructor_comments": instructor_comments,
                "comments": comments
            },
            beforeSend: function(xhr) {
                $('#alert-container').html('');
                $('#spinner-container').show();
                $('#process-btn').prop('disabled', true);
            }
        });
        ajaxRequest.done(function(response) {
            console.log(response);


            if (response.success) {
                window.location.reload(); // Refresh page on success
                // Show success alert
                //showStatus('Success', response.message, 'success');

                // Remove the process button and show the back button
                //$('#process-btn').hide();
                //$('#spinner-container').hide();
                //$('#back-to-session-btn').show();

            } else {

                showStatus('Error', response.message, 'danger');
                // Re-enable the process button in case of failure
                $('#spinner-container').hide();
                $('#process-btn').prop('disabled', false);
            }
        });

        ajaxRequest.fail(function(jqXHR, textStatus, error) {
            // Hide the progress bar on failure
            $('#spinner-container').hide();
            // Show error alert
            showStatus('Error', "Request failed: " + textStatus + '<br>' + error, 'danger');
            // Re-enable the process button
            $('#process-btn').prop('disabled', false);
        });
        ajaxRequest.always(function() {});
    }
    var manuallyTargetCanvas = document.getElementById('manuallyTargetCanvas')
    var manuallyTargetCanvasID = new bootstrap.Offcanvas(manuallyTargetCanvas)
    $('#manuallyTargetCanvas').on('hidden.bs.offcanvas', function() {
        // Trigger a custom event when the offcanvas is hidden
        $('#manuallyTargetCanvasDetail').html('');
    });

    function showDetails(logId) {
        $.ajax({
            url: '<?php echo base_url() ?>sessions/review/getProcessedLog',
            type: 'post',
            data: {
                "logId": logId,
            },
            success: function(response) {
                if (response.success) {
                    $('#manuallyTargetCanvasDetail').html(response.html);

                    // Set modal title (optional)
                    $('#rulesCanvasTitle').text("Resolve Target Conflict");

                    // Show the modal (Full-screen Offcanvas)
                    manuallyTargetCanvasID.show();
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>