<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Staff Member Management</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">

                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">List</h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex flex-wrap align-items-start gap-2">

                            <a href="/user-configuration/users/new" type="button" class="btn btn-primary"><i class="ri-add-line align-bottom me-1"></i> Add New Staff Member</a>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table class="table table-bordered  nowrap align-middle" id="dataTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Groups</th>
                                <th>Last Active</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (isset($users) && count($users)) : ?>
                                <?php foreach ($users as $user) : ?>
                                    <?php if (!$user->inGroup('superadmin')): ?>
                                        <tr>
                                            <td><?= esc($user->email) ?></td>
                                            <td><?= esc($user->username) ?></td>
                                            <td><?= $user->groupList; ?></td>
                                            <td><?= $user->last_active !== null ? $user->last_active->humanize() : 'never' ?></td>
                                            <td class="d-flex justify-content-end">

                                                <!-- Action Menu -->
                                                <a href="/user-configuration/users/edit/<?= $user->id ?>" class="btn btn-sm btn-outline-warning"><i class="ri-edit-line align-bottom me-1"></i>Edit </a>&nbsp;


                                                <!-- Action Menu -->
                                                <a href="#" class="btn btn-sm btn-outline-info deactivate" id='<?= $user->id ?>'><i class="ri-link-unlink align-bottom me-1"></i>Deactivate </a>

                                            </td>
                                        </tr>
                                    <?php endif ?>
                                <?php endforeach ?>
                            <?php endif ?>
                        </tbody>
                    </table>
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
        table = $('#dataTable').DataTable({
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
                        }, {
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

            order: [
                [2, 'asc']
            ],
        });

    });
    $("#dataTable").on('click', '.deactivate', function(e) {
        var btn = $(this);
        var user_id = $(this).attr('id');

        current_row = $(this).parents('tr');
        if (current_row.hasClass('child')) {
            current_row = current_row.prev();
        }

        Swal.fire({
            title: "Are you sure?",
            text: "Warning: system will deactivate user. All clients assigned to the user will be de-linked. The user can be activated in the future, but clients will need to be reassigned to this user. Daily and weekly data will be retained.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Deactivate',
            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
            cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var ajaxRequest = $.ajax({
                    url: '<?php echo base_url() ?>/user-configuration/users/activation',
                    type: 'post',
                    data: {
                        "id": user_id,
                        'type': 'deactivate'
                    },
                    beforeSend: function(xhr) {
                        btn.prop("disabled", true);
                    }
                });
                ajaxRequest.done(function(response) {
                    if (response.status == 'success') {
                        table.row(current_row).remove().draw(false);
                        showAlert(response.statusText, response.message, response.status);
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                });
                ajaxRequest.fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                });
                ajaxRequest.always(function() {
                    btn.prop("disabled", false);
                });

            } else {
                current_row = '';
            }
        });

    });
</script>
<?= $this->endSection() ?>