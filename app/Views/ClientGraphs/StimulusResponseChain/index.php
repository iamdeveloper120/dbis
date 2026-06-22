<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row justify-content-end">
                    <div class="col-md-3 col-sm-12">
                        <select class="form-control" id="client_dropdown_list">
                            <option value="">SELECT CLIENT</option>
                            <?php foreach ($clients as $client) : ?>
                                <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <select class="form-control" id="sDomain">
                            <option value="">SELECT DOMAIN</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <select class="form-control" id="sGoal">
                            <option value="">SELECT GOAL</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <select class="form-control" id="sTarget">
                            <option value="">ALL TARGETS</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="gap-2 float-end">
                            <button type="button" id="clear_search" class="btn btn-success bg-gradient waves-effect waves-light btn-label right">
                                <i class="ri-calendar-event-line label-icon align-middle fs-16 ms-2"></i>Clear
                            </button>
                            <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right">
                                <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search
                            </button>
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="back" title="Previous client">
                                    <i class="ri-arrow-left-line"></i>
                                </button>&nbsp;
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="next" title="Next client">
                                    <i class="ri-arrow-right-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body" id="graphs_container">
                <div class="alert alert-info mb-0">
                    Select Client, Domain, Goal, and optional Target, then press Search.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        var chartInstances = [];

        $('#client_dropdown_list').select2();
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

        function resetDomainGoalTarget() {
            $('#sDomain').empty().append('<option value="">SELECT DOMAIN</option>').val('').trigger('change');
            $('#sGoal').empty().append('<option value="">SELECT GOAL</option>').val('').trigger('change');
            $('#sTarget').empty().append('<option value="">ALL TARGETS</option>').val('').trigger('change');
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
                var canvasId = 'stimulus_response_chain_chart_' + index;
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
                var canvasId = 'stimulus_response_chain_chart_' + index;
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

        function populateDomains(client_id) {
            resetDomainGoalTarget();
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

        function populateGoals(client_id, domain_id) {
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

        function populateTargets(client_id, goal_id) {
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

        $('#next').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var optionsCount = dropdown.find('option').length;

            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex + 1;
                if (nextIndex >= optionsCount) {
                    nextIndex = 1;
                }
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
            } else {
                showAlert('', 'Client not exist', 'info');
            }
        });

        $('#back').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var optionsCount = dropdown.find('option').length;

            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex - 1;
                if (nextIndex <= 0) {
                    nextIndex = optionsCount - 1;
                }
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
            } else {
                showAlert('', 'Client not exist', 'info');
            }
        });

        $("#clear_search").click(function() {
            $('#client_dropdown_list').val('').trigger('change');
            resetDomainGoalTarget();
            clearCharts();
            $('#graphs_container').html('<div class="alert alert-info mb-0">Select Client, Domain, Goal, and optional Target, then press Search.</div>');
        });

        $("#client_dropdown_list").on('change', function() {
            var client_id = $(this).val();
            populateDomains(client_id);
            clearCharts();
        });

        $("#sDomain").on('change', function() {
            var client_id = $("#client_dropdown_list").val();
            var domain_id = $(this).val();
            populateGoals(client_id, domain_id);
            clearCharts();
        });

        $("#sGoal").on('change', function() {
            var client_id = $("#client_dropdown_list").val();
            var goal_id = $(this).val();
            populateTargets(client_id, goal_id);
            clearCharts();
        });

        $("#search").on('click', function(e) {
            e.preventDefault();
            var search = $(this);
            var client_id = $("#client_dropdown_list").val();
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
                    $('#client_dropdown_list').prop("disabled", true);
                    $('#sDomain').prop("disabled", true);
                    $('#sGoal').prop("disabled", true);
                    $('#sTarget').prop("disabled", true);
                    $('#next').prop("disabled", true);
                    $('#back').prop("disabled", true);
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
                $('#client_dropdown_list').prop("disabled", false);
                $('#sDomain').prop("disabled", false);
                $('#sGoal').prop("disabled", false);
                $('#sTarget').prop("disabled", false);
                $('#next').prop("disabled", false);
                $('#back').prop("disabled", false);
                search.prop("disabled", false);
            });
        });
    });
</script>
<?= $this->endSection() ?>
