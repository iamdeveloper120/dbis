 <div class="table-responsive">
     <table id="target_dataTable" class="table table-bordered align-middle" style="width:100%">
         <thead>
             <tr>
                 <th>Domain Code</th>
                 <th>Domain</th>
                 <th>Goal Code</th>
                 <th>Goal</th>
                 <th>Target</th>
                 <th>Action</th>
             </tr>
         </thead>
         <tbody>
             <?php foreach ($targets as $target) : ?>
                 <tr>
                     <td><?= $target->domain_code; ?></td>
                     <td><?= $target->domain_name; ?></td>
                     <td><?= $target->goal_code; ?></td>
                     <td><?= $target->goal_name; ?></td>
                     <td><?= $target->name; ?></td>
                     <td class="dt-nowrap">
                         <button id="<?= $target->id; ?>" type="button" class="btn btn-sm btn-outline-primary addToClient"><i class="ri-user-add-line align-bottom me-1"></i>Add To Client</button>
                     </td>
                 </tr>
             <?php endforeach; ?>
         </tbody>
     </table>
 </div>
 <script>
     $(document).ready(function() {
         table = $('#target_dataTable').DataTable({
             lengthChange: false,
             lengthMenu: [
                 [10, 25, 50, -1],
                 ['10 rows', '25 rows', '50 rows', 'Show all']
             ],
             layout: {
                 topStart: {
                     buttons: [{
                             extend: 'pageLength',
                             className: 'btn btn-light bg-gradient waves-effect waves-light'
                         },
                         {
                             extend: 'copy',
                             className: 'btn btn-light bg-gradient waves-effect waves-light'
                         },
                         {
                             extend: 'excel',
                             className: 'btn btn-light bg-gradient waves-effect waves-light'
                         },
                         {
                             extend: 'colvis',
                             className: 'btn btn-light bg-gradient waves-effect waves-light'
                         }
                     ]
                 },
                 topEnd: {
                     search: {
                         placeholder: 'Search'
                     }
                 }
             },
             columnDefs: [{
                     width: '10%',
                     targets: 0
                 }, // Domain Code
                 {
                     width: '20%',
                     targets: 1
                 }, // Domain
                 {
                     width: '10%',
                     targets: 2
                 }, // Goal Code
                 {
                     width: '20%',
                     targets: 3
                 }, // Goal
                 {
                     width: '30%',
                     targets: 4
                 }, // Target 
                 {
                     width: '10%',
                     targets: 4,
                     class:"dt-nowrap"
                 } // Action
             ]
         });
         /***************************************************************************************** */
         $("#target_dataTable").on('click', '.addToClient', function(e) {
             var btn = $(this);
             var id = $(this).attr('id');
             let client_id = $("#client_dropdown_list").val();

             if (client_id == '') {
                 showAlert('', 'Select Client', 'error');
                 return;
             }
             current_row = $(this).parents('tr');
             if (current_row.hasClass('child')) {
                 current_row = current_row.prev();
             }
             Swal.fire({
                 title: "Are you sure?",
                 text: "Once added then you can only remove this goal from client program section only!",
                 icon: 'warning',
                 showCancelButton: true,
                 confirmButtonText: 'Proceed',
                 customClass: {
                     confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                     cancelButton: 'btn btn-danger w-xs me-2 mt-2',
                 },

                 buttonsStyling: false
             }).then((result) => {
                 if (result.isConfirmed) {
                     var ajaxRequest = $.ajax({
                         url: '<?php echo base_url() ?>client-program/wizard/targets/create',
                         type: 'post',
                         data: {
                             "id": id,
                             "client_id": client_id
                         },
                         beforeSend: function(xhr) {

                         }
                     });
                     ajaxRequest.done(function(response) {
                         if (response.status == 'success') {
                             table.row(current_row).remove().draw(false);
                             Toast.fire({
                                 icon: "success",
                                 title: response.message
                             });
                            
                         } else {
                             showAlert(response.statusText, response.message, response.status);
                         }
                     });

                     ajaxRequest.fail(function(jqXHR, textStatus, error) {
                         showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                     });
                     ajaxRequest.always(function() {

                     });


                 } else {
                     current_row = ''
                 }
             });

         });
         /***************************************************************************************** */

     });
 </script>