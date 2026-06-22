<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .file-manager-content {
        background: var(--vz-body-bg);
    }

    .dashboard-note-preview {
        display: -webkit-box;
        max-width: 100%;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        white-space: normal;
        line-height: 1.35;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
    }

    .dashboard-note-preview.is-expanded {
        display: block;
        -webkit-line-clamp: unset;
        line-clamp: unset;
        -webkit-box-orient: initial;
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
        word-break: break-word;
    }

    .dashboard-notes-table td,
    .dashboard-notes-table th {
        white-space: normal;
        vertical-align: top;
    }

    .dashboard-notes-table td.dashboard-note-date,
    .dashboard-notes-table th.dashboard-note-date {
        white-space: nowrap !important;
        width: 1%;
    }

    .dashboard-note-toggle {
        font-size: 12px;
        line-height: 1.2;
    }

    .active-summary-line {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.92rem;
        color: #4b5563;
    }

    .active-summary-sep {
        color: #9ca3af;
    }

    .active-tree-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-bottom: 0.6rem;
    }

    .active-tree-shell {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        padding: 0.65rem 0.75rem;
    }

    .active-tree {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tree-domain-node + .tree-domain-node {
        margin-top: 0.35rem;
    }

    .domain-details > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 6px;
        padding: 0.4rem 0.5rem;
        font-weight: 600;
        color: #1f2937;
    }

    .domain-details > summary::-webkit-details-marker {
        display: none;
    }

    .domain-details > summary::before {
        content: '+';
        display: inline-block;
        width: 1rem;
        text-align: center;
        color: #2074BA;
        font-weight: 700;
    }

    .domain-details[open] > summary::before {
        content: '-';
    }

    .domain-details > summary:hover {
        background: #f8fafc;
    }

    .tree-domain-count {
        color: #6b7280;
        font-weight: 500;
    }

    .tree-goals {
        margin: 0.3rem 0 0.2rem 1.35rem;
        padding-left: 0.85rem;
        border-left: 1px dashed #d1d5db;
        list-style: none;
    }

    .tree-goal-node + .tree-goal-node {
        margin-top: 0.2rem;
    }

    .goal-details > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        color: #374151;
        padding: 0.2rem 0;
    }

    .goal-details > summary::-webkit-details-marker {
        display: none;
    }

    .goal-details > summary::before {
        content: '-';
        color: #9ca3af;
        display: inline-block;
        width: 0.85rem;
        text-align: center;
    }

    .goal-details[open] > summary::before {
        content: '=';
        color: #2074BA;
    }

    .tree-goal-count {
        color: #6b7280;
        font-size: 0.88rem;
    }

    .tree-targets {
        margin: 0.15rem 0 0.2rem 1.25rem;
        padding-left: 0.8rem;
        border-left: 1px dotted #d1d5db;
        list-style: none;
    }

    .tree-target-item {
        color: #4b5563;
        font-size: 0.9rem;
        padding: 0.12rem 0;
    }

    .tree-target-item::before {
        content: '-';
        color: #c0c4cb;
        margin-right: 0.35rem;
    }

    .active-empty {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        color: #6b7280;
        background: #fcfcfd;
    }

    @media (max-width: 576px) {
        .active-tree-shell {
            padding: 0.55rem 0.55rem;
        }

        .domain-details > summary {
            padding: 0.35rem 0.35rem;
        }
    }
</style>
<?= $this->endSection() ?>
<?php
$dashboardWidgets = $dashboardWidgets ?? [];
$canShowKeyInformation = !empty($dashboardWidgets['keyInformation']);
$canShowSessionQuality = !empty($dashboardWidgets['sessionQuality']);
$canShowCumulativeGraph = !empty($dashboardWidgets['cumulativeGraph']);
$canShowActiveTargets = !empty($dashboardWidgets['activeTargets']);
$canShowMandsGraphs = !empty($dashboardWidgets['mandsGraphs']);
$canShowBehaviourReduction = !empty($dashboardWidgets['behaviourReduction']);
$canShowSessionOverview = !empty($dashboardWidgets['sessionOverview']);
$canShowWowMoments = !empty($dashboardWidgets['wowMoments']);
$dashboardActiveProgramData = is_array($activeProgramData ?? null)
    ? $activeProgramData
    : ['program_summary' => [], 'domains' => []];
$dashboardKeyInformation = is_array($keyInformation ?? null) ? $keyInformation : [];
$keyInformationField = static function (array $data, string $key): string {
    $value = trim((string) ($data[$key] ?? ''));

    return $value !== '' ? $value : '-';
};
?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card crm-widget">
            <div class="card-body p-0">
                <div class="row row-cols-xxl-6 row-cols-md-2 row-cols-1 g-0">

                    <?php foreach (($summaryMetrics ?? []) as $metric) : ?>
                        <div class="col">
                            <div class="py-4 px-3">
                                <h5 class="text-black fs-13"><?= esc((string) ($metric['label'] ?? '')) ?></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="<?= esc((string) ($metric['icon'] ?? '')) ?> fs-3 text-<?= esc((string) ($metric['color'] ?? 'primary')) ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-1">
                                            <span class="text-<?= esc((string) ($metric['color'] ?? 'primary')) ?>"><?= esc((string) ($metric['value'] ?? '')) ?></span>
                                        </h2>
                                        <p class="text-black mb-0"><?= esc((string) ($metric['period'] ?? '')) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div><!-- end row -->
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div><!-- end row -->

<?php if ($canShowSessionQuality || $canShowCumulativeGraph): ?>
<div class="row mt-3">
    <?php if ($canShowSessionQuality): ?>
    <div class="<?= $canShowCumulativeGraph ? 'col-xl-4' : 'col-xl-12' ?>">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Session Quality Rating</h4>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="session_rating_chart" data-colors='["--vz-danger", "--vz-warning", "--vz-success", "--vz-danger", "--vz-info"]' class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canShowCumulativeGraph): ?>
    <div class="<?= $canShowSessionQuality ? 'col-xl-8' : 'col-xl-12' ?>">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Curmulative gaph accross all domains</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <span id="cpd-legend-toggle-skills">
                            <img class="img" src="/assets/images/legend-black.png" alt="" width="35">
                            <span>Skills Retained</span>
                        </span>
                        &nbsp; &nbsp; &nbsp;
                        <span id="cpd-legend-toggle-doi">
                            <img class="img" src="/assets/images/legend-blue.png" alt="" width="35">
                            <span>Degrees of independence</span>
                        </span>
                    </div>
                </div>
                <hr style="border:none">
                <canvas id="cpd-cumulative-chart" class="chart_content" height="100"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php if ($canShowKeyInformation): ?>
<div class="row mt-3">
    <div class="col-xxl-12 col-xl-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-information-line align-middle me-2"></i>Key Information
                </h4>
            </div>
            <div class="card-body bg-light-subtle rounded-3 p-3">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="p-3 border rounded-3 h-100">
                            <div class="text-black small mb-1">Schedule of Reinforcement</div>
                            <div class="text-black text-break"><?= nl2br(esc($keyInformationField($dashboardKeyInformation, 'schedule_of_reinforcement'))) ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 border rounded-3 h-100">
                            <div class="text-black small mb-1">General Comments</div>
                            <div class="text-black text-break"><?= nl2br(esc($keyInformationField($dashboardKeyInformation, 'general_comment'))) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canShowActiveTargets): ?>
<div class="row mt-3">
    <div class="col-xxl-12 col-xl-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-clipboard-line align-middle me-2"></i>Active Targets
                </h4>
                <div class="active-summary-line">
                    <span>Active Domains: <?= (int) ($dashboardActiveProgramData['program_summary']['total_domains_active'] ?? 0) ?></span>
                    <span class="active-summary-sep">|</span>
                    <span>Active Goals: <?= (int) ($dashboardActiveProgramData['program_summary']['total_goals_active'] ?? 0) ?></span>
                    <span class="active-summary-sep">|</span>
                    <span>Active Targets: <?= (int) ($dashboardActiveProgramData['program_summary']['total_targets_active'] ?? 0) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($dashboardActiveProgramData['domains'])): ?>
                    <div class="active-empty">
                        No Active Program Items. All introduced targets are mastered or no targets are introduced yet.
                    </div>
                <?php else: ?>
                    <div class="active-tree-toolbar">
                        <button type="button" id="dashboard_active_targets_expand_all" class="btn btn-sm btn-light">Expand All</button>
                        <button type="button" id="dashboard_active_targets_collapse_all" class="btn btn-sm btn-light">Collapse All</button>
                    </div>

                    <div class="active-tree-shell">
                        <ul class="active-tree">
                            <?php foreach ($dashboardActiveProgramData['domains'] as $domain): ?>
                                <?php $domainGoals = $domain['goals'] ?? []; ?>
                                <li class="tree-domain-node">
                                    <details class="domain-details">
                                        <summary>
                                            <span><?= esc($domain['domain_name']) ?></span>
                                            <span class="tree-domain-count">(<?= (int) count($domainGoals) ?>)</span>
                                        </summary>
                                        <ul class="tree-goals">
                                            <?php foreach ($domainGoals as $goal): ?>
                                                <?php $goalTargets = $goal['targets'] ?? []; ?>
                                                <li class="tree-goal-node">
                                                    <details class="goal-details">
                                                        <summary>
                                                            <span><?= esc($goal['goal_name']) ?></span>
                                                            <span class="tree-goal-count">(<?= (int) count($goalTargets) ?>)</span>
                                                        </summary>
                                                        <ul class="tree-targets">
                                                            <?php foreach ($goalTargets as $target): ?>
                                                                <li class="tree-target-item"><?= esc($target['target_name'] ?? '') ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </details>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($canShowMandsGraphs): ?>
<div class="row mt-3">
    <div class="col-xxl-12 col-xl-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-message-2-line align-middle me-2"></i>Communication (Mands)
                </h4>
                <div class="flex-shrink-0">
                    <div class="dropdown card-header-dropdown">

                    </div>
                </div>
            </div>
            <div class="card-body px-0">
                <div class="row g-3 mx-0">
                    <div class="col-12 col-lg-6">
                        <h6 id="cpd-total-mands-title" class="text-black fs-12 fw-semibold px-3 mb-3">Total Mands</h6>
                        <canvas id="cpd-total-mands-chart" class="chart_content" height="140"></canvas>
                    </div>
                    <div class="col-12 col-lg-6">
                        <h6 id="cpd-mand-variety-title" class="text-black fs-12 fw-semibold px-3 mb-3">Variety of Mands</h6>
                        <canvas id="cpd-mand-variety-chart" class="chart_content" height="140"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($canShowBehaviourReduction): ?>
<div class="row mt-3">
    <div class="col-xxl-12 col-xl-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-line-chart-line align-middle me-2"></i>Behaviour Reduction
                </h4>
                <div class="flex-shrink-0">
                    <div class="dropdown card-header-dropdown">

                    </div>
                </div>
            </div>
            <div class="card-body px-0">
                <div class="row g-3 mx-0">
                    <div class="col-12 col-lg-6">
                        <h6 id="cpd-pb-frequency-title" class="text-black fs-12 fw-semibold px-3 mb-3">Frequency of Problem Behavior</h6>
                        <canvas id="cpd-pb-frequency-chart" class="chart_content" height="140"></canvas>
                    </div>
                    <div class="col-12 col-lg-6">
                        <h6 id="cpd-pb-duration-title" class="text-black fs-12 fw-semibold px-3 mb-3">Total Duration of Problem Behavior</h6>
                        <canvas id="cpd-pb-duration-chart" class="chart_content" height="140"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php endif; ?>

<?php if ($canShowSessionOverview || $canShowWowMoments): ?>
<div class="row mt-3">
    <?php if ($canShowSessionOverview): ?>
    <div class="<?= $canShowWowMoments ? 'col-xxl-6 col-xl-6' : 'col-xxl-12 col-xl-12' ?>">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-calendar-check-line align-middle me-2"></i>Session Overview
                </h4>
                <div class="flex-shrink-0">
                    <span class="badge bg-light text-black fs-11">Last 5</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-card">
                    <table class="table align-middle mb-0 dashboard-notes-table">
                        <thead>
                            <tr>
                                <th scope="col" class="text-black fw-semibold">Date</th>
                                <th scope="col" class="text-black fw-semibold">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sessionOverview)): ?>
                                <?php foreach ($sessionOverview as $index => $entry): ?>
                                    <?php
                                    $noteId = 'session-overview-note-' . $index;
                                    ?>
                                    <tr>
                                        <td class="no-wrap fw-semibold dashboard-note-date"><?= esc($entry['date']) ?></td>
                                        <td class="w-100">
                                            <div id="<?= esc($noteId) ?>" class="dashboard-note-preview"><?= esc($entry['note']) ?></div>
                                            <button type="button" class="btn btn-link text-decoration-none p-0 mt-1 dashboard-note-toggle js-dashboard-note-toggle d-none" data-target-id="<?= esc($noteId) ?>">Read more</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-black">No session overview notes found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canShowWowMoments): ?>
    <div class="<?= $canShowSessionOverview ? 'col-xxl-6 col-xl-6' : 'col-xxl-12 col-xl-12' ?>">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-star-smile-line align-middle me-2"></i>Wow Moments
                </h4>
                <div class="flex-shrink-0">
                    <span class="badge bg-success-subtle text-success fs-11">Highlights</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-card">
                    <table class="table align-middle mb-0 dashboard-notes-table">
                        <thead>
                            <tr>
                                <th scope="col" class="text-black fw-semibold">Date</th>
                                <th scope="col" class="text-black fw-semibold">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($wowMoments)): ?>
                                <?php foreach ($wowMoments as $index => $entry): ?>
                                    <?php
                                    $noteId = 'wow-moment-note-' . $index;
                                    ?>
                                    <tr>
                                        <td class="no-wrap fw-semibold dashboard-note-date"><?= esc($entry['date']) ?></td>
                                        <td class="w-100">
                                            <div id="<?= esc($noteId) ?>" class="dashboard-note-preview"><?= esc($entry['note']) ?></div>
                                            <button type="button" class="btn btn-link text-decoration-none p-0 mt-1 dashboard-note-toggle js-dashboard-note-toggle d-none" data-target-id="<?= esc($noteId) ?>">Read more</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-black">No wow moments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
 

<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="/assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
    function getChartColorsArray(chartId) {
        if (document.getElementById(chartId) !== null) {
            var colors = document.getElementById(chartId).getAttribute("data-colors");

            if (colors) {
                colors = JSON.parse(colors);
                return colors.map(function(value) {
                    var newValue = value.replace(" ", "");

                    if (newValue.indexOf(",") === -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        return color ? color.trim() : newValue;
                    }

                    var val = value.split(",");

                    if (val.length === 2) {
                        var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                        return "rgba(" + rgbaColor + "," + val[1] + ")";
                    }

                    return newValue;
                });
            }
        }

        return [];
    }

    document.addEventListener("DOMContentLoaded", function() {
        const sessionQualityChart = <?= json_encode(
                                    $sessionQualityChart ?? ['labels' => [], 'series' => []],
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
                                ) ?>;
        const clientId = <?= json_encode((string) ($client->id ?? '')) ?>;
        const csrfToken = <?= json_encode(csrf_hash()) ?>;
        const chartInstances = {};

        if (typeof $ !== "undefined") {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                }
            });
        }

        const dashboardActiveExpandBtn = document.getElementById("dashboard_active_targets_expand_all");
        const dashboardActiveCollapseBtn = document.getElementById("dashboard_active_targets_collapse_all");
        const dashboardActiveTreeNodes = document.querySelectorAll(".domain-details, .goal-details");

        if (dashboardActiveExpandBtn) {
            dashboardActiveExpandBtn.addEventListener("click", function() {
                dashboardActiveTreeNodes.forEach(function(node) {
                    node.setAttribute("open", "open");
                });
            });
        }

        if (dashboardActiveCollapseBtn) {
            dashboardActiveCollapseBtn.addEventListener("click", function() {
                dashboardActiveTreeNodes.forEach(function(node) {
                    node.removeAttribute("open");
                });
            });
        }

        function renderSessionQualityChart() {
            const element = document.querySelector("#session_rating_chart");

            if (!element || typeof ApexCharts === "undefined") {
                return;
            }

            const ratingLabels = Array.isArray(sessionQualityChart.labels) ? sessionQualityChart.labels : [];
            const ratingData = Array.isArray(sessionQualityChart.series)
                ? sessionQualityChart.series.map(function(value) {
                    return Number(value || 0);
                })
                : [];
            const total = ratingData.reduce(function(sum, value) {
                return sum + value;
            }, 0);

            const options = {
                series: ratingData,
                labels: ratingLabels,
                chart: {
                    type: "pie",
                    height: 330
                },
                colors: getChartColorsArray("session_rating_chart"),
                legend: {
                    position: "bottom"
                },
                tooltip: {
                    y: {
                        formatter: function(value, opts) {
                            const label = opts.globals.labels[opts.seriesIndex];
                            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : "0.0";

                            return "<div>" + label + ": " + value + " sessions (" + percent + "%)</div>";
                        },
                        title: {
                            formatter: function() {
                                return "";
                            }
                        }
                    }
                }
            };

            if (chartInstances.sessionRating) {
                chartInstances.sessionRating.destroy();
            }

            chartInstances.sessionRating = new ApexCharts(element, options);
            chartInstances.sessionRating.render();
        }

        const mandsConfig = {
            type: "line",
            data: [],
            options: {
                tooltips: {
                    intersect: false
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 5,
                            callback: function(value) {
                                if (value < 0) {
                                    return "";
                                }

                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Total number of mands"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90,
                            callback: function(value) {
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Dates"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            }
        };
        const varietyConfig = {
            type: "line",
            data: [],
            options: {
                tooltips: {
                    intersect: false
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 5,
                            callback: function(value) {
                                if (value < 0) {
                                    return "";
                                }

                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Variety of Mands"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Dates"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            }
        };
        const frequencyPbConfig = {
            type: "line",
            data: [],
            options: {
                tooltips: {
                    intersect: false
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 5,
                            callback: function(value) {
                                if (value < 0) {
                                    return "";
                                }

                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Frequency of Problem Behavior"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Dates"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            }
        };
        const durationPbConfig = {
            type: "line",
            data: [],
            options: {
                tooltips: {
                    intersect: false
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.2,
                            suggestedMax: 5,
                            callback: function(value) {
                                if (value < 0) {
                                    return "";
                                }

                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Total Duration of Problem Behavior  (Minutes)"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Dates"
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            }
        };

        function findDatasetIndexByKeyword(chart, keyword) {
            if (!chart || !chart.data || !Array.isArray(chart.data.datasets)) {
                return -1;
            }

            const term = keyword.toLowerCase();
            return chart.data.datasets.findIndex(function(dataset) {
                return (dataset.label || "").toLowerCase().includes(term);
            });
        }

        function applyCumulativeLegendDatasetVisibility(chart, skillsVisible, doiVisible) {
            const skillsIndex = findDatasetIndexByKeyword(chart, "skills");
            const doiIndex = findDatasetIndexByKeyword(chart, "degree");

            if (skillsIndex > -1) {
                chart.getDatasetMeta(skillsIndex).hidden = !skillsVisible;
            }

            if (doiIndex > -1) {
                chart.getDatasetMeta(doiIndex).hidden = !doiVisible;
            }
        }

        function applyNoSessionPointStyle(chart, noSessionPointImage) {
            const noSessionIndex = findDatasetIndexByKeyword(chart, "no session");

            if (noSessionIndex > -1) {
                chart.data.datasets[noSessionIndex].pointStyle = noSessionPointImage;
            }
        }

        function buildCumulativeGraph() {
            const element = document.getElementById("cpd-cumulative-chart");

            if (!element || typeof Chart === "undefined") {
                return null;
            }

            const config = {
                type: "line",
                data: [],
                options: {
                    tooltips: {
                        intersect: false
                    },
                    annotation: {
                        annotations: []
                    },
                    legend: {
                        display: false,
                        usePointStyle: true,
                        pointStyle: "circle",
                        labels: {
                            filter: function(item) {
                                return !item.text.includes("No Session");
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                suggestedMin: 0
                            },
                            afterDataLimits: function(scale) {
                                scale.max += 10;
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "Cumulative Skills Retained Across All Domains"
                            },
                            gridLines: {
                                display: true,
                                drawBorder: true,
                                drawOnChartArea: false
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                autoSkip: true,
                                callback: function(value, index) {
                                    const noSessionDataset = this.chart.data.datasets[2];

                                    if (noSessionDataset && noSessionDataset.data[index] === 0) {
                                        return "";
                                    }

                                    return value;
                                }
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "Week Ending"
                            },
                            gridLines: {
                                display: true,
                                drawBorder: true,
                                drawOnChartArea: false
                            }
                        }]
                    },
                    elements: {
                        point: {
                            radius: 3
                        }
                    }
                }
            };

            return new Chart(element, config);
        }

        function loadCumulativeGraph(cumulativeGraph, noSessionPointImage, skillsVisible, doiVisible) {
            if (!cumulativeGraph || typeof $ === "undefined") {
                return;
            }

            $.ajax({
                url: "/graphs/cumulative",
                type: "post",
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                },
                data: {
                    client_id: clientId,
                    start_date: "",
                    end_date: ""
                }
            }).done(function(response) {
                if (!response || response.status !== "success") {
                    showDashboardGraphError("Unable to load cumulative graph.");
                    return;
                }

                const cumulativeData = response.data || {};
                cumulativeGraph.data = cumulativeData.graph_data;
                applyNoSessionPointStyle(cumulativeGraph, noSessionPointImage);
                cumulativeGraph.options.annotation.annotations = cumulativeData.phaseline || [];
                applyCumulativeLegendDatasetVisibility(cumulativeGraph, skillsVisible, doiVisible);
                cumulativeGraph.update();
            }).fail(function() {
                showDashboardGraphError("Unable to load cumulative graph.");
            });
        }

        function showDashboardGraphError(message) {
            if (typeof showAlert === "function") {
                showAlert("", message, "error");
            }
        }

        const totalMandsElement = document.getElementById("cpd-total-mands-chart");
        const varietyElement = document.getElementById("cpd-mand-variety-chart");
        const frequencyPbElement = document.getElementById("cpd-pb-frequency-chart");
        const durationPbElement = document.getElementById("cpd-pb-duration-chart");
        const totalMandsGraph = totalMandsElement && typeof Chart !== "undefined" ? new Chart(totalMandsElement, mandsConfig) : null;
        const varietyGraph = varietyElement && typeof Chart !== "undefined" ? new Chart(varietyElement, varietyConfig) : null;
        const frequencyPbGraph = frequencyPbElement && typeof Chart !== "undefined" ? new Chart(frequencyPbElement, frequencyPbConfig) : null;
        const durationPbGraph = durationPbElement && typeof Chart !== "undefined" ? new Chart(durationPbElement, durationPbConfig) : null;

        function loadDashboardDailyGraphs(startDate = null, endDate = null) {
            const hasDailyGraph = totalMandsGraph || varietyGraph || frequencyPbGraph || durationPbGraph;

            if (!hasDailyGraph) {
                return;
            }

            if (typeof $ === "undefined") {
                showDashboardGraphError("Unable to load dashboard graphs.");
                return;
            }

            var ajaxRequest = $.ajax({
                url: "/graphs/dailyData",
                type: "post",
                data: {
                    "client_id": clientId,
                    "start_date": startDate,
                    "end_date": endDate
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == "success") {
                    if (totalMandsGraph) {
                        totalMandsGraph.data = response.data.total_mands;
                        totalMandsGraph.update();
                    }
                    if (varietyGraph) {
                        varietyGraph.data = response.data.variety_of_mands;
                        varietyGraph.update();
                    }
                    if (frequencyPbGraph) {
                        frequencyPbGraph.data = response.data.frequency_of_problem_behavior;
                        frequencyPbGraph.update();
                    }
                    if (durationPbGraph) {
                        durationPbGraph.data = response.data.total_duration_of_problem_behavior;
                        durationPbGraph.update();
                    }
                } else if (response.status == "validation_error") {
                    let errors = Object.values(response.message);
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + "<br>" + error, "error");
            });
        }

        renderSessionQualityChart();
        const noSessionPointImage = new Image();
        noSessionPointImage.src = "/assets/images/legend-black-8.jpg";
        const cumulativeGraph = buildCumulativeGraph();
        let cumulativeSkillsVisible = true;
        let cumulativeDoiVisible = true;
        const skillsLegendToggle = document.getElementById("cpd-legend-toggle-skills");
        const doiLegendToggle = document.getElementById("cpd-legend-toggle-doi");

        if (skillsLegendToggle) {
            skillsLegendToggle.addEventListener("click", function() {
                if (!cumulativeGraph) {
                    return;
                }

                cumulativeSkillsVisible = !cumulativeSkillsVisible;
                applyCumulativeLegendDatasetVisibility(cumulativeGraph, cumulativeSkillsVisible, cumulativeDoiVisible);
                cumulativeGraph.update();
            });
        }

        if (doiLegendToggle) {
            doiLegendToggle.addEventListener("click", function() {
                if (!cumulativeGraph) {
                    return;
                }

                cumulativeDoiVisible = !cumulativeDoiVisible;
                applyCumulativeLegendDatasetVisibility(cumulativeGraph, cumulativeSkillsVisible, cumulativeDoiVisible);
                cumulativeGraph.update();
            });
        }

        loadCumulativeGraph(cumulativeGraph, noSessionPointImage, cumulativeSkillsVisible, cumulativeDoiVisible);
        loadDashboardDailyGraphs();

        function refreshDashboardNoteToggles() {
            document.querySelectorAll(".js-dashboard-note-toggle").forEach(function(toggleButton) {
                const targetId = toggleButton.getAttribute("data-target-id");
                if (!targetId) {
                    return;
                }

                const noteElement = document.getElementById(targetId);
                if (!noteElement) {
                    return;
                }

                if (!noteElement.classList.contains("is-expanded")) {
                    const hasOverflow = noteElement.scrollHeight > noteElement.clientHeight + 1;
                    toggleButton.classList.toggle("d-none", !hasOverflow);
                    toggleButton.textContent = "Read more";
                } else {
                    toggleButton.classList.remove("d-none");
                    toggleButton.textContent = "Read less";
                }
            });
        }

        refreshDashboardNoteToggles();
        window.addEventListener("resize", refreshDashboardNoteToggles);

        document.addEventListener("click", function(event) {
            const toggleButton = event.target.closest(".js-dashboard-note-toggle");

            if (!toggleButton) {
                return;
            }

            const targetId = toggleButton.getAttribute("data-target-id");
            if (!targetId) {
                return;
            }

            const noteElement = document.getElementById(targetId);
            if (!noteElement) {
                return;
            }

            noteElement.classList.toggle("is-expanded");
            const isExpanded = noteElement.classList.contains("is-expanded");
            toggleButton.textContent = isExpanded ? "Read less" : "Read more";
            if (!isExpanded) {
                refreshDashboardNoteToggles();
            }
        });
    });
</script>
<?= $this->endSection() ?>
