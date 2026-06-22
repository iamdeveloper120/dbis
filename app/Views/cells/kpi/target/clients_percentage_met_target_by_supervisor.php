 <div class="row">
     <div class="col-md-12">
         <?php foreach ($data['supervisorData'] as $supervisorData) : ?>
             <?php
                $supervisorId = $supervisorData['supervisorId'];
                $supervisorName = $supervisorData['supervisorName'];
                $tableDataHTML = $supervisorData['tableDataHTML'];
                $graphData = $supervisorData['graphData'];
                $chartDataJS = $supervisorData['chartDataJS'];
                ?>
             <div class="card" id="">
                 <div class="card-header border-bottom-dashed">
                     <div class="row g-4 align-items-center">
                         <div class="col-sm">
                             <div>
                                 <h5 class="card-title mb-0"><?= $supervisorName ?></h5>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="card-body">

                     <!-- Nav tabs -->
                     <ul class="nav nav-pills nav-custom nav-custom-light mb-3" role="tablist">
                         <li class="nav-item" role="presentation">
                             <a class="nav-link active" data-bs-toggle="tab" href="#nav-light-graph-<?= $supervisorId ?>" role="tab" aria-selected="true">
                                 Graph
                             </a>
                         </li>
                         <li class="nav-item" role="presentation">
                             <a class="nav-link" data-bs-toggle="tab" href="#nav-light-graph-data-<?= $supervisorId ?>" role="tab" aria-selected="false" tabindex="-1">
                                 Graph Data
                             </a>
                         </li>
                         <li class="nav-item" role="presentation">
                             <a class="nav-link" data-bs-toggle="tab" href="#nav-light-client-data-<?= $supervisorId ?>" role="tab" aria-selected="false" tabindex="-1">
                                 Clients Monthly Data
                             </a>
                         </li>
                     </ul>
                     <div class="tab-content text-muted">
                         <div class="tab-pane active show" id="nav-light-graph-<?= $supervisorId ?>" role="tabpanel" style="padding-top: 10px;">
                             <!-- Create a canvas element for the bar chart -->
                             <canvas id="barChart<?= $supervisorId ?>" height="80"></canvas>
                             <!-- JavaScript code for generating the bar chart -->
                             <script>
                                 var chartData = <?= $chartDataJS ?>;

                                 var ctx = document.getElementById('barChart<?= $supervisorId ?>').getContext('2d');
                                 var chart = new Chart(ctx, {
                                     type: 'bar',
                                     data: {
                                         labels: chartData.labels,
                                         datasets: [{
                                             label: 'Percentage',
                                             data: chartData.data,
                                             backgroundColor: chartData.colors,
                                             borderColor: chartData.colors,
                                             borderWidth: 1
                                         }]
                                     },
                                     options: {
                                         legend: {
                                             display: false,

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
                                                     beginAtZero: true,
                                                     max: 100,
                                                     callback: function(value) {
                                                         return value + '%';
                                                     }
                                                 },
                                                 scaleLabel: {
                                                     display: true,
                                                     labelString: "The percentage of supervisor's clients that met their target rate per month (%)"
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
                             </script>
                         </div>
                         <div class="tab-pane" id="nav-light-graph-data-<?= $supervisorId ?>" role="tabpanel">
                             <?= $graphData ?>
                         </div>
                         <div class="tab-pane" id="nav-light-client-data-<?= $supervisorId ?>" role="tabpanel">
                             <?= $tableDataHTML ?>
                         </div>
                     </div>
                 </div>

             </div>
         <?php endforeach; ?>

     </div>
 </div>
 <script>
     $(document).ready(function() {
         $('.table').DataTable({
             response: false,
             lengthChange: false,
             lengthMenu: [
                 [5, 10, 25, 50, -1],
                 ['5 rows', '10 rows', '25 rows', '50 rows', 'Show all']
             ],
             initComplete: function(settings, json) {
                 var table = this.api();
                 var wrapper = $(table.table().container()).closest('.dataTables_wrapper');

                 var buttons = [
                     'pageLength',
                     {
                         extend: 'excelHtml5',
                         exportOptions: {
                             format: {
                                 body: function(data, row, column, node) {
                                     if ($(node).find('span').length) {
                                         return $(node).find('span').text();
                                     } else {
                                         return data;
                                     }
                                 }
                             }
                         }
                     }
                 ];

                 new $.fn.dataTable.Buttons(table, {
                     buttons: buttons
                 });

                 table.buttons().container().appendTo(wrapper.find('.col-md-6:eq(0)'));
             }
         });
     });
 </script>