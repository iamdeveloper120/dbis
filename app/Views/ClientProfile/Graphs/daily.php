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
                    <h5 class="card-title mb-0">Daily Data Graphs</h5>
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
                    <a class="nav-link active" data-bs-toggle="tab" href="#skills_retained" role="tab" aria-selected="true">
                        <i class="ri-line-chart-line align-middle me-1"></i>Skills Retained
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#doi" role="tab" aria-selected="true">
                        <i class="ri-line-chart-line align-middle me-1"></i>Degrees of Independence
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#session_quality_rating" role="tab" aria-selected="false" tabindex="-1">
                        <i class="ri-line-chart-line align-middle me-1"></i>Session quality rating
                    </a>
                </li>
            </ul>
            <div class="tab-content text-muted" style="padding-top: 30px;">
                <div class="tab-pane active show" id="skills_retained" role="tabpanel">
                    <canvas id="skills_retained_graph" class="chart_content" height="100"></canvas>
                </div>
                <div class="tab-pane" id="doi" role="tabpanel">
                    <canvas id="doi_graph" class="chart_content" height="100"></canvas>
                </div>
                <div class="tab-pane" id="session_quality_rating" role="tabpanel">
                    <canvas id="session_qr_graph" class="chart_content" height="100"></canvas>
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

        const skills_retained_config = {
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
                            labelString: 'Number of skills retained'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        //type: "time",
                        // time: {
                        //    unit: "month"
                        // },
                        ticks: { //rotating the x-axis labels
                            //autoSkip: true,
                            //maxTicksLimit: 24,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90,
                            callback: function(value, index, values) {
                                //return tickValues.includes(value) ? value : '';                         
                                /*if (modifiedLabels[index] === "") {
                                    return "";
                                }
                                return modifiedLabels[originalLabels.indexOf(value)];*/
                                return value;
                            }
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
        const doi_config = {
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
                            labelString: 'Number of degrees of Independence'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        //type: "time",
                        // time: {
                        //    unit: "month"
                        // },
                        ticks: { //rotating the x-axis labels
                            //autoSkip: true,
                            //maxTicksLimit: 24,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90,
                            callback: function(value, index, values) {
                                //return tickValues.includes(value) ? value : '';                         
                                /*if (modifiedLabels[index] === "") {
                                    return "";
                                }
                                return modifiedLabels[originalLabels.indexOf(value)];*/
                                return value;
                            }
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
        const session_qr_config = {
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
                            min: 0,
                            max: 3,
                            stepSize: 1,
                            callback: function(value, index, values) {
                                if (value === 0) return '';
                                if (value === 1) return 'Poor';
                                if (value === 2) return 'Good';
                                if (value === 3) return 'Excellent';
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Session Quality Rating'
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
        var ctx1 = document.getElementById("session_qr_graph");
        var session_qr_graph = new Chart(ctx1, session_qr_config);
        var ctx2 = document.getElementById("doi_graph");
        var session_doi_graph = new Chart(ctx2, doi_config);
        var ctx3 = document.getElementById("skills_retained_graph");
        var session_skills_retained_graph = new Chart(ctx3, skills_retained_config);

        function loadGraphs(start_date = null, end_date = null) {
            var ajaxRequest = $.ajax({
                url: '/client-profile/graphs/daily/<?= encodeValue($client->id) ?>/data',
                type: 'post',
                data: {
                    "start_date": start_date,
                    "end_date": end_date
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    session_skills_retained_graph.data = response.data.skills_retained;
                    session_skills_retained_graph.update();
                    session_doi_graph.data = response.data.doi;
                    session_doi_graph.update();
                    session_qr_graph.data = response.data.session_quality_rating;
                    session_qr_graph.update();
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

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            setTimeout(function() {
                if (typeof SimpleBar !== 'undefined') {
                    document.querySelectorAll('[data-simplebar]').forEach(el => {
                        new SimpleBar(el);
                    });
                }

                $('.page-content, .card-body').animate({
                    scrollTop: 0
                }, 'fast');
            }, 200);
        });
    });
</script>
<?= $this->endSection() ?>