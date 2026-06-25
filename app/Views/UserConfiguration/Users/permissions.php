<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Staff Member Direct Permissions</h5>
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
                            <h5 class="card-title mb-0"><?= $user->name() ?></h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex flex-wrap align-items-start gap-2">
                            <a href="/user-configuration/users/active-list" type="button" class="btn btn-primary">
                                <i class="ri-arrow-go-back-fill align-bottom me-1"></i> Staff Member List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('UserConfiguration/Users/_tabs', ['tab' => 'permissions', 'user' => $user]) ?>
                <div class="tab-content text-muted">
                    <div class="tab-pane active show" role="tabpanel">

                        <form action="<?= current_url() ?>" method="post">
                            <?= csrf_field() ?>
                            <fieldset>
                                <legend>Staff Member Permissions</legend>

                                <p>These permissions are applied in addition to any allowed by the staff's groups.</p>
                                <p>Indeterminate checkboxes indicate the permission is already available from one or more groups the staff is a part of.</p>

                                <?php if (session()->has('message')): ?>
                                    <div class="alert alert-success"><?= session('message') ?></div>
                                <?php endif; ?>
                                <div class="row">
                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Sessions Menu Access',
                                            'permission_card_id' => 'Session_Menu',
                                            'permission_comparison' => 'sessions.access',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Live Sessions',
                                            'permission_card_id' => 'Live_Sessions',
                                            'permission_comparison' => 'sessions.live',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Session Review',
                                            'permission_card_id' => 'Session_Review',
                                            'permission_comparison' => 'sessions.review',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Completed Sessions',
                                            'permission_card_id' => 'Completed_Sessions',
                                            'permission_comparison' => 'sessions.daily',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Data Menu Access',
                                            'permission_card_id' => 'Data_Menu',
                                            'permission_comparison' => 'data-sheet.access',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Datasheets',
                                            'permission_card_id' => 'Data_Sheets',
                                            'permission_comparison' => 'data-sheet.view',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Program Change',
                                            'permission_card_id' => 'Program_Change',
                                            'permission_comparison' => 'sessions.program-change',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Daily Data Management',
                                            'permission_card_id' => 'Daily_Data_Management',
                                            'permission_comparison' => 'daily-data',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Weekly Data Management',
                                            'permission_card_id' => 'Weekly_Data_Management',
                                            'permission_comparison' => 'weekly-data',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Client Profile',
                                            'permission_card_id' => 'Client_Profile',
                                            'permission_comparison' => 'client-profile',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Client Configuration Menu Access',
                                            'permission_card_id' => 'Client_Configuration_Menu_Access',
                                            'permission_comparison' => 'client-configuration.access',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Client Management',
                                            'permission_card_id' => 'Client_Management',
                                            'permission_comparison' => 'clients',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Client Program Management',
                                            'permission_card_id' => 'Client_Program_Management',
                                            'permission_comparison' => 'client-program',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Master Program Management',
                                            'permission_card_id' => 'Master_Program_Management',
                                            'permission_comparison' => 'master-program',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Mands Reinforcer (Master + Client Default)',
                                            'permission_card_id' => 'Master_Mands_Reinforcer',
                                            'permission_comparison' => 'mands-reinforcer',
                                            'user' => $user
                                        ]
                                    ) ?>
                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'ABC Data (Master + Client Specific)',
                                            'permission_card_id' => 'Master_Mands_Reinforcer',
                                            'permission_comparison' => 'abc-data',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'Staff Member Management',
                                            'permission_card_id' => 'Staff_Member_Management',
                                            'permission_comparison' => 'user-configuration',
                                            'user' => $user
                                        ]
                                    ) ?>

                                    <?= view(
                                        'UserConfiguration/Users/_permission_table',
                                        [
                                            'permission_card_title' => 'MIS Configuration',
                                            'permission_card_id' => 'MIS_Configuration',
                                            'permission_comparison' => 'app-configuration',
                                            'user' => $user
                                        ]
                                    ) ?>

                                </div>

                            </fieldset>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line align-bottom me-1"></i>Save Permissions</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    // Handle permissions inherited from the group (disabled but checked)
    let inheritedPermissions = document.getElementsByClassName('in-group');
    Array.prototype.forEach.call(inheritedPermissions, function(el, i) {
        el.indeterminate = true;
        el.disabled = true;
    });

    // Handle direct permissions (editable checkboxes)
    let directPermissions = document.getElementsByClassName('in-person');
    Array.prototype.forEach.call(directPermissions, function(el, i) {
        el.indeterminate = false;
        el.disabled = false;
    });
</script>
<?= $this->endSection() ?>
