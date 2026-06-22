 <div class="row mb-4">
     <div class="col-md-12">
         <div class="card border">
             <div class="card-header">
                 <h6 class="card-title mb-0"><?= $target['target_name']; ?>
                     <span class="badge bg-primary-subtle text-primary float-end"><?= "";//$target['phase_name'] ?></span>
                 </h6>
             </div>
             <div class="card-body">
                 <p class="card-text">
                     <?php
                        $method = $target['chain']['method'] ?? null;
                        $phaseId = $target['current_phase_id'];

                        if ($phaseId == 1) {
                            echo view('ClientSessionsReview/ProgramReview/StimulusProgramEdit/_stimulus_block_baseline', [
                                'domain' => $domain,
                                'goal' => $goal,
                                'target' => $target,
                                'session_id' => $session_id,
                                'baseline_choices' => [
                                    ['value' => 'NR', 'label' => 'NR'],
                                    ['value' => 'IR', 'label' => 'IR'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                                'teaching_choices' => [
                                    ['value' => 'FP', 'label' => 'FP'],
                                    ['value' => 'PP', 'label' => 'PP'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                            ]);
                        } elseif ($method == 'total_task') {
                            echo view('ClientSessionsReview/ProgramReview/StimulusProgramEdit/_stimulus_block_total_task', [
                                'domain' => $domain,
                                'goal' => $goal,
                                'target' => $target,
                                'session_id' => $session_id,
                                'baseline_choices' => [
                                    ['value' => 'NR', 'label' => 'NR'],
                                    ['value' => 'IR', 'label' => 'IR'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                                'teaching_choices' => [
                                    ['value' => 'FP', 'label' => 'FP'],
                                    ['value' => 'PP', 'label' => 'PP'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                            ]);
                        } elseif ($method == 'forward') {
                            echo view('ClientSessionsReview/ProgramReview/StimulusProgramEdit/_stimulus_block_forward', [
                                'domain' => $domain,
                                'goal' => $goal,
                                'target' => $target,
                                'session_id' => $session_id,
                                'baseline_choices' => [
                                    ['value' => 'NR', 'label' => 'NR'],
                                    ['value' => 'IR', 'label' => 'IR'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                                'teaching_choices' => [
                                    ['value' => 'FP', 'label' => 'FP'],
                                    ['value' => 'PP', 'label' => 'PP'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                            ]);
                        } elseif ($method == 'backward') {
                            echo view('ClientSessionsReview/ProgramReview/StimulusProgramEdit/_stimulus_block_backward', [
                                'domain' => $domain,
                                'goal' => $goal,
                                'target' => $target,
                                'session_id' => $session_id,
                                'baseline_choices' => [
                                    ['value' => 'NR', 'label' => 'NR'],
                                    ['value' => 'IR', 'label' => 'IR'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                                'teaching_choices' => [
                                    ['value' => 'FP', 'label' => 'FP'],
                                    ['value' => 'PP', 'label' => 'PP'],
                                    ['value' => 'IND', 'label' => 'IND'],
                                ],
                            ]);
                        }
                        ?>

                 </p>

             </div>

         </div>
     </div>
 </div>
 </div>
 <script>
     $(document).ready(function() {

         /***************************************************************************************** */
         var csrfToken = "<?= csrf_hash() ?>";
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': csrfToken,
             }
         });
         // Object to store the previous values for each radio button group

         var previousValues = {};
         // Object to store the current active group for each target         
         function enableNextGroup(targetId, setId) {
             $('input[name^="prob_' + targetId + '_' + (setId + 1) + '"]').prop('disabled', false);
         }

         // Function to disable the next group for a specific target
         function disableNextGroup(targetId, setId) {
             // Iterate over all sets starting from the next one after startSetId
             for (var setId = setId + 1;; setId++) {
                 // Check if there are radio buttons in the current set
                 if ($('input[name^="prob_' + targetId + '_' + setId + '"]').length === 0) {
                     break; // No more sets, exit the loop
                 }

                 // Disable all radio buttons in the current set
                 $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('disabled', true);

                 // Uncheck all radio buttons in the current set
                 $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('checked', false);
             }
         }

         // Disable all groups except the first one initially
         //$('input[name^="prob_"]').not('input[name="prob_1"]').prop('disabled', true);

         // Radio button click event handler
         $(document).off('click', 'input[type="radio"]').on('click', 'input[type="radio"]', function() {
             // Get the value of the clicked radio button
             var clickedValue = $(this).val();
             var targetId = $(this).data('target-id');
             var setId = $(this).data('set-id');

             // Get the name of the radio button group
             var groupName = $(this).attr('name');

             // Check if the clicked radio button has the same value as the previously selected one
             if (previousValues.hasOwnProperty(groupName)) {
                 if (clickedValue === previousValues[groupName]) {
                     // Reset both radio buttons in the group
                     $('input[name="' + groupName + '"]').prop('checked', false);
                     // Update the previous value for the group
                     previousValues[groupName] = undefined;

                     // Disable the next group for this target
                     disableNextGroup(targetId, setId);
                 } else {
                     // Keep the clicked radio button selected
                     $('input[name="' + groupName + '"][value="' + clickedValue + '"]').prop('checked', true);
                     // Update the previous value for the group
                     previousValues[groupName] = clickedValue;
                     // Check if the next group is already enabled
                     enableNextGroup(targetId, setId);
                 }
             } else {
                 // If there is no previous value for the group, set the clicked value as the previous value
                 previousValues[groupName] = clickedValue;
                 enableNextGroup(targetId, setId);
             }
         });


         const Toast = Swal.mixin({
             toast: true,
             position: "top-end",
             showConfirmButton: false,
             timer: 3000,
             timerProgressBar: true,
             didOpen: (toast) => {
                 toast.onmouseenter = Swal.stopTimer;
                 toast.onmouseleave = Swal.resumeTimer;
             }
         });
         /***************************************************************************************** */

     });
 </script>