<?= $this->extend("layout/master-profile") ?>

<?= $this->section("head_tag") ?>

<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2">
    <div class="card border">
        <div class="card-header">
            <div class="row align-items-center">
                <!-- Title -->
                <div class="col-md-4">
                    <h5 class="card-title mb-0">Behaviour Reduction Graphs</h5>
                </div>

                <!-- Filters -->
                <div class="col-md-8">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">

                        <!-- Start Date -->
                        <div class="input-group" style="width: 170px;">
                            <span class="input-group-text">
                                <i class="ri-calendar-line"></i>
                            </span>
                            <input id="start_date"
                                type="text"
                                class="form-control"
                                placeholder="Start Date"
                                data-provider="flatpickr"
                                data-date-format="d-M-Y"
                                data-maxDate="today">
                        </div>

                        <!-- End Date -->
                        <div class="input-group" style="width: 170px;">
                            <span class="input-group-text">
                                <i class="ri-calendar-line"></i>
                            </span>
                            <input id="end_date"
                                type="text"
                                class="form-control"
                                placeholder="End Date"
                                data-provider="flatpickr"
                                data-date-format="d-M-Y"
                                data-maxDate="today">
                        </div>

                        <!-- Clear Button -->
                        <button type="button"
                            id="clear_search"
                            class="btn btn-success bg-gradient waves-effect waves-light btn-label right">
                            <i class="ri-calendar-event-line label-icon align-middle fs-16 ms-2"></i>
                            Clear
                        </button>

                        <!-- Search Button -->
                        <button type="button"
                            id="search"
                            class="btn btn-info bg-gradient waves-effect waves-light btn-label right">
                            <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>
                            Search
                        </button>

                    </div>
                </div>

            </div>
        </div>
        <div class="card-body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#frequency_of_problem_behavior" role="tab" aria-selected="false" tabindex="-1">
                        <i class="ri-line-chart-line align-middle me-1"></i>Frequency of Problem Behavior
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#total_duration_of_problem_behavior" role="tab" aria-selected="false" tabindex="-1">
                        <i class="ri-line-chart-line align-middle me-1"></i>Total Duration of Problem Behavior
                    </a>
                </li>
            </ul>
            <div class="tab-content text-muted" style="padding-top: 30px;">
                <div class="tab-pane active show" id="frequency_of_problem_behavior" role="tabpanel">
                    <canvas id="frequency_pb_graph" class="chart_content" height="100"></canvas>
                </div>
                <div class="tab-pane" id="total_duration_of_problem_behavior" role="tabpanel">
                    <canvas id="duration_pb_graph" class="chart_content" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        // Page-level loader binding (ONLY for this page)
        $(document).ajaxStart(function() {
            showPageLoader();
        });

        $(document).ajaxStop(function() {
            hidePageLoader();
        });
        let client_id = "<?= $client->id ?>"; // passed from controller
        $("#start_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });
        $("#end_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });
        var csrfToken = "<?= csrf_hash() ?>";
        var modifiedLabels = [];
        var originalLabels = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        const frequency_pb_config = {
            type: 'line',
            data: [],
            options: {
                tooltips: {
                    intersect: false,
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false,
                    /*labels: {
                        filter: function(item, chart) {
                            // Logic to remove a particular legend item goes here
                            return item.text.includes('No Session');
                        }
                    },*/
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 5,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value;
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Frequency of Problem Behavior'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: { //rotating the x-axis labels
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Dates'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true,
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                },
                // reponsive: true
            }
        };
        const duration_pb_config = {
            type: 'line',
            data: [],
            options: {
                tooltips: {
                    intersect: false,
                },
                annotation: {
                    annotations: []
                },
                legend: {
                    display: false,
                    /*labels: {
                        filter: function(item, chart) {
                            // Logic to remove a particular legend item goes here
                            return item.text.includes('No Session');
                        }
                    },*/
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.2,
                            suggestedMax: 5,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value;
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Total Duration of Problem Behavior  (Minutes)'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: { //rotating the x-axis labels
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Dates'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        },
                        offset: true,
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                },
                // reponsive: true
            }
        };
        var ctx1 = document.getElementById("frequency_pb_graph");
        var frequency_pb_graph = new Chart(ctx1, frequency_pb_config);
        var ctx2 = document.getElementById("duration_pb_graph");
        var duration_pb_graph = new Chart(ctx2, duration_pb_config);

        function loadGraphs(start_date = null, end_date = null) {
            var ajaxRequest = $.ajax({
                url: '/graphs/dailyData',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    frequency_pb_graph.data = response.data.frequency_of_problem_behavior;
                    frequency_pb_graph.update();
                    duration_pb_graph.data = response.data.total_duration_of_problem_behavior;
                    duration_pb_graph.update();
                    //showAlert('','Data Loaded', response.status);
                } else if (response.status == 'validation_error') {
                    let errors = Object.values(response.message);
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
        }
        $("#search").on('click', function(e) {
            e.preventDefault();

            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();

            // Basic validation
            if (start_date && end_date) {
                let start = new Date(start_date);
                let end = new Date(end_date);

                if (end < start) {
                    showAlert('', 'End date must be greater than Start date', 'warning');
                    return;
                }
            }

            loadGraphs(start_date, end_date);
        });
        $("#clear_search").on('click', function() {

            // Clear flatpickr properly
            if ($("#start_date")[0]._flatpickr) {
                $("#start_date")[0]._flatpickr.clear();
            }

            if ($("#end_date")[0]._flatpickr) {
                $("#end_date")[0]._flatpickr.clear();
            }

            // Reload full dataset
            loadGraphs(null, null);
        });
        // auto-load on page load
        loadGraphs();
    });
</script>
<?= $this->endSection() ?>