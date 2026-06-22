 <div class="row pb-2">
     <div class="col-md-11">
         <select class="form-control " id="domains_dropdown_list">
             <option value="">All</option>
             <?php $object = json_decode(json_encode($domains), FALSE);
                foreach ($object as $domain) {  ?>
                 <option value="<?php echo $domain->id; ?>">
                     <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
             <?php } ?>
         </select>
     </div>
     <div class="col-md-1">
         <button type="button" id="search-goals" class="btn btn-info bg-gradient waves-effect waves-light btn-label right " title="Search">
             <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>
     </div>
 </div>
 <hr>
 <div id="goal_table_area">
     <?= view('ClientProgram/Wizard/goals_table', ['domains' => $domains]) ?>
 </div>

 <script>
     $(document).ready(function() {
         $('select').select2();

         $("#search-goals").on('click', function(e) {

             let client_id = $("#client_dropdown_list").val();
             let domain_id = $("#domains_dropdown_list").val();

             if (client_id == '') {
                 showAlert('', 'Select Client', 'error');
                 return;
             }
             var ajaxRequest = $.ajax({
                 url: '<?php echo base_url() ?>client-program/wizard/goals/search',
                 type: 'post',
                 data: {
                     "client_id": client_id,
                     "domain_id": domain_id
                 },
                 beforeSend: function(xhr) {

                     $("#client_dropdown_list").prop("disabled", true);
                     $("#domains_dropdown_list").prop("disabled", true);
                 }
             });
             ajaxRequest.done(function(response) {
                 if (response.status == 'success') {
                     // Populate DataTable with the retrieved data
                     $('#goal_table_area').html(response.data);
                 } else {
                     showAlert(response.statusText, response.message, response.status);
                 }
             });
             ajaxRequest.fail(function(jqXHR, textStatus, error) {
                 showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
             });
             ajaxRequest.always(function() {
                 $('#client_dropdown_list').prop("disabled", false);
                 $("#domains_dropdown_list").prop("disabled", false);
             });

         }); //On change function ends

         /***************************************************************************************** */

     });
 </script>