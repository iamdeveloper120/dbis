<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Daily Data Graphs</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Graphs</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row justify-content-end">
                    <div class="col-md-4">
                     
                        <select class="form-control " id="client_dropdown_list">
                            <option value="">SELECT CLIENT</option>
                            <?php foreach ($clients as $client) {  ?>
                                 <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                       
                        <input id="start_date" type="text" class="form-control" placeholder="Start Date" data-provider="flatpickr" data-date-format="d-M-Y" data-maxDate="today" data-clear data-week-number>
                    </div>
                    <div class="col-md-2">
                        
                        <input id="end_date" type="text" class="form-control" placeholder="End Date" data-provider="flatpickr" data-date-format="d-M-Y" data-maxDate="today" data-clear data-week-number>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <div class="gap-2 float-end">
                            <button type="button" id="clear_search" class="btn btn-success bg-gradient waves-effect waves-light btn-label right"><i class="ri-calendar-event-line label-icon align-middle fs-16 ms-2"></i>Clear</button>

                            <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right "><i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>

                            <div class="btn-group mt-4 mt-md-0" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="back" title="Previous client"><i class="ri-arrow-left-line"></i></button>&nbsp;
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="next" title="Next client"><i class="ri-arrow-right-line"></i></button>
                            </div>
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
                            <i class="ri-line-chart-line align-middle me-1"></i>DOI
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#total_mands" role="tab" aria-selected="true">
                            <i class="ri-line-chart-line align-middle me-1"></i># Mands
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#variety_of_mands" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line me-1 align-middle"></i> Variety
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#frequency_of_problem_behavior" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line align-middle me-1"></i>F of PB
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#total_duration_of_problem_behavior" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line align-middle me-1"></i>T D of PB
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#session_quality_rating" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ri-line-chart-line align-middle me-1"></i>QR
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
                    <div class="tab-pane" id="total_mands" role="tabpanel">
                        <canvas id="mands_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="variety_of_mands" role="tabpanel">
                        <canvas id="variety_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="frequency_of_problem_behavior" role="tabpanel">
                        <canvas id="frequency_pb_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="total_duration_of_problem_behavior" role="tabpanel">
                        <canvas id="duration_pb_graph" class="chart_content" height="100"></canvas>
                    </div>
                    <div class="tab-pane" id="session_quality_rating" role="tabpanel">
                        <canvas id="session_qr_graph" class="chart_content" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header border-bottom-dashed">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm">
                            <div>
                                <h5 class="card-title mb-0">Abbreviations</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom-dashed border-bottom">
                    <ul class="list-group">
                        <li class="list-group-item"><span class="text-info">DOI</span> <i class="mdi mdi-chevron-right"></i> Degrees of Independence </li>
                        <li class="list-group-item"><span class="text-info"># Mands</span> <i class="mdi mdi-chevron-right"></i> Total Mands </li>
                        <li class="list-group-item"><span class="text-info">Variety</span> <i class="mdi mdi-chevron-right"></i> Variety of Mands </li>
                        <li class="list-group-item"><span class="text-info">F of PB</span> <i class="mdi mdi-chevron-right"></i> Frequency of problem behaviour</li>
                        <li class="list-group-item"><span class="text-info">T D of PB</span> <i class="mdi mdi-chevron-right"></i> Total duration of problem behaviour</li>
                        <li class="list-group-item"><span class="text-info">QR</span> <i class="mdi mdi-chevron-right"></i> Session quality rating</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
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
        var csrfToken = "<?= csrf_hash() ?>";
        var modifiedLabels = [];
        var originalLabels = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('#client_dropdown_list').select2();
        $('#next').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedOption = dropdown.val();
            var optionsCount = dropdown.find('option').length;
            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex + 1;
                // Wrap around to the first option if the last option is selected
                if (nextIndex >= optionsCount) {
                    nextIndex = 1;
                }
                // Set the next option as selected
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
                $('#search').click();
            } else {
                showAlert('', 'Client not exist', 'info');
            }
        });
        $('#back').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedOption = dropdown.val();
            var optionsCount = dropdown.find('option').length;
            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex - 1;
                // Wrap around to the first option if the last option is selected
                if (nextIndex <= 0) {
                    nextIndex = optionsCount - 1;
                }
                // Set the next option as selected
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
                $('#search').click();
            } else {
                showAlert('', 'Client not exist', 'info');
            }
        });
        $("#clear_search").click(function() {
            $('#start_date').flatpickr(dateConfig).clear();
            $('#end_date').flatpickr(dateConfig).clear();
            $('#search').click();
        });
        /***************************************************************************************** */
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
                // reponsive: true
            }
        };
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
        var ctx1 = document.getElementById("mands_graph");
        var mands_graph = new Chart(ctx1, mands_config);
        var ctx2 = document.getElementById("variety_graph");
        var variety_graph = new Chart(ctx2, variety_config);
        var ctx3 = document.getElementById("frequency_pb_graph");
        var frequency_pb_graph = new Chart(ctx3, frequency_pb_config);
        var ctx4 = document.getElementById("duration_pb_graph");
        var duration_pb_graph = new Chart(ctx4, duration_pb_config);
        var ctx5 = document.getElementById("session_qr_graph");
        var session_qr_graph = new Chart(ctx5, session_qr_config);
        var ctx6 = document.getElementById("doi_graph");
        var session_doi_graph = new Chart(ctx6, doi_config);
        var ctx7 = document.getElementById("skills_retained_graph");
        var session_skills_retained_graph = new Chart(ctx7, skills_retained_config);
        /****************************************************************************************  */
        var dateConfig = {
            dateFormat: "d-M-Y",
            maxDate: "today",
            weekNumbers: true,
        };
        $("#search").on('click', function(e) {
            e.preventDefault;
            search = $(this);
            let client_id = $("#client_dropdown_list").val();
            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();
            var ajaxRequest = $.ajax({
                url: '/graphs/dailyData',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                },
                beforeSend: function(xhr) {
                    $('#client_dropdown_list').prop("disabled", true);
                    search.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    session_skills_retained_graph.data = response.data.skills_retained;
                    session_skills_retained_graph.update();
                    session_doi_graph.data = response.data.doi;
                    session_doi_graph.update();
                    tickValues = [];
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
                    variety_graph.update();
                    frequency_pb_graph.data = response.data.frequency_of_problem_behavior;
                    frequency_pb_graph.update();
                    duration_pb_graph.data = response.data.total_duration_of_problem_behavior;
                    duration_pb_graph.update();
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
            ajaxRequest.always(function() {
                $('#client_dropdown_list').prop("disabled", false);
                search.prop("disabled", false);
            });
        }); //On change function ends
        /***************************************************************************************** */
    });
    /***************************************************************************************** */
</script>
<?= $this->endSection() ?>