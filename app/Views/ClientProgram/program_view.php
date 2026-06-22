<!-- View (program_list) -->
<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {
        --vz-offcanvas-width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Client Program</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>
<div id="client-program-app"></div> <!-- Root element for React -->
<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<!-- right offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='probe_set_and_rules_area'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to target list</a>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="stimulusStepsOfCanvas" aria-labelledby="stimulusStepsOfCanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="stimulusStepsOfCanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='stimulusStepsOfCanvasContent'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to target list</a>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="stimulusChainOfCanvas" aria-labelledby="stimulusChainOfCanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="stimulusChainOfCanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='stimulusChainOfCanvasContent'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to target list</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="/react/ClientProgramView/ClientProgramView.js" type="module"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        var offcanvasRight = document.getElementById('offcanvasRight');
        var offcanvas = new bootstrap.Offcanvas(offcanvasRight);

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        /****************************************************************************************  */

        // Function to clear the offcanvas content and remove any attached events
        function clearOffcanvasContent() {

            $('#probe_set_and_rules_area').find('*').off("click");
            $('#probe_set_and_rules_area').children().remove();
            $('#offcanvasTitle').empty(); // Clear the title
        }

        $('#offcanvasRight').on('hidden.bs.offcanvas', function() {
            // Clear offcanvas content
            clearOffcanvasContent();

            // Call a global function or trigger an event React can listen to
            window.updateReactComponentAfterClose();
        });


        /********************************************************************** */
        // Event listener for the "Add New Probe Set and Rules" button
        window.openAddNewProbeSet = function(client_id, goal_id) {


            // Load the modal for adding a new probe set
            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/create-probe-set',
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id
                },
                success: function(response) {
                    if (response.status === 'success' || response.status === 'no_config') {
                        $('#offcanvasTitle').html('Attach Probe Set and Rules for selected client and goal');
                        clearOffcanvasContent();
                        $('#probe_set_and_rules_area').html(response.html);
                        offcanvas.show()
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                },
            });
        }

        // Event listener for the "Manage Existing Rules" button
        window.openManageExistingRules = function(client_id, goal_id) {

            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/load-client-existing-probe-sets-list', // Endpoint to load the probe sets list
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#offcanvasTitle').html('Update Probe Set and Rules for selected client and goal');
                        clearOffcanvasContent();
                        $('#probe_set_and_rules_area').html(response.html); // Load the HTML content into the offcanvas area
                        offcanvas.show(); // Show the offcanvas panel
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
                }
            });
        }

        window.openActiveProbeSetRules = function(client_id, goal_id) {

            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/load-client-active-probe-set-rules', // Endpoint to load the probe sets list
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#offcanvasTitle').html('Active Probe Set and Rules for selected client and goal');
                        clearOffcanvasContent();
                        $('#probe_set_and_rules_area').html(response.html); // Load the HTML content into the offcanvas area
                        offcanvas.show(); // Show the offcanvas panel
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
                }
            });
        }

        /********************************************************************** */
        function clearOffcanvasContent() {
            // Remove specific events on elements by selectors
            $('#probe_set_dropdown').off('change');
            $('#combination_dropdown').off('change');
            $('#save_probe_set').off('click');
            $(document).off('click', '.edit-probe-set');
            $(document).off('click', '#btnCloseEdit');
            $(document).off('click', '#btnUpdateProbeSet');
            $(document).off('click', '.activate-probe-set');
            $(document).off('click', '.delete-probe-set');
            $('#addDurationInput').off('click');
            $(document).off('click', '.removeDurationInput');

            // If there are other global or document-level event listeners, remove them similarly
        }

        /***************************Steps management ******************************************* */
        var stimulusStepsOfCanvas = document.getElementById('stimulusStepsOfCanvas');
        var stepCanvas = new bootstrap.Offcanvas(stimulusStepsOfCanvas);
        $('#stimulusStepsOfCanvas').on('hidden.bs.offcanvas', function() {
            // Clear offcanvas content
            clearOffStepsCanvas();

            // Call a global function or trigger an event React can listen to
            window.updateReactComponentAfterClose();
        });

        function clearOffStepsCanvas() {
            // Unbind all delegated events related to chaining
            $(document).off('click', '#stimulusStepList .btn-edit');
            $(document).off('click', '#stimulusStepList .btn-save');
            $(document).off('click', '#stimulusStepList .btn-delete');
            $(document).off('submit', '#addStepForm');

            // Clear Sortable instance if stored
            const stepList = document.getElementById('stimulusStepList');
            if (stepList && stepList._sortable) {
                stepList._sortable.destroy();
                delete stepList._sortable;
            }

            // Remove canvas content and title
            $('#stimulusStepsOfCanvasContent').empty();
            $('#stimulusStepsOfCanvasTitle').empty();
        }

        window.openStimulusStepsEditor = function(client_id, goal_id, target_id) {
            $.ajax({
                url: '<?php echo base_url() ?>client-program/target/load-stimulus-steps-editor',
                type: 'post',
                data: {
                    client_id: client_id,
                    goal_id: goal_id,
                    target_id: target_id,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        clearOffStepsCanvas();
                        $('#stimulusStepsOfCanvasTitle').html('Stimulus Step Editor');
                      
                        $('#stimulusStepsOfCanvasContent').html(response.html);
                        stepCanvas.show();
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                }
            });
        }
        /*****************************End of steps management ***************************************** */
        /***************************Target Chain management ******************************************* */
        var stimulusChainOfCanvas = document.getElementById('stimulusChainOfCanvas');
        var chainCanvas = new bootstrap.Offcanvas(stimulusChainOfCanvas);
        $('#stimulusChainOfCanvas').on('hidden.bs.offcanvas', function() {
            // Clear offcanvas content
            clearOfChainCanvas();
            // Call a global function or trigger an event React can listen to
            window.updateReactComponentAfterClose();
        });

        function clearOfChainCanvas() {
            // Unbind any delegated events related to chaining (if any custom ones are used)
            $(document).off('submit', '#stimulusChainForm');

            // Optional: unbind change listeners if you add custom validation later
            $('.chaining-radio').off('change');

            // Clear all injected chaining form elements
            $('#stimulusChainOfCanvasContent').empty();
            $('#stimulusChainOfCanvasTitle').empty();
        }


        window.openStimulusChainEditor = function(client_id, goal_id, target_id) {
            $.ajax({
                url: '<?php echo base_url() ?>client-program/target/load-stimulus-chain-editor',
                type: 'post',
                data: {
                    client_id: client_id,
                    goal_id: goal_id,
                    target_id: target_id,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        clearOfChainCanvas();
                        $('#stimulusChainOfCanvasTitle').html('Stimulus Chain Selection');
                        $('#stimulusChainOfCanvasContent').html(response.html);
                        chainCanvas.show();
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                }
            });
        }
        /*****************************End of Target Chain management ***************************************** */
    });
</script>
<?= $this->endSection() ?>