<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    #legend_toggle_skills,
    #legend_toggle_doi {
        cursor: pointer;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="tab-content text-muted">
            <div class="tab-pane active show" role="tabpanel">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom-dashed pb-0 mb-0">
                                <?= view('ClientGraphs/Cumulative/_tabs', ['tab' => 'graph']) ?>
                            </div>
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

                                        <input id="start_date" type="text" placeholder="Start Date" class="form-control">
                                    </div>
                                    <div class="col-md-2">

                                        <input id="end_date" type="text" placeholder="End Date" class="form-control">
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
                            <div id="program_card_container"></div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <span id="legend_toggle_skills">
                                            <img class="img" src="/assets/images/legend-black.png" alt="" width="35">
                                            <span class="">Skills Retained</span>
                                        </span>
                                        &nbsp; &nbsp; &nbsp;
                                        <span id="legend_toggle_doi">
                                            <img class="img" src="/assets/images/legend-blue.png" alt="" width="35">
                                            <span class="">Degrees of independence</span>
                                        </span>
                                    </div>

                                </div>
                                <div class="row justify-content-end" style="margin-top: -20px;">
                                    <div class="col-md-3" style="text-align: right;">
                                        <span id="current_programs1"></span>
                                    </div>
                                </div>
                                <hr style="border:none">
                                <canvas id="cumGraphChart" class="chart_content" height="100"></canvas>
                                <hr class="sidebar-divider">
                                <div id='p_table'></div>
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
        var noSessionPointImage = new Image();
        noSessionPointImage.src = "<?php echo base_url() ?>assets/images/legend-black-8.jpg";
        var ctx = document.getElementById("cumGraphChart");
        var phaseline_annotation = [];

        const config2 = {
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
                            return !item.text.includes('No Session');
                        }
                    },
                },

                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: 0
                        },
                        afterDataLimits(scale) {
                            scale.max += 10;
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Cumulative Skills Retained Across All Domains'
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
                            //maxRotation: 90,
                            //minRotation: 45,
                            //maxTicksLimit: 90,
                            callback: function(value, index, values) {
                                if (this.chart.data.datasets[2].data[index] === 0) {
                                    this.chart.data.datasets[2].data[index] === -0.1;
                                }
                                // Remove label if value is 0 in the "No Data" dataset
                                if (this.chart.data.datasets[2].data[index] === 0) {
                                    return '';
                                }
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Week Ending'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false,
                        },

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
        const cumulative_graph = new Chart(ctx, config2);
        let skillsVisible = true;
        let doiVisible = true;

        function findDatasetIndexByKeyword(chart, keyword) {
            if (!chart || !chart.data || !Array.isArray(chart.data.datasets)) {
                return -1;
            }
            const term = keyword.toLowerCase();
            return chart.data.datasets.findIndex(ds => (ds.label || '').toLowerCase().includes(term));
        }

        function applyLegendDatasetVisibility(chart) {
            const skillsIndex = findDatasetIndexByKeyword(chart, 'skills');
            const doiIndex = findDatasetIndexByKeyword(chart, 'degree');

            if (skillsIndex > -1) {
                chart.getDatasetMeta(skillsIndex).hidden = !skillsVisible;
            }
            if (doiIndex > -1) {
                chart.getDatasetMeta(doiIndex).hidden = !doiVisible;
            }
        }

        function applyNoSessionPointStyle(chart) {
            const noSessionIndex = findDatasetIndexByKeyword(chart, 'no session');
            if (noSessionIndex > -1) {
                chart.data.datasets[noSessionIndex].pointStyle = noSessionPointImage;
            }
        }

        $('#legend_toggle_skills').on('click', function() {
            skillsVisible = !skillsVisible;
            applyLegendDatasetVisibility(cumulative_graph);
            cumulative_graph.update();
        });

        $('#legend_toggle_doi').on('click', function() {
            doiVisible = !doiVisible;
            applyLegendDatasetVisibility(cumulative_graph);
            cumulative_graph.update();
        });
        /****************************************************************************************  */
        var dateConfig = {
            dateFormat: "d-M-Y",
            maxDate: "today",
            weekNumbers: true,
        };

        $("#client_dropdown_list").on('change', function(e) {

        });

        $("#search").on('click', function(e) {
            e.preventDefault;
            search = $(this);
            let client_id = $("#client_dropdown_list").val();
            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();
            var ajaxRequest = $.ajax({
                url: '/graphs/cumulative',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                },
                beforeSend: function(xhr) {
                    $('#client_dropdown_list').prop("disabled", true);
                    search.prop("disabled", true);
                    $('#next').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    let programHtml = '';

                    if (response.client && response.client.description && response.client.description.trim() !== '') {

                        programHtml = `
                                        <div class="card-header p-0 border-0 bg-light-subtle">
                                            <div class="row g-0 text-center">
                                                <div class="col-12">
                                                    <div class="p-3 border border-dashed border-start-0">
                                                        <h5 class="mb-1">List of Programs</h5>
                                                        <p class="text-muted mb-0" id="current_programs">
                                                            ${response.client.description}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                    }

                    // Render OR clear
                    $("#program_card_container").html(programHtml);
                    cumulative_graph.data = response.data.graph_data;
                    applyNoSessionPointStyle(cumulative_graph);
                    cumulative_graph.options.annotation.annotations = response.data.phaseline;
                    //console.log(response.data.phaseline);
                    //cumulative_graph.options.plugins.annotation.annotations = response.graph_data.phaseline;
                    applyLegendDatasetVisibility(cumulative_graph);

                    cumulative_graph.update(); // Calling update now animates the position of March from 90 to 50.

                    $("#p_table").html(response.data.table);
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
                $('#next').prop("disabled", false);
            });


        }); //On change function ends

        /***************************************************************************************** */

    });


    /***************************************************************************************** */
</script>
<?= $this->endSection() ?>
