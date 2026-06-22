<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="card border mx-n3 pt-2 px-2">
    <div class="card-header">
        <div class="row align-items-center">
            <!-- Title -->
            <div class="col-md-4">
                <h5 class="card-title mb-0">Mand Graphs</h5>
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
        <div class="card">

            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#total_mands" role="tab" aria-selected="true">
                            <i class="ri-line-chart-line align-middle me-1"></i>Total Mands
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#variety_of_mands" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Variety of Mands
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#prompt_level" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Prompt Level
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#mand_errors" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Mand Errors
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#vocal_response" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Vocal Response
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#prompt_delay_trial" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Prompt Delay
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#echoic_trial" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Echoic Trial
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#peer_mands" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line align-middle me-1"></i>Peer Mands
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#eye_contact_mands" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line align-middle me-1"></i>Eye Contact
                        </a>
                    </li>

                </ul>
                <div class="tab-content" style="padding-top: 30px;">
                    <div class="tab-pane  active show" id="total_mands" role="tabpanel">
                        <canvas id="mands_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="variety_of_mands" role="tabpanel">
                        <canvas id="variety_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="prompt_level" role="tabpanel">
                        <canvas id="prompt_level_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="mand_errors" role="tabpanel">
                        <canvas id="mand_errors_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="vocal_response" role="tabpanel">
                        <canvas id="vocal_response_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="prompt_delay_trial" role="tabpanel">
                        <canvas id="prompt_delay_trial_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="echoic_trial" role="tabpanel">
                        <canvas id="echoic_trial_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="peer_mands" role="tabpanel">
                        <canvas id="peer_mands_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="eye_contact_mands" role="tabpanel">
                        <canvas id="eye_contact_mands_graph" class="chart_content" height="100"></canvas>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
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
        /***************************************************************************************** */
        let client_id = "<?= $client->id ?>"; // passed from controller
        var csrfToken = "<?= csrf_hash() ?>";
        var modifiedLabels = [];
        var originalLabels = [];
        var totalMandsLabels = [];
        var totalMandsValues = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
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

        /***************************************************************************************** */
        const mands_config = {
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
                            labelString: 'Total number of mands'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{

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
        const variety_config = {
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
                            labelString: 'Variety of Mands'
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
            }
        };
        const peer_mands_config = {
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
                            labelString: 'Total number of peer mands'
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
            }
        };
        const eye_contact_mands_config = {
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
                            labelString: 'Total number of eye contact mands'
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
            }
        };
        const prompt_level_config = {
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
                    display: true,
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
                            labelString: 'Number of mands by prompt level'
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
            }
        };
        const mand_errors_config = {
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
                    display: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 100,
                            stepSize: 10,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value + '%';
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '% of mand errors'
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
            }
        };
        const vocal_response_config = {
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
                    display: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 100,
                            stepSize: 10,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value + '%';
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '% of vocalisations across vocal categories'
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
            }
        };

        const prompt_delay_trial_config = {
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
                    display: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 100,
                            stepSize: 10,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value + '%';
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '% of Change - Prompt Delay'
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
            }
        };
        const echoic_trial_config = {
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
                    display: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: -0.1,
                            suggestedMax: 100,
                            stepSize: 10,
                            callback: function(value, index, values) {
                                if (value < 0) {
                                    return '';
                                } else {
                                    return value + '%';
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '% of Change - Echoic Trial'
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
            }
        };
        var ctx1 = document.getElementById("mands_graph");
        var mands_graph = new Chart(ctx1, mands_config);
        var ctx8 = document.getElementById("peer_mands_graph");
        var peer_mands_graph = new Chart(ctx8, peer_mands_config);
        var ctx9 = document.getElementById("eye_contact_mands_graph");
        var eye_contact_mands_graph = new Chart(ctx9, eye_contact_mands_config);
        var ctx2 = document.getElementById("variety_graph");
        var variety_graph = new Chart(ctx2, variety_config);
        var ctx3 = document.getElementById("prompt_level_graph");
        var prompt_level_graph = new Chart(ctx3, prompt_level_config);
        var ctx4 = document.getElementById("mand_errors_graph");
        var mand_errors_graph = new Chart(ctx4, mand_errors_config);
        var ctx5 = document.getElementById("vocal_response_graph");
        var vocal_response_graph = new Chart(ctx5, vocal_response_config);
        var ctx6 = document.getElementById("prompt_delay_trial_graph");
        var prompt_delay_trial_graph = new Chart(ctx6, prompt_delay_trial_config);
        var ctx7 = document.getElementById("echoic_trial_graph");
        var echoic_trial_graph = new Chart(ctx7, echoic_trial_config);
        /****************************************************************************************  */

        function loadCombinedMandGraphs(start_date = null, end_date = null) {
            var ajaxRequest = $.ajax({
                url: '/graphs/dailyData',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                },
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    totalMandsLabels = response.data.total_mands.labels;
                    totalMandsValues = response.data.total_mands.datasets[0].data;

                    tickValues = [];
                    modifiedLabels = [];
                    originalLabels = response.data.total_mands.labels;
                    originalLabels.forEach(function(label) {
                        var date = new Date(label);
                        var modifiedLabel = date.toLocaleString('default', {
                            month: 'short',
                            year: 'numeric'
                        });
                        if (!modifiedLabels.includes(modifiedLabel)) {
                            modifiedLabels.push(modifiedLabel);
                        } else {
                            modifiedLabels.push("");
                        }
                    });

                    mands_graph.data = response.data.total_mands;
                    mands_graph.update();

                    variety_graph.data = response.data.variety_of_mands;
                    variety_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    variety_graph.update();
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

        function loadMandsGraphs(start_date = null, end_date = null) {

            var ajaxRequest = $.ajax({
                url: '/graphs/mands',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                },
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    peer_mands_graph.data = response.data.peer_mands_data;
                    peer_mands_graph.update();
                    eye_contact_mands_graph.data = response.data.eye_contact_mands_data;
                    eye_contact_mands_graph.update();


                    prompt_level_graph.data = response.data.prompt_level_data;
                    prompt_level_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    prompt_level_graph.update();


                    mand_errors_graph.data = response.data.mand_errors_data;
                    mand_errors_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}%`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    mand_errors_graph.update();


                    vocal_response_graph.data = response.data.vocal_response_data;
                    vocal_response_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}%`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    vocal_response_graph.update();


                    prompt_delay_trial_graph.data = response.data.prompt_delay_trial_data;
                    prompt_delay_trial_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}%`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    prompt_delay_trial_graph.update();


                    echoic_trial_graph.data = response.data.echoic_trial_data;
                    echoic_trial_graph.options.tooltips = {
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.datasets[tooltipItem.datasetIndex].label || '';
                                const val = tooltipItem.yLabel;
                                return `${label}: ${val}%`;
                            },
                            afterBody: function(tooltipItems, data) {
                                const index = tooltipItems[0].index;
                                const dateLabel = data.labels[index];
                                const totalIndex = totalMandsLabels.indexOf(dateLabel);
                                const totalMands = totalIndex !== -1 ? totalMandsValues[totalIndex] : 'N/A';

                                return [`    Total Mands: ${totalMands}`];
                            }
                        }
                    };
                    echoic_trial_graph.update();


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

        }; //On change function ends
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

            loadCombinedMandGraphs(start_date, end_date);
            loadMandsGraphs(start_date, end_date);
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
            loadCombinedMandGraphs(null, null);
            loadMandsGraphs(null, null);
        });
        loadCombinedMandGraphs();
        loadMandsGraphs();

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            // Fix scroll issue after tab switch
            setTimeout(function() {
                if (typeof SimpleBar !== 'undefined') {
                    // Reinitialize simplebar (Velzon uses this for scrollable content)
                    document.querySelectorAll('[data-simplebar]').forEach(el => {
                        new SimpleBar(el);
                    });
                }

                // Optional: Scroll to top of page content after tab change
                $('.page-content, .card-body').animate({
                    scrollTop: 0
                }, 'fast');
            }, 200);
        });
        /***************************************************************************************** */
    });
</script>
<?= $this->endSection() ?>
