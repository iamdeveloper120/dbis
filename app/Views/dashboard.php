<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>

<div class="row mb-3 pb-1">
    <div class="col-12">
        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-16 mb-1">Dashboard</h4>
                <p class="text-muted mb-0">Live session and program insights.</p>
            </div>
            <div class="mt-3 mt-lg-0">
                <form id="dashboard-filter-form" action="javascript:void(0);">
                    <div class="row g-3 mb-0 align-items-center">
                        <div class="col-sm-auto">
                            <div class="input-group">
                                <input type="text" id="date-range-picker" class="form-control border-0 dash-filter-picker shadow">
                                <div class="input-group-text bg-primary border-primary text-white">
                                    <i class="ri-calendar-2-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card crm-widget">
            <div class="card-body p-0">
                <div class="row row-cols-xxl-6 row-cols-md-2 row-cols-1 g-0">

                    <?php
                    $metrics = [
                        'in_progress'   => ['Sessions In Progress', 'ri-timer-line', 'primary'],
                        'in_review'     => ['Sessions In Review', 'ri-clipboard-line', 'info'],
                        'processed'     => ['Sessions Processed', 'ri-check-double-line', 'success'],
                        'conflict'      => ['Conflict Sessions', 'ri-error-warning-line', 'danger'],
                        'program_alerts' => ['Program Alerts', 'ri-notification-2-line', 'danger'],
                        'program_changes' => ['Program Changes', 'ri-exchange-line', 'secondary']
                    ];

                    foreach ($metrics as $key => [$label, $icon, $color]) : ?>
                        <div class="col">
                            <div class="py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13"><?= esc($label) ?></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="<?= esc($icon) ?> fs-3 text-<?= esc($color) ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0">
                                            <span class="counter-value" data-metric="<?= esc($key) ?>">0</span>
                                        </h2>
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


<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Session Quality Rating</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div id="session_rating_chart" data-colors='["--vz-danger", "--vz-warning", "--vz-success", "--vz-danger", "--vz-info"]' class="apex-charts" dir="ltr"></div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div>
    <!-- end col -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Client Progress (% of retained targets out of introduced targets)</h4>
            </div>
            <div class="card-body">
                <div id="client-progress-chart"
                    data-colors='["--vz-primary"]'
                    class="apex-charts"
                    dir="ltr"></div>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <!-- 1. Frequency and Duration of Problem Behaviors -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">PB – Frequency & Duration by Client</h4>
            </div>
            <div class="card-body">
                <div id="client-pb-chart"
                    data-colors='["--vz-warning", "--vz-danger"]'
                    class="apex-charts"
                    dir="ltr"></div>
            </div>
        </div>
    </div>

    <!-- 2. Skills Retained and Degree of Independence -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Skills Retained & DOI by Client</h4>
            </div>
            <div class="card-body">
                <div id="client-retained-chart"
                    data-colors='["--vz-success", "--vz-primary"]'
                    class="apex-charts"
                    dir="ltr"></div>
            </div>
        </div>
    </div>

    <!-- 3. Mands – Frequency and Variety -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Mands – Frequency & Variety by Client</h4>
            </div>
            <div class="card-body">
                <div id="client-mand-chart"
                    data-colors='["--vz-info", "--vz-secondary"]'
                    class="apex-charts"
                    dir="ltr"></div>
            </div>
        </div>
    </div>

    <!-- 4. Program Changes and Alerts -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Program Changes & Alerts by Client</h4>
            </div>
            <div class="card-body">
                <div id="client-program-alert-chart"
                    data-colors='["--vz-danger", "--vz-warning"]'
                    class="apex-charts"
                    dir="ltr"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <!-- 1. Frequency and Duration of Problem Behaviors -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Session Comments and WOW Moments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="client_comments_wow_sessions_datatable" class="table table-bordered align-middle nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Instructor</th>
                                <th class="dt-nowrap">Date</th>
                                <th>QR</th>
                                <th>Instructor Comments</th>
                                <th>Wow moments!</th>
                            </tr>
                        </thead>

                        <tbody> </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="full_comment_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="nosession_wd_modal_title">Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script src="/assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
    // === Velzon-standard color loader ===
    function escapeForAttribute(text) {
        return $('<div>').text(text).html().replace(/"/g, '&quot;');
    }

    function exportFormatter(data, row, column, node) {
        return $('<div>').html(data).text().trim();
    }


    function getChartColorsArray(chartId) {
        if (document.getElementById(chartId) !== null) {
            var colors = document.getElementById(chartId).getAttribute("data-colors");
            if (colors) {
                colors = JSON.parse(colors);
                return colors.map(function(value) {
                    var newValue = value.replace(" ", "");
                    if (newValue.indexOf(",") === -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        if (color) return color.trim();
                        else return newValue;
                    } else {
                        var val = value.split(',');
                        if (val.length == 2) {
                            var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                            rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                            return rgbaColor;
                        } else {
                            return newValue;
                        }
                    }
                });
            } else {
                console.warn('data-colors attribute not found on:', chartId);
            }
        }
    }
    const chartInstances = {};
    document.addEventListener("DOMContentLoaded", function() {
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        let progressChart = null;
        let commentsWowTable = null;

        commentsWowTable = $('#client_comments_wow_sessions_datatable').DataTable({
            lengthChange: false,
            ordering: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                orthogonal: 'export',
                                format: {
                                    body: exportFormatter
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                orthogonal: 'export',
                                format: {
                                    body: exportFormatter
                                }
                            }
                        },
                        {
                            extend: 'colvis',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }
                    ]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search'
                    }
                }
            },
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'client'
                },
                {
                    data: 'instructor_name'
                },
                {
                    data: 'session_date',
                    render: function(data) {
                        return data;
                    }
                },

                {
                    data: 'qr',
                    render: function(val) {
                        if (val == 1) return 'Poor';
                        if (val == 2) return 'Good';
                        if (val == 3) return 'Excellent';
                        return '';
                    }
                },
                {
                    data: 'instructor_comments',
                    render: {
                        display: function(data) {
                            if (!data || data.length <= 20) return data;

                            const safe = escapeForAttribute(data);
                            return safe.substr(0, 20) +
                                '... <a href="#" class="readMore" data-full-comment="' + safe + '">' +
                                '<span class="badge bg-info-subtle text-info">Read more</span></a>';
                        },
                        export: function(data) {
                            return data; // FULL TEXT
                        },
                        _: function(data) {
                            return data; // search, sort, filter
                        }
                    }
                },

                {
                    data: 'wow_moments',
                    render: {
                        display: function(data) {
                            if (!data || data.length <= 20) return data;

                            const safe = escapeForAttribute(data);
                            return safe.substr(0, 20) +
                                '... <a href="#" class="readMore" data-full-comment="' + safe + '">' +
                                '<span class="badge bg-info-subtle text-info">Read more</span></a>';
                        },
                        export: function(data) {
                            return data; // FULL TEXT
                        },
                        _: function(data) {
                            return data; // search, sort, filter
                        }
                    }
                }

            ]
        });

        $('#client_comments_wow_sessions_datatable').on('click', '.readMore', function(e) {
            e.preventDefault();
            const fullComment = $(this).attr('data-full-comment'); // get the full comment from the data attribute
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });
        const fetchDashboardData = (startDate = null, endDate = null) => {
            $.ajax({
                url: "<?= site_url('dashboard/data') ?>",
                method: "POST",
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    // Update KPI metrics
                    for (const key in response.data) {
                        const el = document.querySelector(`[data-metric="${key}"]`);
                        if (el) el.textContent = response.data[key];
                    }

                    // Render client progress chart
                    if (response.progress && response.progress.length > 0) {
                        // Sort from highest to lowest for bottom-to-top bar

                        const categories = response.progress.map(row => row.client);
                        const seriesData = response.progress.map(row => row.percentage);

                        var chartColors = getChartColorsArray("client-progress-chart");

                        var options = {
                            series: [{
                                name: "Progress",
                                data: seriesData
                            }],
                            chart: {
                                type: "bar",
                                height: 300,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false, // ✅ switch to vertical bars
                                    borderRadius: 0,
                                    distributed: true,
                                    columnWidth: "40%", // optional: improve spacing
                                    dataLabels: {
                                        position: "top"
                                    }
                                }
                            },
                            colors: chartColors,
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val + "%";
                                },
                                offsetY: -20, // adjust for top label
                                style: {
                                    fontSize: "12px",
                                    fontWeight: 600,
                                    colors: ["#adb5bd"]
                                }
                            },
                            xaxis: {
                                categories: categories,
                                labels: {
                                    rotate: -45,
                                    rotateAlways: true,
                                    trim: false, // ✅ prevent trimming which might suppress rotation
                                    formatter: function(val) {
                                        return val;
                                    }
                                }
                            },
                            yaxis: {
                                max: 100,
                                labels: {
                                    formatter: function(val) {
                                        return val + "%";
                                    }
                                }
                            },
                            grid: {
                                show: true,
                                strokeDashArray: 4
                            },
                            legend: {
                                show: false
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val, opts) {
                                        const row = response.progress[opts.dataPointIndex];
                                        console.log(row);
                                        return `${val}%\nIntroduced: ${row.introduced}\nRetained: ${row.retained}`;
                                    }
                                }
                            }
                        };


                        if (progressChart) {
                            progressChart.destroy();
                        }

                        progressChart = new ApexCharts(document.querySelector("#client-progress-chart"), options);
                        progressChart.render();



                    }

                    if (response.ratings) {
                        console.log(response.ratings_clients);
                        const ratingData = [
                            response.ratings.poor || 0,
                            response.ratings.good || 0,
                            response.ratings.excellent || 0
                        ];

                        const total = ratingData.reduce((a, b) => a + b, 0);

                        const options = {
                            series: ratingData,
                            labels: ['Poor', 'Good', 'Excellent'],
                            chart: {
                                type: 'pie',
                                height: 330
                            },
                            colors: getChartColorsArray("session_rating_chart"),
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val, opts) {
                                        const label = opts.globals.labels[opts.seriesIndex]; // 'Poor', 'Good', 'Excellent'
                                        console.log(label);
                                        const percent = ((val / total) * 100).toFixed(1);

                                        // Access MRN list from global `response` object
                                        const mrnsMap = response.ratings_clients || {};
                                        const mrnList = mrnsMap[label.toLowerCase()] || [];

                                        let output = `<div>${label}:  ${val} sessions (${percent}%)</div>`;

                                        if (mrnList.length > 0) {
                                            output += `<div>Clients:</div>`;

                                            const chunkSize = 4;
                                            for (let i = 0; i < mrnList.length; i += chunkSize) {
                                                const chunk = mrnList.slice(i, i + chunkSize);
                                                output += `<div>${chunk.join(', ')}</div>`;
                                            }
                                        }

                                        return output;
                                    },
                                    title: {
                                        formatter: function() {
                                            return ''; // Hides the label
                                        }
                                    }
                                }
                            }
                        };

                        if (chartInstances["session_rating_chart"]) {
                            chartInstances["session_rating_chart"].destroy();
                        }

                        chartInstances["session_rating_chart"] = new ApexCharts(
                            document.querySelector("#session_rating_chart"),
                            options
                        );
                        chartInstances["session_rating_chart"].render();
                    }

                    // Only if metrics data exists
                    if (response.metrics && response.metrics.length > 0) {
                        const categories = response.metrics.map(row => row.client);

                        // Chart 1: Problem Behavior – Frequency & Duration
                        renderDualBarChart(
                            "#client-pb-chart",
                            categories,
                            response.metrics.map(row => row.pb_frequency),
                            response.metrics.map(row => Math.round(row.pb_duration_secs / 60)), // convert secs to mins
                            "Frequency",
                            "Duration (min)"
                        );

                        // Chart 2: Skills Retained & DOI
                        renderDualBarChart(
                            "#client-retained-chart",
                            categories,
                            response.metrics.map(row => row.skills_retained),
                            response.metrics.map(row => row.doi),
                            "Skills Retained",
                            "DOI"
                        );

                        // Chart 3: Mands – Frequency & Variety
                        renderDualBarChart(
                            "#client-mand-chart",
                            categories,
                            response.metrics.map(row => row.mands_freq),
                            response.metrics.map(row => row.mands_variety),
                            "Mands Frequency",
                            "Variety"
                        );

                        // Chart 4: Program Changes & Alerts
                        renderDualBarChart(
                            "#client-program-alert-chart",
                            categories,
                            response.metrics.map(row => row.program_changes),
                            response.metrics.map(row => row.program_alerts),
                            "Changes",
                            "Alerts"
                        );
                    } else {
                        // Clear each chart if it exists
                        const idsToClear = [
                            'client-pb-chart',
                            'client-retained-chart',
                            'client-mand-chart',
                            'client-program-alert-chart' // Add this
                        ];
                        idsToClear.forEach(id => {
                            if (chartInstances[id]) {
                                chartInstances[id].destroy();
                                delete chartInstances[id]; // Optional: clean up reference
                            }
                        });
                    }

                    commentsWowTable.clear().rows.add(response.session_comments).draw();
                }
            });
        };

        // Init with today's date
        const today = moment().format('YYYY-MM-DD');
        flatpickr("#date-range-picker", {
            mode: "range",
            dateFormat: dateFormat,
            defaultDate: [new Date(), new Date()],
            onClose: function(selectedDates) {
                if (selectedDates.length === 2) {
                    const start = moment(selectedDates[0]).format('YYYY-MM-DD');
                    const end = moment(selectedDates[1]).format('YYYY-MM-DD');
                    fetchDashboardData(start, end);
                }
            }
        });

        fetchDashboardData(today, today);


    });



    function renderDualBarChart(container, categories, data1, data2, label1, label2) {
        const chartId = container.replace("#", "");
        const chartColors = getChartColorsArray(chartId);

        if (chartInstances[chartId]) {
            chartInstances[chartId].destroy();
        }
        const options = {
            series: [{
                    name: label1,
                    data: data1
                },
                {
                    name: label2,
                    data: data2
                }
            ],
            chart: {
                type: "bar",
                height: 300,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "10", // ✅ makes bars narrower
                    endingShape: "rounded",
                    dataLabels: {
                        position: "top"
                    }
                }
            },
            colors: chartColors,
            dataLabels: {
                enabled: false,
                offsetY: -20,
                formatter: function(val) {
                    return val === 0 ? '' : val;
                },
                style: {
                    fontSize: "12px",
                    fontWeight: "bold",
                    colors: [chartColors[0], chartColors[1]] // ✅ correctly apply label colors
                }
            },
            xaxis: {
                categories: categories,
                tickPlacement: 'on',
                labels: {
                    rotate: -45,
                    rotateAlways: true,
                    trim: false, // ✅ prevent trimming which might suppress rotation
                    hideOverlappingLabels: false,
                    showDuplicates: true,
                    style: {
                        fontSize: "11px",
                        fontWeight: "normal",
                        fontFamily: "inherit",
                        colors: ['#6c757d'] // You can dynamically use chartColors too
                    }
                }
            },
            yaxis: {
                title: {
                    text: ''
                }
            },
            grid: {
                show: true,
                strokeDashArray: 4
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent'] // ✅ remove white overlay between bars
            },
            tooltip: {
                shared: true,
                intersect: false
            },
            legend: {
                position: 'top'
            }
        };

        const chart = new ApexCharts(document.querySelector(container), options);
        chart.render();
        chartInstances[chartId] = chart;
    }
</script>

<?= $this->endSection() ?>