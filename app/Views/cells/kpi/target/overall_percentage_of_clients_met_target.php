 <div class="row">
     <div class="col-lg-12">
         <div class="card" id="">
             <div class="card-header border-bottom-dashed">
                 <div class="row g-4 align-items-center">
                     <div class="col-sm">
                         <div>
                             <h5 class="card-title mb-0">The percentage of months each client met their target rate since start of services</h5>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="card-body border-bottom-dashed border-bottom">
                 <div class="row">
                     <div class="col-md-12" style="text-align: center;">
                         <span id="chartTitle"></span>
                         <canvas id="overall_percentage_of_clients_met_target" height="80"></canvas>
                     </div>
                 </div>

             </div>
         </div>
     </div>
 </div>
 <!-- top offcanvas -->
 <div class="modal fadeInRight" id="overall_percentage_of_clients_met_target_modal">
     <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
         <div class="modal-content">
             <div class="modal-header bg-light p-3">
                 <h5 class="modal-title" id="addNewclientModalTitle"></h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
             </div>

             <div class="modal-body">
                 <div class="col-md-12">
                     <div class="alert alert-light alert-solid" role="alert" id='cTarget'></div>

                 </div>
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
     /***************************************************************************************** */
     var csrfToken = "<?= csrf_hash() ?>";
     $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': csrfToken,
         }
     });
     /***************************************************************************************** */
     var overall_percentage_of_clients_met_target_chart = '';
     <?php

        $ids_json = json_encode($ids);
        ?>
     var ids = <?php echo $ids_json; ?>;

     function show_client_overall_data(event, chartElements) {
         if (chartElements && chartElements.length > 0) {
             var index = chartElements[0]._index;
             var label = overall_percentage_of_clients_met_target_chart.data.labels[index];
             var value = overall_percentage_of_clients_met_target_chart.data.datasets[0].data[index];
             //alert('Clicked on ' + label + ': ' + value);
             var id = ids[index];
             //swalAert('Client', 'Clicked on ' + label + ': ' + ', ID: ' + id, 'info');

             var data = {
                 'client_id': id,
                 'internal_mrn': label
             };
             var ajaxRequest = $.ajax({
                 url: '<?php echo base_url() ?>kpi/client-target/data',
                 type: 'post',
                 data: data,
                 beforeSend: function(xhr) {

                 }
             });
             ajaxRequest.done(function(response) {
                 if (response.status == 'success') {
                     $("#overall_percentage_of_clients_met_target_modal .modal-title").html(label);
                     $("#overall_percentage_of_clients_met_target_modal #cTarget").html('<b>Skills Target: </b>' + response.skills_target + ' - <b>DOI Target:</b> ' + response.doi_target);
                     $("#overall_percentage_of_clients_met_target_modal #tData").html(response.data);
                     $('#overall_percentage_of_clients_met_target_modal').modal('show');

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
     document.addEventListener("DOMContentLoaded", function() {
         var overall_percentage_of_clients_met_target_chartLabels = <?php echo json_encode($chartLabels); ?>;
         var overall_percentage_of_clients_met_target_chartPercentages = <?php echo json_encode($chartPercentages); ?>;

         var overall_percentage_of_clients_met_target_chartConfig = {
             type: 'bar',
             data: {
                 labels: overall_percentage_of_clients_met_target_chartLabels,
                 datasets: [{
                     label: 'Percentage',
                     data: overall_percentage_of_clients_met_target_chartPercentages,
                     backgroundColor: 'rgba(75, 192, 192, 0.2)',
                     borderColor: 'rgba(75, 192, 192, 1)',
                     borderWidth: 1
                 }],
             },
             options: {
                 onClick: show_client_overall_data,
                 scales: {
                     yAxes: [{
                         ticks: {
                             beginAtZero: true,
                             callback: function(value, index, values) {
                                 return value + "%";
                             }
                         },
                         scaleLabel: {
                             display: true,
                             labelString: 'Percentage of months (%)'
                         },
                         gridLines: {
                             drawOnChartArea: false
                         }
                     }],
                     xAxes: [{
                         gridLines: {
                             drawOnChartArea: false
                         },
                         scaleLabel: {
                             display: true,
                             labelString: 'Clients'
                         },
                     }],
                 },
                 legend: {
                     display: false,
                     labels: {
                         usePointStyle: true,
                         pointStyle: 'circle',
                     }
                 },
                 title: {
                     display: true,
                     text: '',
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return "Percentage: " + tooltipItem.yLabel + "%";
                         },
                     },
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
             },
         };

         var overall_percentage_of_clients_met_target_canvas = document.getElementById("overall_percentage_of_clients_met_target").getContext("2d");
         overall_percentage_of_clients_met_target_chart = new Chart(overall_percentage_of_clients_met_target_canvas, overall_percentage_of_clients_met_target_chartConfig);

         var chartTitle = document.getElementById("chartTitle");
         var aboveSixtyCount = overall_percentage_of_clients_met_target_chartPercentages.filter(function(percentage) {
             return percentage > 0 && percentage >= 60;
         }).length;
         var totalCount = overall_percentage_of_clients_met_target_chartPercentages.filter(function(percentage) {
             return percentage > 0;
         }).length;
         var aboveSixtyPercentage = (aboveSixtyCount / totalCount) * 100;
         chartTitle.innerHTML = "Percentage of Clients Meeting Target (≥ 60%) = " + aboveSixtyPercentage.toFixed(2) + "%";
     });
 </script>