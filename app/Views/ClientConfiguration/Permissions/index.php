<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .table .form-check {
        padding-left: 20px;
        margin-bottom: 0;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Client Access Permissions</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Permissions</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <ul class="nav nav-pills arrow-navtabs nav-info bg-light nav-justified mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#Instructors_tab" role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                            <span class="d-none d-sm-block">Instructors</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#Supervisors_tab" role="tab" aria-selected="false" tabindex="-1">
                            <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                            <span class="d-none d-sm-block">Supervisors</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="Instructors_tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <select class="form-control " id="instructor_dropdown_list">
                                    <option value="">SELECT Instructor</option>
                                    <?php if (isset($instructors) && count($instructors)) : ?>
                                        <?php foreach ($instructors as $instructor) : ?>
                                            <option value="<?php echo $instructor->id; ?>">
                                                <?php echo $instructor->first_name . ' ' . $instructor->last_name . ' (' . $instructor->groupList . ' )';  ?>
                                            </option>

                                        <?php endforeach ?>
                                    <?php endif ?>

                                </select>
                            </div>

                        </div>
                        <hr />
                        <div class="table-responsive">
                            <table class="table table-bordered" id="instructor_client_permission_dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Client No.</th>
                                        <th>Name</th>
                                        <th>Assign/Unassign</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="Supervisors_tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <select class="form-control " id="supervisor_dropdown_list">
                                    <option value="">SELECT Supervisor</option>
                                    <?php if (isset($supervisors) && count($supervisors)) : ?>
                                        <?php foreach ($supervisors as $supervisor) : ?>
                                            <option value="<?php echo $supervisor->id; ?>">
                                                <?php echo $supervisor->first_name . ' ' . $supervisor->last_name . ' (' . $supervisor->groupList . ' )';  ?></option>

                                        <?php endforeach ?>
                                    <?php endif ?>

                                </select>
                            </div>
                        </div>
                        <hr />
                        <div class="table-responsive">
                            <table class="table table-bordered" id="supervisor_client_permission_dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>

                                        <th>Client No.</th>
                                        <th>Name</th>
                                        <th>Client Supervisor</th>
                                        <th>Assign/Unassign</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <!--end col-->
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

        /********************* */
        $('select').select2();
        /********************* */

        var table = $('#instructor_client_permission_dataTable').DataTable({
            response: false,
            data: [],
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            columnDefs: [ 
                {
                    width: '20%',
                    targets: 0
                },
                {
                    width: '70%',
                    targets: 1
                },
                {
                    width: '10%',
                    targets: 2
                }
            ],


        });
        /****************************************************************************************  */
        $("#instructor_dropdown_list").on('change', function(e) {
            e.preventDefault;
            let user_id = $("#instructor_dropdown_list").val();

            var ajaxRequest = $.ajax({
                url: '/clients/permissions/list',
                type: 'post',
                data: {
                    "user_id": user_id,
                    "type": 'instructor'
                },
                beforeSend: function(xhr) {
                    $('#instructor_dropdown_list').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                table.clear();
                table.rows.add(response);
                table.draw();
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#instructor_dropdown_list').prop("disabled", false);
            });

        }); //On change function ends
        /***************************************************************************************** */

        $("#instructor_client_permission_dataTable").on('click', '.client-permission', function(e) {
            var btn = $(this);
            var client_id = $(this).attr('client_id');
            var u_id = $(this).attr('u_id');
            var permission = 0;

            if ($('#customCheck-' + client_id).is(":checked")) {
                permission = 1;

            } else {
                console.log("unchecked");
            }


            var ajaxRequest = $.ajax({
                url: '/clients/permissions/save',
                type: 'post',
                data: {
                    "user_id": u_id,
                    'client_id': client_id,
                    "permission": permission
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    if (permission === 1) {
                        $('#customCheck-' + client_id).prop("checked", false);
                    } else {
                        $('#customCheck-' + client_id).prop("checked", true);
                    }
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        /***************************************************************************************** */

        /***************************************************************************************** */
        var table2 = $('#supervisor_client_permission_dataTable').DataTable({
            response: false,
            data: [],
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            columnDefs: [ 
                {
                    width: '20%',
                    targets: 0
                },
                {
                    width: '50%',
                    targets: 1
                },
                {
                    width: '20%',
                    targets: 2
                },
                {
                    width: '10%',
                    targets: 3
                }
            ],

        });
        /***************************************************************************************** */
        $("#supervisor_dropdown_list").on('change', function(e) {
            e.preventDefault;
            let user_id = $("#supervisor_dropdown_list").val();

            var ajaxRequest = $.ajax({
                url: '/clients/permissions/list',
                type: 'post',
                data: {
                    "user_id": user_id,
                    "type": 'supervisor'
                },
                beforeSend: function(xhr) {
                    $('#supervisor_dropdown_list').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                table2.clear();
                table2.rows.add(response);
                table2.draw();
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#supervisor_dropdown_list').prop("disabled", false);
            });

        }); //On change function ends
        /***************************************************************************************** */
        $("#supervisor_client_permission_dataTable").on('click', '.default-permission', function(e) {
            var btn = $(this);
            var client_id = $(this).attr('client_id');
            var u_id = $(this).attr('u_id');
            var is_default = 0;

            if ($('#is-default-' + client_id).is(":checked")) {
                is_default = 1;
            } else {
                console.log("unchecked");
            }


            var ajaxRequest = $.ajax({
                url: '/clients/permissions/save-supervisor',
                type: 'post',
                data: {
                    "user_id": u_id,
                    'client_id': client_id,
                    "is_default": is_default
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    if (is_default === 1) {
                        $('#is-default-' + client_id).prop("checked", false);
                    } else {
                        $('#is-default-' + client_id).prop("checked", true);
                    }
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        /***************************************************************************************** */
        $("#supervisor_client_permission_dataTable").on('click', '.client-permission', function(e) {
            var btn = $(this);
            var client_id = $(this).attr('client_id');
            var u_id = $(this).attr('u_id');
            var permission = 0;

            if (btn.is(":checked")) {
                permission = 1;

            } else {
                console.log("unchecked");
            }


            var ajaxRequest = $.ajax({
                url: '/clients/permissions/save',
                type: 'post',
                data: {
                    "user_id": u_id,
                    'client_id': client_id,
                    "permission": permission
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    if (permission === 1) {
                        btn.prop("checked", false);
                    } else {
                        btn.prop("checked", true);
                    }
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        /***************************************************************************************** */
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href"); // Get the target tab
            if (target === "#Supervisors_tab") {
                table2.columns.adjust().draw(); // Adjust columns for the supervisor table
            } else if (target === "#Instructors_tab") {
                table.columns.adjust().draw(); // Adjust columns for the instructor table
            }
        });
    });
    /***************************************************************************************** */
</script>
<?= $this->endSection() ?>