 <script>
     // Global filter state
     let currentStatusFilter = 'All';

     function bindSearchAndSortLogic() {
         const searchInput = document.getElementById('searchInput');
         if (searchInput) {
             searchInput.onkeyup = function() {
                 applyCombinedFilters();
             };
         }

         // Bind status filter buttons
         document.querySelectorAll('.status-filter').forEach(btn => {
             btn.addEventListener('click', function() {
                 // Toggle active class
                 document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                 this.classList.add('active');

                 // Set global filter and reapply
                 currentStatusFilter = this.dataset.filter;
                 document.getElementById('searchInput').value = ''; // clear search input
                 applyCombinedFilters();
             });
         });

         sortTargetsByPriority();
         applyCombinedFilters(); // Filter and count together
     }

     function applyCombinedFilters() {
         const searchText = document.getElementById('searchInput').value.toLowerCase();
         const items = document.querySelectorAll('#listContainer .list-group-item');

         items.forEach(item => {
             const textMatch = (item.innerText || item.textContent).toLowerCase().includes(searchText);
             const statusMatch = currentStatusFilter === 'All' || (item.dataset.status === currentStatusFilter);

             item.style.display = (textMatch && statusMatch) ? "" : "none";
         });

         updateTargetCount();
     }

     function sortTargetsByPriority() {
         const listContainer = document.getElementById('listContainer');
         if (!listContainer) return;

         const items = Array.from(listContainer.querySelectorAll('.list-group-item'));

         items.sort((a, b) => {
             const dateA = new Date(a.dataset.latestDate || '1900-01-01');
             const dateB = new Date(b.dataset.latestDate || '1900-01-01');
             const dateDiff = dateB - dateA;
             if (dateDiff !== 0) return dateDiff;

             const domainA = a.dataset.domainCode || '';
             const domainB = b.dataset.domainCode || '';
             if (domainA !== domainB) return domainA.localeCompare(domainB);

             const goalA = a.dataset.goalCode || '';
             const goalB = b.dataset.goalCode || '';
             if (goalA !== goalB) return goalA.localeCompare(goalB);

             const nameA = a.dataset.targetName?.toLowerCase() || '';
             const nameB = b.dataset.targetName?.toLowerCase() || '';
             return nameA.localeCompare(nameB);
         });

         items.forEach(item => listContainer.appendChild(item));
     }

     function updateTargetCount() {
         const items = document.querySelectorAll('#listContainer .list-group-item');
         let visibleCount = 0;
         items.forEach(item => {
             if (item.style.display !== "none") visibleCount++;
         });

         const countEl = document.getElementById('targetCount');
         if (countEl) {
             countEl.textContent = "Total Targets: " + visibleCount;
         }
     }

     $(document).ready(function() {
         /***************************************************************************************** */
         var csrfToken = "<?= csrf_hash() ?>";
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': csrfToken,
             }
         });
         $('select').select2();
         /***************************************************************************************** */



         // Listen to the domain selection change
         $('#sDomain').on('change', function() {
             var domain_id = $(this).val();
             var client_id = '<?= $client->id ?>';

             // Clear the existing options in the Goals dropdown and add "All Goals" option
             $('#sGoal').empty().append('<option value="">All Goals</option>');

             if (domain_id !== '') {
                 // Send an AJAX request to fetch goals for the selected domain
                 $.ajax({
                     url: '<?= base_url('shared-datasheet/getGoalsByDomain') ?>',
                     type: 'POST',
                     data: {
                         client_id: client_id,
                         domain_id: domain_id,
                         csrf_test_name: csrfToken // Include the CSRF token
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

         // When the "Apply Filter" button is clicked
         $('#filter_data').on('click', function() {
             var domain_id = $('#sDomain').val();
             var goal_id = $('#sGoal').val();
             var probeSet = null; //$('#sProbeSet').val();
             var client_id = '<?= $client->id ?>';


             // Send an AJAX request to filter the data
             $.ajax({
                 url: '<?= base_url('shared-datasheet/filterProgramData') ?>',
                 type: 'POST',
                 data: {
                     client_id: client_id,
                     domain_id: domain_id,
                     goal_id: goal_id,
                     probeSet: probeSet,
                 },
                 success: function(response) {
                     $('#dataSheetTableArea').html(response);
                     // 🔁 Rebind sorting, counting, and filtering
                     bindSearchAndSortLogic();
                 },
                 error: function(xhr, status, error) {
                     console.log('Error fetching filtered data:', error);
                 }
             });
         });

         $('#dataSheetTableArea').on('click', '.active-probe-detail', function() {

             var goal_id = $(this).attr('goal-id');
             var client_id = $(this).attr('client-id');
             openActiveProbeSetRules(client_id, goal_id);
         });
         $('#dataSheetTableArea').on('click', '.percentage-yes-no', function() {

             var collection_id = $(this).attr('data-collection-id');
             openTransitionEntries(collection_id);
         });
         $('#dataSheetTableArea').on('click', '.stimulus-program', function() {

             var collection_id = $(this).attr('data-collection-id');
             var target_id = $(this).attr('data-target-id');
             openTargetSteps(collection_id, target_id);
         });
         // Initial sort and count on first render
         bindSearchAndSortLogic();
     });

     /************************************************************************************************* */
     var offcanvasRight = document.getElementById('offcanvasRight')
     var bsc = new bootstrap.Offcanvas(offcanvasRight)
     var prog_ch_btn = null;

     var rulesCanvasID = document.getElementById('rulesCanvas');
     var rulesCanvas = new bootstrap.Offcanvas(rulesCanvasID);


     $('#offcanvasRight').on('hidden.bs.offcanvas', function() {
         // Trigger a custom event when the offcanvas is hidden
         $('#prog_ch_area').html('');
     });



     function programChangeShow(pg_alert_id, pg_change_id, client_id, target_id) {

         console.log(pg_alert_id, pg_change_id, client_id, target_id);
         var ajaxRequest = $.ajax({
             type: 'POST',
             url: '<?= base_url('sessions/programChange/getForm') ?>',
             data: {
                 pg_alert_id: pg_alert_id,
                 pg_change_id: pg_change_id,
                 client_id: client_id,
                 target_id: target_id,
             },
             dataType: 'html',
             beforeSend: function(xhr) {

             }
         });
         ajaxRequest.done(function(response) {
             // Update program list content
             if (response == '') {
                 showAlert('', "No program change has been made", 'info');
             } else {
                 $('#prog_ch_area').html(response);
                 bsc.show()
             }

         });

         ajaxRequest.fail(function(jqXHR, textStatus, error) {
             showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
         });
         ajaxRequest.always(function() {

         });
     }

     $('#rulesCanvas').on('hidden.bs.offcanvas', function() {
         // Trigger a custom event when the offcanvas is hidden
         $('#rulesCanvasDetail').html('');
     });

     function openActiveProbeSetRules(client_id, goal_id) {

         $.ajax({
             url: '<?php echo base_url() ?>client-program/goal/load-client-active-probe-set-rules', // Endpoint to load the probe sets list
             type: 'post',
             data: {
                 "goal_id": goal_id,
                 "client_id": client_id
             },
             success: function(response) {
                 if (response.status === 'success') {
                     $('#rulesCanvasTitle').html('Active Probe Set and Rules for selected client and goal');
                     $('#rulesCanvasDetail').html(response.html);
                     $('#rulesCanvasDetail input').prop('disabled', true);
                     rulesCanvas.show()
                 } else {
                     showAlert(response.statusText, response.message, response.status);
                 }
             },
             error: function(jqXHR, textStatus, error) {
                 showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
             }
         });
     }

     var lastClickedCell = null;

     function setLastClickedCell(cell) {
         lastClickedCell = cell;
     }

     var transitionEntriesCanvasID = document.getElementById('transitionEntriesCanvas');
     var transitionEntriesCanvas = new bootstrap.Offcanvas(transitionEntriesCanvasID);

     $('#transitionEntriesCanvas').on('hidden.bs.offcanvas', function() {
         // Trigger a custom event when the offcanvas is hidden
         $('#transitionEntriesCanvasDetail').html('');
     });

     function openTransitionEntries(collection_id) {

         $.ajax({
             url: '<?php echo base_url() ?>shared-datasheet/transitionList', // Endpoint to load the probe sets list
             type: 'post',
             data: {
                 "collection_id": collection_id,
             },
             success: function(response) {
                 $('#transitionEntriesCanvasTitle').html('Trial data');
                 $('#transitionEntriesCanvasDetail').html(response.html);
                 transitionEntriesCanvas.show()
             },
             error: function(jqXHR, textStatus, error) {
                 showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
             }
         });
     }

     var targetStepsCanvasID = document.getElementById('targetStepsCanvas');
     var targetStepsCanvasCanvas = new bootstrap.Offcanvas(targetStepsCanvasID);

     $('#targetStepsCanvas').on('hidden.bs.offcanvas', function() {
         // Trigger a custom event when the offcanvas is hidden
         $('#targetStepsCanvasDetail').html('');
     });

     function openTargetSteps(collection_id, target_id) {

         $.ajax({
             url: '<?php echo base_url() ?>shared-datasheet/stimulusSteps', // Endpoint to load the probe sets list
             type: 'post',
             data: {
                 "collection_id": collection_id,
                 "target_id": target_id,
             },
             success: function(response) {
                 $('#targetStepsCanvasTitle').html('Stimulus Response Chain');
                 $('#targetStepsCanvasDetail').html(response.html);
                 targetStepsCanvasCanvas.show()
             },
             error: function(jqXHR, textStatus, error) {
                 showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
             }
         });
     }
 </script>