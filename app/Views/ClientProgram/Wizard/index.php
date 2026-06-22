<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h5 class="mb-sm-0">Program Wizard</h5>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Program</a></li>
                    <li class="breadcrumb-item active">Wizard</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <form action="#" class="form-steps" autocomplete="off">
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control " id="client_dropdown_list">
                                <option value="">SELECT CLIENT</option>
                                <?php foreach ($clients as $client) {  ?>
                                    <option value="<?php echo $client->id; ?>">
                                        <?php echo $client->mrn . ' ( ' . $client->internal_mrn . ' ) - ' . $client->name(); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                    </div>
                    <hr />
                    <div class="step-arrow-nav mb-4">
                        <ul class="nav nav-pills custom-nav nav-justified" role="tablist" id="wizardTab">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-domains-nav" url="<?php echo base_url() ?>client-program/wizard/domains/list" data-bs-toggle="pill" data-bs-target="#tab-domains" type="button" role="tab" aria-controls="tab-domains-nav" aria-selected="true">Master Program Domains</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link " id="tab-goals-nav" url="<?php echo base_url() ?>client-program/wizard/goals/list" data-bs-toggle="pill" data-bs-target="#tab-goals" type="button" role="tab" aria-controls="tab-goals-nav" aria-selected="false">Master Program Goals</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-targets-nav" url="<?php echo base_url() ?>client-program/wizard/targets/list" data-bs-toggle="pill" data-bs-target="#tab-targets" type="button" role="tab" aria-controls="tab-targets-nav" aria-selected="false">Master Program Targets</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-domains" role="tabpanel" aria-labelledby="tab-domains">
                            Select Client
                        </div>
                        <!-- end tab pane -->

                        <div class="tab-pane fade" id="tab-goals" role="tabpanel" aria-labelledby="tab-goals">
                            Select Client
                        </div>
                        <!-- end tab pane -->
                        <div class="tab-pane fade" id="tab-targets" role="tabpanel" aria-labelledby="tab-targets">
                            Select Client
                        </div>
                        <!-- end tab pane -->
                    </div>
                    <!-- end tab content -->
                </form>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div><!-- end row -->
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('select').select2();
        /***************************************************************************************** */
        function emptyTabs() {
            // Empty the contents of all tabs
            $(".tab-pane").empty();

            // Make sure the first tab is selected
            $('#wizardTab .nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');

            $('#tab-domains-nav').addClass('active');
            $('#tab-domains').addClass('show active');
        }
        /***************************************************************************************** */

        $("#client_dropdown_list").on('change', function(e) {
            e.preventDefault;
            emptyTabs();
            let client_id = $("#client_dropdown_list").val();

            if (client_id == '') {
                showAlert('', 'Select Client', 'error');
                return;
            }
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/wizard/domains/list',
                type: 'post',
                data: {
                    "client_id": client_id
                },
                beforeSend: function(xhr) {

                    $("#client_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Populate DataTable with the retrieved data
                    $('#tab-domains').html(response.data);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#client_dropdown_list').prop("disabled", false);
            });

        }); //On change function ends

        /****************************************************************************** */
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            var url = $(e.target).attr('url');
            var target = $(e.target).attr('data-bs-target');

            let client_id = $("#client_dropdown_list").val();

            if (client_id == '') {
                $(".tab-pane").empty();
                $('' + target).html('Select Client');
                showAlert('', 'Select Client', 'error');
                return;
            }
            $(".tab-pane").empty();
            var ajaxRequest = $.ajax({
                url: url,
                type: 'post',
                data: {
                    "client_id": client_id
                },
                beforeSend: function(xhr) {

                    $('button[data-bs-toggle="pill"]').prop('disabled', true);
                    $('button[data-bs-toggle="pill"]').prop('disabled', true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Populate DataTable with the retrieved data
                    $('' + target).html(response.data);

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#client_dropdown_list').prop("disabled", false);
                $('button[data-bs-toggle="pill"]').prop('disabled', false);
            });


        });
        /***************************************************************************** */






    }); // End of document ready
</script>
<?= $this->endSection() ?>