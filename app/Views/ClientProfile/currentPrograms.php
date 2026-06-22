<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .domain-card {
        margin-bottom: 1rem;
        border: 1px solid #e9ecef;
        background: #fff;
        border-radius: 6px;
    }

    .domain-header {
        background-color: #f8f9fc;
        padding: 0.75rem 1rem;
        cursor: pointer;
        position: relative;
    }

    .domain-header h5 {
        color: #2074BA !important;
    }

    .domain-header:hover {
        background-color: #eef2f7;
    }

    .domain-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 2px;
        background: #2074BA;
        border-radius: 0;
    }

    .goal-header {
        background-color: #fdfdfd;
        border-top: 1px solid #e9ecef;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .goal-header:hover {
        background-color: #f8f9fa;
    }

    .goal-progress {
        height: 1px;
        background: #198754;
        margin-top: 4px;
        margin-bottom: 12px;
    }

    .goal-targets-wrap {
        margin-left: 1.5rem;
        padding-left: 0.75rem;
        border-left: 2px solid #eef2f7;
    }

    @media (max-width: 767.98px) {
        .goal-targets-wrap {
            margin-left: 0.75rem;
            padding-left: 0.5rem;
        }
    }

    .goal-block + .goal-block {
        border-top: 1px solid #e9ecef;
        padding-top: 12px;
        margin-top: 12px;
    }

    .file-manager-content-scroll {
        padding-bottom: 150px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }

    .toggle-icon {
        transition: transform .2s ease;
    }

    .collapsed .toggle-icon {
        transform: rotate(-90deg);
    }


    svg .s0 {
        fill: #2074BA !important;
        opacity: .1;
    }

    /* Only apply when 6 columns are in one row (xl and up) */
    @media (min-width: 1200px) {
        .summary-text h6 {
            /* keep title on one line, shrink slightly if needed */
            font-size: clamp(0.8rem, 0.9vw, 0.95rem);
            line-height: 1.1;
            margin-bottom: 0.35rem !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .summary-text .fs-12 {
            /* keep value on one line, shrink slightly if needed */
            font-size: clamp(0.78rem, 0.85vw, 0.9rem);
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            /* ensures ellipsis works reliably */
            width: 100%;
            /* ensures ellipsis has a containing width */
        }
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <!-- ===================== Program Summary (Updated UI) ===================== -->
    <div class="row g-3 mb-4">
        <!-- Program Start -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Program Start</h6>
                            <div class="fs-12 fw-normal"><?= !empty($programData['program_summary']['program_start']) ? app_date($programData['program_summary']['program_start']) : '-'; ?>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="program_start_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domains -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">

                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Domains</h6>
                            <div class="fs-12 fw-normal">
                                <?= $programData['program_summary']['total_domains'] - $programData['program_summary']['total_domains_mastered']; ?> Active /
                                <?= $programData['program_summary']['total_domains_mastered']; ?> Mastered
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="domains_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">

                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Goals</h6>
                            <div class="fs-12 fw-normal">
                                <?= $programData['program_summary']['total_goals'] - $programData['program_summary']['total_goals_mastered']; ?> Active /
                                <?= $programData['program_summary']['total_goals_mastered']; ?> Mastered
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="goals_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Targets -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">

                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Targets</h6>
                            <div class="fs-12 fw-normal">
                                <?= $programData['program_summary']['total_targets'] - $programData['program_summary']['total_targets_mastered']; ?> Active /
                                <?= $programData['program_summary']['total_targets_mastered']; ?> Mastered
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="targets_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Alerts -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">

                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Program Alerts</h6>
                            <div class="fs-12 fw-normal">
                                <?= $programData['program_summary']['program_changes']; ?> Changes /
                                <?= $programData['program_summary']['program_changes_alerts']; ?> Alerts
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="alerts_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duration -->
        <div class="col-xl-2 col-md-4 col-sm-12">
            <div class="card card-animate overflow-hidden">
                <div class="position-absolute start-0" style="z-index: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">

                        <path class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                    </svg>
                </div>
                <div class="card-body" style="z-index:1;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden text-center summary-text">
                            <h6 class=" text-uppercase mb-2">Program Duration</h6>
                            <div class="fs-12 fw-normal"><?= $programData['program_summary']['days']; ?></div>
                        </div>
                        <div class="flex-shrink-0">
                            <div id="duration_chart" class="apex-charts" style="min-height:89px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ===================== Domains ===================== -->
    <?php foreach ($programData['domains'] as $domain):
        $masteredPercent = round(($domain['mastered_targets'] / $domain['total_targets']) * 100);
    ?>
        <div class="card domain-card" style="border: 1px solid grey; border-radius:0px">
            <div style="border-bottom: 1px solid grey;" class="domain-header d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" data-bs-target="#domain<?= $domain['domain_id']; ?>" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-arrow-down-s-line toggle-icon"></i>
                    <div>
                        <h5 class="mb-0"><?= $domain['domain_name']; ?></h5>
                        <small class="text-muted">Introduced: <?= app_date($domain['introduced_on']); ?></small>
                    </div>
                </div>
                <div class="text-end">
                    Goals: <?= $domain['mastered_goals']; ?>/<?= $domain['total_goals']; ?> |
                    Targets: <?= $domain['mastered_targets']; ?>/<?= $domain['total_targets']; ?> |
                    Status: <?= $domain['is_mastered'] ? '<span class="text-success">Mastered</span>' : '<span class="text-warning">In Progress</span>'; ?>
                </div>
                <div class="domain-progress" style="width:<?= $masteredPercent; ?>%;"></div>
            </div>

            <div id="domain<?= $domain['domain_id']; ?>" class="collapse">
                <div class="card-body">
                    <?php foreach ($domain['goals'] as $goal):
                        $goalPercent = round(($goal['mastered_targets'] / $goal['total_targets']) * 100);
                    ?>
                        <div class="goal-header collapsed" data-bs-toggle="collapse" data-bs-target="#goal<?= $domain['domain_id']; ?>-<?= $goal['goal_id']; ?>" aria-expanded="false">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-arrow-down-s-line toggle-icon"></i>
                                <div>
                                    <strong><?= $goal['goal_name']; ?></strong>
                                    <small class="text-muted d-block">Introduced: <?= !empty($goal['introduced_on']) ? app_date($goal['introduced_on']) : '-'; ?></small>
                                </div>
                            </div>
                            <div class="text-muted">
                                Targets: <?= $goal['mastered_targets']; ?>/<?= $goal['total_targets']; ?> |
                                Avg Mastery: <?= $goal['average_mastery_days']; ?> days |
                                Status: <?= $goal['is_mastered'] ? '<span class="text-success">Mastered</span>' : '<span class="text-warning">In Progress</span>'; ?>
                            </div>
                        </div>
                        <div class="goal-progress" style="width:<?= $goalPercent; ?>%;"></div>

                        <div id="goal<?= $domain['domain_id']; ?>-<?= $goal['goal_id']; ?>" class="collapse">
                            <div class="goal-targets-wrap">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mt-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Target</th>
                                                <th>Status</th>
                                                <th>Introduced</th>
                                                <th>Mastered</th>
                                                <th>Duration</th>
                                                <th>Sessions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($goal['targets'] as $t): ?>
                                                <tr>
                                                    <td><?= $t['target_name']; ?></td>
                                                    <td><?= $t['status'] === 'Mastered'
                                                            ? '<i class="ri-checkbox-circle-line align-middle text-success me-2"></i><span class="">Mastered</span>'
                                                            : '<i class="ri-refresh-line align-middle text-warning me-2"></i><span class="">In Progress</span>'; ?></td>
                                                    <td><?= $t['introduced_on'] ? app_date($t['introduced_on']) : '-'; ?></td>
                                                    <td><?= $t['mastered_on'] ? app_date($t['mastered_on']) : '-'; ?></td>
                                                    <td><?= $t['duration_days']; ?></td>
                                                    <td><?= $t['sessions_count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>



<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    // Debounce helper
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function initOrRecalcSimpleBars() {
        document.querySelectorAll("[data-simplebar]").forEach(el => {
            // Ensure SimpleBar instance exists
            if (!el.SimpleBar && window.SimpleBar) {
                new SimpleBar(el, {
                    autoHide: false
                }); // or your preferred options
            }
            // Recalculate if present
            if (el.SimpleBar) el.SimpleBar.recalculate();
        });
    }

    const recalcSimpleBars = debounce(initOrRecalcSimpleBars, 60);

    // Existing: recalc on Bootstrap tab shown
    document.addEventListener('shown.bs.tab', recalcSimpleBars);

    // ✅ NEW: recalc when a collapse finishes opening/closing (height changed)
    document.addEventListener('shown.bs.collapse', recalcSimpleBars);
    document.addEventListener('hidden.bs.collapse', recalcSimpleBars);

    // (Optional) If you use modals that inject content heights
    document.addEventListener('shown.bs.modal', recalcSimpleBars);
    document.addEventListener('hidden.bs.modal', recalcSimpleBars);

    // Existing: on full load (after charts/styles settle)
    window.addEventListener('load', () => {
        setTimeout(recalcSimpleBars, 300);
    });

    // Existing: on resize
    window.addEventListener('resize', recalcSimpleBars);

    // ✅ Optional safety net: observe DOM mutations inside the scroll area
    //    (useful when tables render async or charts update)
    const scrollHost = document.querySelector('.file-manager-content-scroll');
    if (scrollHost && window.MutationObserver) {
        const mo = new MutationObserver(recalcSimpleBars);
        mo.observe(scrollHost, {
            childList: true,
            subtree: true
        });
    }

    // ✅ Optional: if you render ApexCharts, recalc after they mount/update
    // Example if you have chart instances:
    // chart.on('mounted', recalcSimpleBars);
    // chart.on('updated', recalcSimpleBars);
</script>
<?= $this->endSection() ?>
