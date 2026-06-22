<?= $this->extend("layout/master-profile") ?>

<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2">
    <div class="card border">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h5 class="card-title mb-0">Stimulus Response Chain Graphs</h5>
                </div>
                <div class="col-md-7">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                        <div style="width: 220px;">
                            <select class="form-control" id="sDomain">
                                <option value="">SELECT DOMAIN</option>
                            </select>
                        </div>
                        <div style="width: 220px;">
                            <select class="form-control" id="sGoal">
                                <option value="">SELECT GOAL</option>
                            </select>
                        </div>
                        <div style="width: 220px;">
                            <select class="form-control" id="sTarget">
                                <option value="">ALL TARGETS</option>
                            </select>
                        </div>
                        <button type="button" id="clear_search" class="btn btn-success bg-gradient waves-effect waves-light btn-label right">
                            <i class="ri-calendar-event-line label-icon align-middle fs-16 ms-2"></i>Clear
                        </button>
                        <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right">
                            <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body" id="graphs_container">
            <div class="alert alert-info mb-0">
                Select Domain, Goal, and optional Target, then press Search.
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        $(document).ajaxStart(function() {
            showPageLoader();
        });

        $(document).ajaxStop(function() {
            hidePageLoader();
        });

        var client_id = "<?= $client->id ?>";
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        var chartInstances = [];

        $('#sDomain').select2();
        $('#sGoal').select2();
        $('#sTarget').select2();

        function clearCharts() {
            chartInstances.forEach(function(chart) {
                chart.destroy();
            });
            chartInstances = [];
            $('#graphs_container').empty();
        }

        function resetGoalTarget() {
            $('#sGoal').empty().append('<option value="">SELECT GOAL</option>').val('').trigger('change');
            $('#sTarget').empty().append('<option value="">ALL TARGETS</option>').val('').trigger('change');
        }

        function resetTarget() {
            $('#sTarget').empty().append('<option value="">ALL TARGETS</option>').val('').trigger('change');
        }

        function methodLabel(method) {
            if (method === 'total_task') return 'Total Chain';
            if (method === 'forward') return 'Forward Chain';
            if (method === 'backward') return 'Backward Chain';
            return method;
        }

        function buildChartOptions(target) {
            var yMax = parseFloat(target.y_axis_max || 0);
            var yStep = parseFloat(target.y_axis_step || 1);
            return {
                tooltips: {
                    intersect: false,
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: parseFloat(target.y_axis_min || 0),
                            max: yMax,
                            stepSize: yStep,
                            precision: target.graph_type === 'steps' ? 0 : 2,
                            callback: function(value) {
                                if (target.graph_type === 'percentage') {
                                    return value + '%';
                                }
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: target.y_axis_label || 'Values'
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        offset: true,
                        ticks: {
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
                        }
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            };
        }

        function renderGraphs(targets) {
            clearCharts();

            if (!targets || !targets.length) {
                $('#graphs_container').html('<div class="alert alert-warning mb-0">No stimulus response chain graph data found for the selected filters.</div>');
                return;
            }

            var html = '';
            targets.forEach(function(target, index) {
                var canvasId = 'profile_stimulus_response_chain_chart_' + index;
                html += `
                    <div class="card border mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">${target.target_name} <span class="badge bg-info-subtle text-info ms-2">${methodLabel(target.chain_method)}</span></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="${canvasId}" class="chart_content" height="90"></canvas>
                        </div>
                    </div>
                `;
            });

            $('#graphs_container').html(html);

            targets.forEach(function(target, index) {
                var canvasId = 'profile_stimulus_response_chain_chart_' + index;
                var ctx = document.getElementById(canvasId);
                if (!ctx) return;

                var chart = new Chart(ctx, {
                    type: 'line',
                    data: target.chart_data || {
                        labels: [],
                        datasets: []
                    },
                    options: buildChartOptions(target)
                });
                chartInstances.push(chart);
            });
        }

        function loadDomains() {
            $('#sDomain').empty().append('<option value="">SELECT DOMAIN</option>').val('').trigger('change');
            resetGoalTarget();

            if (!client_id) return;
            $.ajax({
                url: '<?= base_url('graphs/stimulus-response-chain/getClientDomains') ?>',
                type: 'POST',
                data: {
                    client_id: client_id
                },
                success: function(response) {
                    if (response && response.length > 0) {
                        $.each(response, function(index, domain) {
                            $('#sDomain').append(
                                $('<option></option>').attr('value', domain.id).text(domain.name + ' (' + domain.domain_code + ')')
                            );
                        });
                    } else {
                        $('#sDomain').append('<option value="" disabled>No Domains Found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, "Request failed: " + status + '<br>' + error, 'error');
                }
            });
        }

        function loadGoals(domain_id) {
            resetGoalTarget();
            if (!client_id || !domain_id) return;

            $.ajax({
                url: '<?= base_url('graphs/stimulus-response-chain/getClientDomainGoals') ?>',
                type: 'POST',
                data: {
                    client_id: client_id,
                    domain_id: domain_id
                },
                success: function(response) {
                    if (response && response.length > 0) {
                        $.each(response, function(index, goal) {
                            $('#sGoal').append(
                                $('<option></option>').attr('value', goal.id).text(goal.name + ' (' + goal.goal_code + ')')
                            );
                        });
                    } else {
                        $('#sGoal').append('<option value="" disabled>No Goals Found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, "Request failed: " + status + '<br>' + error, 'error');
                }
            });
        }

        function loadTargets(goal_id) {
            resetTarget();
            if (!client_id || !goal_id) return;

            $.ajax({
                url: '<?= base_url('graphs/stimulus-response-chain/getClientGoalTargets') ?>',
                type: 'POST',
                data: {
                    client_id: client_id,
                    goal_id: goal_id
                },
                success: function(response) {
                    if (response && response.length > 0) {
                        $.each(response, function(index, target) {
                            $('#sTarget').append(
                                $('<option></option>').attr('value', target.id).text(target.name)
                            );
                        });
                    } else {
                        $('#sTarget').append('<option value="" disabled>No Targets Found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, "Request failed: " + status + '<br>' + error, 'error');
                }
            });
        }

        $('#sDomain').on('change', function() {
            var domain_id = $(this).val();
            loadGoals(domain_id);
            clearCharts();
        });

        $('#sGoal').on('change', function() {
            var goal_id = $(this).val();
            loadTargets(goal_id);
            clearCharts();
        });

        $('#clear_search').on('click', function() {
            loadDomains();
            clearCharts();
            $('#graphs_container').html('<div class="alert alert-info mb-0">Select Domain, Goal, and optional Target, then press Search.</div>');
        });

        $("#search").on('click', function(e) {
            e.preventDefault();
            var search = $(this);
            var domain_id = $("#sDomain").val();
            var goal_id = $("#sGoal").val();
            var target_id = $("#sTarget").val();

            var ajaxRequest = $.ajax({
                url: '/graphs/stimulus-response-chain',
                type: 'POST',
                data: {
                    client_id: client_id,
                    domain_id: domain_id,
                    goal_id: goal_id,
                    target_id: target_id
                },
                beforeSend: function() {
                    $('#sDomain').prop("disabled", true);
                    $('#sGoal').prop("disabled", true);
                    $('#sTarget').prop("disabled", true);
                    search.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    renderGraphs((response.data || {}).targets || []);
                } else if (response.status === 'validation_error') {
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
                $('#sDomain').prop("disabled", false);
                $('#sGoal').prop("disabled", false);
                $('#sTarget').prop("disabled", false);
                search.prop("disabled", false);
            });
        });

        loadDomains();
    });
</script>
<?= $this->endSection() ?>
