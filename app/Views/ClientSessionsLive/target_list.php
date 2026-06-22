<div id="view-identifier" data-view="target_list"></div>
<div class="row mb-4">
    <div class="col-sm order-3 order-sm-2 mt-3 mt-sm-0">
        <h5 class="fw-semibold mb-0">Program Target List for "<span class="text-primary fw-medium fst-italic">[<?= $domain->domain_code; ?>][<?= $goal->goal_code; ?>]</span> "</h5>
    </div>
    <div class="col-auto order-2 order-sm-3 ms-auto">
        <div class="hstack gap-2">
            <div class="btn-group" role="group" aria-label="Basic example">
                <a class="btn btn-icon fw-semibold btn-soft-info  " href="#targetSlider" role="button" data-bs-slide="prev"><i class="ri-arrow-left-line"></i></a>&nbsp;
                <a class="btn btn-icon fw-semibold btn-soft-info  " href="#targetSlider" role="button" data-bs-slide="next"><i class="ri-arrow-right-line"></i></a>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-12">
        <!-- Target list will be rendered here -->
        <div class="">
            <div id="targetSlider" class="carousel slide" data-bs-interval="false">
                <div class="carousel-inner" role="listbox">
                    <?php
                    if (empty($targets))
                        echo "There are no targets scheduled for this goal today.";
                    ?>
                    <?php $first = 0;
                    foreach ($targets as $target) : ?>
                        <?php
                        $activation_days = $target['additional_data']['current_rule']['activation_days'] ?? null;
                        $last_session_date = $target['last_session_date'] ?? null;

                        // Only proceed with the date comparison if both variables are set (not null)
                        if ($activation_days !== null && $last_session_date !== null) {
                            $days_since_last_session = getDaysDifference($last_session_date);

                            // Skip if the number of days since the last session is less than the activation days
                            if ($days_since_last_session < $activation_days) {
                                continue;
                            }
                        }
                        $first++;
                        ?>

                        <div class="card border carousel-item <?= $first == 1 ? 'active' : '' ?> ">
                            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                                <h6 class="card-title mb-0 flex-grow-1"><?= $target['target_name']; ?></h6>

                                <?php $alertCount = (int) ($target['program_alert_count'] ?? 0); ?>
                                <span class="badge <?= $alertCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted' ?>"
                                    title="<?= $alertCount > 0 && !empty($target['last_alert_date']) ? 'Last alert: ' . app_date($target['last_alert_date']) : 'No program alerts' ?>">
                                    <i class="ri-alarm-warning-line align-bottom"></i>
                                    Alerts: <?= $alertCount ?>
                                    <?php if ($alertCount > 0 && !empty($target['last_alert_date'])): ?>
                                        (<?= app_date($target['last_alert_date']) ?>)
                                    <?php endif; ?>
                                </span>

                                <?php $changeCount = (int) ($target['program_change_count'] ?? 0); ?>
                                <span class="badge <?= $changeCount > 0 ? 'bg-warning-subtle text-warning' : 'bg-light text-muted' ?>"
                                    title="<?= $changeCount > 0 && !empty($target['last_change_date']) ? 'Last change: ' . app_date($target['last_change_date']) : 'No program changes' ?>">
                                    <i class="ri-exchange-line align-bottom"></i>
                                    Changes: <?= $changeCount ?>
                                    <?php if ($changeCount > 0 && !empty($target['last_change_date'])): ?>
                                        (<?= app_date($target['last_change_date']) ?>)
                                    <?php endif; ?>
                                </span>
                                <span class="badge bg-primary-subtle text-primary"><?= $target['phase_name'] ?></span>
                                <button type="button"
                                    class="btn btn-sm btn-icon btn-soft-secondary view-target-history"
                                    data-client-id="<?= $client_id; ?>"
                                    data-domain-id="<?= $domain->id; ?>"
                                    data-goal-id="<?= $goal->id; ?>"
                                    data-target-id="<?= $target['target_id']; ?>"
                                    data-probe-set-id="<?= $target['client_probe_set_id']; ?>"
                                    data-target-name="<?= esc($target['target_name']); ?>"
                                    title="Target History">
                                    <i class="ri-history-line"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                <div class="row">
                                    <?= $target['input_html'] ?> <!-- Rendered Inputs from Controller -->

                                </div>
                                </p>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-12 col-md-10 col-lg-10 text-start">
                                    </div>
                                    <div class="col-sm-12 col-md-2 col-lg-2 text-end">
                                        <button
                                            data-client-id="<?= $client_id; ?>"
                                            data-session-id="<?= $session_id; ?>"
                                            data-domain-id="<?= $domain->id; ?>"
                                            data-goal-id="<?= $goal->id; ?>"
                                            data-target-id="<?= $target['target_id']; ?>"
                                            data-probe-set-id="<?= $target['client_probe_set_id']; ?>"
                                            data-current-phase-id="<?= $target['current_phase_id']; ?>"
                                            class="btn btn-primary save-button-all-probe"><i class="ri-save-2-line"></i> Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

            </div>

        </div>
    </div>
</div>
<!-- Spinner container -->
<div id="spinner-container" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center d-none" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5); display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>