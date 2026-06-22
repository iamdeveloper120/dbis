<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="tab-content text-muted mx-n3 pt-2 px-2">
    <div class="tab-pane active show" role="tabpanel">
        <div class="card">
            <div class="card-header border-bottom-dashed pb-0 mb-0">
                <?= view('ClientProfile/Graphs/Rate/_tabs', ['tab' => 'graphs-rate']) ?>
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
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        var noDataPointImage = new Image();
        noDataPointImage.src = "<?php echo base_url() ?>assets/images/legend-black-8.jpg";

        /***************************************************************************************** */
        let client_id = "<?= $client->id ?>"; // passed from controller
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        // Page-level loader binding (ONLY for this page)
        $(document).ajaxStart(function() {
            showPageLoader();
        });

        $(document).ajaxStop(function() {
            hidePageLoader();
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

        function loadRateGraph() {
            var ajaxRequest = $.ajax({
                url: '/graphs/rate',
                type: 'post',
                data: {
                    "client_id": client_id
                },

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
        }; //On change function ends

        loadRateGraph();

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


    /***************************************************************************************** */
</script>
<?= $this->endSection() ?>