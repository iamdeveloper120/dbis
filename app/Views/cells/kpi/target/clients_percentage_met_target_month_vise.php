<div class="row">
    <div class="col-lg-12">
        <div class="card" id="">
            <div class="card-header border-bottom-dashed">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">The percentage of all clients that met their target rate each month</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12" style="text-align: center;">
                            <span id="chartTitle2"></span>
                            <canvas id="clients_percentage_met_targe_month_vise" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- top offcanvas -->
<div class="modal fadeInRight" id="clients_percentage_met_target_month_vise_modal">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="addNewclientModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>

            <div class="modal-body">
                <div id='tData' class="col-md-12"></div>
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>

                </div>
            </div>

        </div>
    </div>
</div>
<script>
    function show_month_vise_data(event, chartElements) {
        if (chartElements && chartElements.length > 0) {
            var index = chartElements[0]._index;
            var label = clients_percentage_met_targe_month_vise_chart.data.labels[index];
            var value = clients_percentage_met_targe_month_vise_chart.data.datasets[0].data[index];


            var data = {
                'month': label,
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>kpi/client-target-month-vise/data',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {

                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    $("#clients_percentage_met_target_month_vise_modal .modal-title").html(label);
                    $("#clients_percentage_met_target_month_vise_modal #tData").html(response.data);
                    $('#clients_percentage_met_target_month_vise_modal').modal('show');

                } else {
                    swalAert(response.statusText, response.message, response.status);

                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                swalAert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {

            });
        }
    }
    var clients_percentage_met_targe_month_vise_canvas = document.getElementById('clients_percentage_met_targe_month_vise').getContext('2d');
    var clients_percentage_met_targe_month_vise_chart = new Chart(clients_percentage_met_targe_month_vise_canvas, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Percentage',
                data: <?php echo json_encode($dataset); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,

            }]
        },
        options: {
            onClick: show_month_vise_data,
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return "Percentage: " + tooltipItem.yLabel + "%";
                    },
                },
            },
            legend: {
                display: false,
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',

                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        drawOnChartArea: false
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Months'
                    },
                }],
                yAxes: [{
                    ticks: {
                        max: 100,
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return value + "%";
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Percentage of clients (%)'
                    },
                    gridLines: {
                        drawOnChartArea: false
                    }
                }]
            },
            annotation: {
                drawTime: 'afterDatasetsDraw',
                annotations: [{
                    type: 'line',
                    mode: 'horizontal',
                    scaleID: 'y-axis-0',
                    value: 60,
                    borderColor: 'rgba(255, 0, 0, 0.3)',
                    borderWidth: 1,
                }],
            },
        }
    });
    var clients_percentage_met_targe_month_vise_chartPercentages = <?php echo json_encode($dataset); ?>;
    var chartTitle2 = document.getElementById("chartTitle2");
    var aboveSixtyCount2 = clients_percentage_met_targe_month_vise_chartPercentages.filter(function(percentage) {
        return percentage > 0 && percentage >= 60;
    }).length;
    var totalCount2 = clients_percentage_met_targe_month_vise_chartPercentages.filter(function(percentage) {
        return percentage > 0;
    }).length;
    var aboveSixtyPercentage2 = (aboveSixtyCount2 / totalCount2) * 100;
    chartTitle2.innerHTML = "Percentage of Clients Meeting Target (≥ 60%) = " + aboveSixtyPercentage2.toFixed(2) + "%";
</script>