<?= $this->extend("layout/master-profile") ?>
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
                                <?= view('ClientProfile/Graphs/Cumulative/_tabs', ['tab' => 'graph-domain']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="row justify-content-end">
                                    <div class="col-md-5 col-sm-12">
                                        <select class="form-control" name="choices-single-default" id="sDomain">
                                            <option value="" selected>All Domains</option>
                                        </select>
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-5 col-sm-12">
                                        <select class="form-control" name="choices-single-default" id="sGoal">
                                            <option value="" selected>All Goals</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-12">
                                        <div class="gap-2 float-end">
                                            <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right "><i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>

                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div id="program_card_container"></div>

                            <div class="card-body">
                                <div class="text-center mb-2">
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
                                <canvas id="cumGraphChartCombined" class="chart_content" height="100"></canvas>

                            </div>
                            <!--end col-->
                        </div>
                    </div>
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
        $('#sDomain').select2();
        $('#sGoal').select2();

        // Listen to the domain selection change
        $('#sDomain').on('change', function() {
            var domain_id = $(this).val();

            // Clear the existing options in the Goals dropdown and add "All Goals" option
            $('#sGoal').empty().append('<option value="">All Goals</option>');

            if (domain_id !== '') {
                // Send an AJAX request to fetch goals for the selected domain
                $.ajax({
                    url: '<?= base_url('graphs/cumulative/getClientDomainGoals') ?>',
                    type: 'POST',
                    data: {
                        client_id: client_id,
                        domain_id: domain_id,
                    },
                    success: function(response) {
                        // Log the response for debugging


                        // Populate the Goals dropdown with the fetched goals from the object
                        if (response && response.length > 0) {
                            $.each(response, function(index, goal) {
                                $('#sGoal').append(
                                    $('<option></option>').attr('value', goal.id).text(goal.name + ' (' + goal.goal_code + ')')
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error fetching goals:', error);
                    }
                });
            }
        });
        /***************************************************************************************** */

        const config = {
            type: 'line',
            data: [],
            options: {
                tooltips: {
                    intersect: false,
                },
                legend: {
                    display: false,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    labels: {
                        filter: function(item, chart) {
                            // Logic to remove a particular legend item goes here
                            return item;
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
                        offset: true, // ✅ Adds spacing before the first tick
                        ticks: { //rotating the x-axis labels
                            autoSkip: true,
                            maxRotation: 90,
                            minRotation: 45,
                            maxTicksLimit: 90,

                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'ًWeek Ending'
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
        var ctx_combined = document.getElementById("cumGraphChartCombined");
        const cumulative_graph_combined = new Chart(ctx_combined, config);
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

        $('#legend_toggle_skills').on('click', function() {
            skillsVisible = !skillsVisible;
            applyLegendDatasetVisibility(cumulative_graph_combined);
            cumulative_graph_combined.update();
        });

        $('#legend_toggle_doi').on('click', function() {
            doiVisible = !doiVisible;
            applyLegendDatasetVisibility(cumulative_graph_combined);
            cumulative_graph_combined.update();
        });
        /***************************************************************************************** */



        $("#search").on('click', function(e) {
            e.preventDefault;
            search = $(this);
            let sDomain = $("#sDomain").val();
            let sGoal = $("#sGoal").val();
            var ajaxRequest = $.ajax({
                url: '/graphs/cumulative/domains-and-goals',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "domain_id": sDomain,
                    "goal_id": sGoal
                },
                beforeSend: function(xhr) {
                    search.prop("disabled", true);
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




                    cumulative_graph_combined.data = response.data.combined_graph_data;
                    applyLegendDatasetVisibility(cumulative_graph_combined);
                    cumulative_graph_combined.update();



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
                search.prop("disabled", false);
                $('#next').prop("disabled", false);
            });

        }); //On change function ends

        function loadDomains() {
            // Clear the existing options in the Goals dropdown and add "All Goals" option
            $('#sDomain').empty().append('<option value="">All Domains</option>');
            $('#sGoal').empty().append('<option value="">All Goals</option>');

            if (client_id !== '') {
                // Send an AJAX request to fetch goals for the selected domain
                $.ajax({
                    url: '<?= base_url('graphs/cumulative/getClientDomains') ?>',
                    type: 'POST',
                    data: {
                        client_id: client_id,
                    },
                    success: function(response) {

                        // Populate the Goals dropdown with the fetched goals from the object
                        if (response && response.length > 0) {
                            $.each(response, function(index, domain) {
                                $('#sDomain').append(
                                    $('<option></option>').attr('value', domain.id).text(domain.name + ' (' + domain.domain_code + ')')
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error fetching domains:', error);
                    }
                });
            }
        };
        loadDomains();
        /***************************************************************************************** */
        function loadAllDomainGraphs() {
            let sDomain = '';
            let sGoal = '';
            var ajaxRequest = $.ajax({
                url: '/graphs/cumulative/domains-and-goals',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "domain_id": sDomain,
                    "goal_id": sGoal
                },

            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    var current_programs;

                    if (response.client.description) {
                        current_programs = response.client.description;
                    } else {
                        current_programs = ''; //response.clientActiveProgram;
                    }
                    $("#current_programs").html(current_programs);



                    cumulative_graph_combined.data = response.data.combined_graph_data;
                    applyLegendDatasetVisibility(cumulative_graph_combined);
                    cumulative_graph_combined.update();



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
        loadAllDomainGraphs();
        /**************************************************************************************** */

    });
</script>
<?= $this->endSection() ?>
