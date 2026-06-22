<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h6 class="mb-sm-0">
        <div class="hstack gap-1 flex-wrap">
            <div> <span class="fs-5 text text primary">Session</span> <i class="ri-arrow-right-fill text-primary"></i> </div>
            <div><i class="ri-calendar-event-line align-bottom me-1"></i><span class="fw-medium"><?= app_date($session->session_date) ?> (<?= $session->start_time ?>  <i class="ri-arrow-right-fill text-primary"></i> <?= $session->end_time ?>)</span></div>
            <div><i class="mdi mdi-account-child-outline"></i>&nbsp;<?= $client->internal_mrn; ?></div>
            <div class="vr"></div>
            <div><span class="fw-medium"><i class="mdi mdi-account-clock-outline align-bottom me-1"></i><?= $session->instructor_name(); ?></span></div>
            <div class="vr"></div>
            <div><span class="fw-medium"><i class="mdi mdi-account-group-outline align-middle me-1"></i><?= $session->supervisor_name(); ?></span></div>
            <div>
                <span class="fw-medium">
                    <?php
                    // Check session status and display the appropriate badge
                    switch ($session->status) {
                        case 1:
                            echo '<span class="badge bg-primary-subtle text-primary">In Progress</span>';
                            break;
                        case 2:
                            echo '<span class="badge bg-primary-subtle text-primary">In Review</span>';
                            break;
                        case 3:
                            echo '<span class="badge bg-success-subtle text-success">Processed</span>';
                            break;
                        case 4:
                            echo '<span class="badge bg-danger-subtle text-danger">Partially Processed</span>';
                            break;
                        default:
                            echo '<span class="badge bg-secondary-subtle text-secondary">Unknown Status</span>';
                            break;
                    }
                    ?>
                </span>
            </div>
    </h6>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active"><?= $section_name ?></li>
        </ol>
    </div>
</div>