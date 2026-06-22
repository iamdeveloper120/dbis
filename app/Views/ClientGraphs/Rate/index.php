<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="tab-content text-muted">
            <div class="tab-pane active show" role="tabpanel">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom-dashed pb-0 mb-0">
                                <?= view('ClientGraphs/Rate/_tabs', ['tab' => 'graph']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="row justify-content-end">
                                    <div class="col-md-10">
                                        <select class="form-control " id="client_dropdown_list">
                                            <option value="">SELECT CLIENT</option>
                                            <?php foreach ($clients as $client) {  ?>
                                                  <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 align-self-end">
                                        <div class="gap-2 float-end">                                             
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
                                <ul class="nav nav-tabs  arrow-navtabs  mb-3" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_skills_retained" role="tab" aria-selected="true">
                                            <i class="ri-line-chart-line align-middle me-1"></i> Skills Retained Rate Graph
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#tab_doi" role="tab" aria-selected="false" tabindex="-1">
                                            <i class="ri-line-chart-line me-1 align-middle"></i> Degrees Of Independence Rate Graph
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content text-muted" style="padding-top: 10px;">
                                    <div class="tab-pane active show" id="tab_skills_retained" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <img class="img" src="/assets/images/legend-black.png" alt="" width="35">
                                                <span class="">Skills Retained</span>
                                                &nbsp; &nbsp; &nbsp;
                                                <img class="img" src="/assets/images/legend-red.jpg" alt="" width="35">
                                                <span class="">Target</span>
                                            </div>
                                        </div>
                                        <canvas id="rate_graph_skills" class="chart_content" height="100" style="padding-top: 10px;"></canvas>
                                        <hr class="sidebar-divider">
                                        <div id='skill_phaseline_table'></div>
                                    </div>
                                    <div class="tab-pane" id="tab_doi" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <img class="img" src="/assets/images/legend-black.png" alt="" width="35">
                                                <span class="">Degrees of independence</span>
                                                &nbsp; &nbsp; &nbsp;
                                                <img class="img" src="/assets/images/legend-red.jpg" alt="" width="35">
                                                <span class="">Target</span>
                                            </div>
                                        </div>
                                        <canvas id="rate_graph_doi" class="chart_content" height="100" style="padding-top: 10px;"></canvas>
                                        <hr class="sidebar-divider">
                                        <div id='doi_phaseline_table'></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!--end col-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        var noDataPointImage = new Image();
        noDataPointImage.src = "<?php echo base_url() ?>assets/images/legend-black-8.jpg";

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
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

            } else {
                showAlert('', 'Client not exist', 'info');
            }

        });
        /***************************************************************************************** */
        const rate_graph_skills_config = {
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
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 2,
                    labels: {
                        filter: function(item, chart) {
                            // Logic to remove a particular legend item goes here
                            return !item.text.includes('No Data');
                        }
                    },
                },

                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: 0,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Skills Retained per Hour'
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
                            maxTicksLimit: 90,
                            callback: function(value, index, values) {
                                // Remove label if value is 0 in the "No Data" dataset
                                if (this.chart.data.datasets[2].data[index] === 0) {
                                    return '';
                                }
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Months'
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
        var ctx1 = document.getElementById("rate_graph_skills");
        var rate_graph_skills = new Chart(ctx1, rate_graph_skills_config);
        /****************************************************************************************  */

        const rate_graph_doi_config = {
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
                    usePointStyle: true,
                    pointStyle: 'circle',
                    labels: {
                        filter: function(item, chart) {
                            // Logic to remove a particular legend item goes here
                            return !item.text.includes('No Data');
                        }
                    },
                },

                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: 0,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Degrees of Independence per Hour '
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
                            //maxTicksLimit: 90,
                            callback: function(value, index, values) {
                                // Remove label if value is 0 in the "No Data" dataset
                                if (this.chart.data.datasets[2].data[index] === 0) {
                                    return '';
                                }
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Months'
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
        var ctx2 = document.getElementById("rate_graph_doi");
        var rate_graph_doi = new Chart(ctx2, rate_graph_doi_config);
        /***************************************************************************************** */

        $("#client_dropdown_list").on('change', function(e) {
            e.preventDefault;
             

            let client_id = $("#client_dropdown_list").val();
            //console.log(client_id);
            var ajaxRequest = $.ajax({
                url: '/graphs/rate',
                type: 'post',
                data: {
                    "client_id": client_id
                },
                beforeSend: function(xhr) {
                    $("#client_dropdown_list").prop("disabled", true);
                     
                    $('#next').prop("disabled", true);
                    $('#back').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    rate_graph_skills.data = response.data.skill_data.graph_data;
                    rate_graph_skills.options.annotation.annotations = response.data.skill_data.phase_line;
                    rate_graph_skills.data.datasets[2].pointStyle = noDataPointImage;
                    rate_graph_skills.update();
                    $("#skill_phaseline_table").html(response.data.skill_data.phase_line_table);

                    rate_graph_doi.data = response.data.doi_data.graph_data;
                    rate_graph_doi.options.annotation.annotations = response.data.doi_data.phase_line;
                    rate_graph_doi.data.datasets[2].pointStyle = noDataPointImage;
                    rate_graph_doi.update();
                    $("#doi_phaseline_table").html(response.data.doi_data.phase_line_table);
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
                
                $('#next').prop("disabled", false);
                $('#back').prop("disabled", false);
            });


        }); //On change function ends

        /***************************************************************************************** */

    });


    /***************************************************************************************** */
</script>
<?= $this->endSection() ?>