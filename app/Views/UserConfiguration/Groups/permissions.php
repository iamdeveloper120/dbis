<?= $this->extend("layout/master") ?>

<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Groups/Roles Permissions</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Permissions</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">Edit Group "<span class="text-primary"><i><?= esc($group->title) ?></i></span>" &amp; Permissions</h5>
                    </div>
                    <div class="col-sm-auto">
                        <a href="/user-configuration/groups" class="btn btn-primary"><i class="ri-arrow-go-back-fill align-bottom me-1"></i> Groups</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?= view('UserConfiguration/Groups/_tabs', ['tab' => 'permissions', 'group' => $group->alias]) ?>
                <p><?php
                    // Check if there's a flashed message
                    if (session()->has('message')) {
                        // Get and display the flashed message
                        $message = session('message');
                        echo '<div class="alert alert-success">' . $message . '</div>';
                    }
                    ?></p>
                <form action="<?= current_url() ?>" method="post">
                    <?= csrf_field() ?>
                    <fieldset>
                        <div class="row">
                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Sessions Menu Access',
                                    'permission_card_id' => 'Session_Menu',
                                    'permission_comparison' => 'sessions.access',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Live Sessions',
                                    'permission_card_id' => 'Live_Sessions',
                                    'permission_comparison' => 'sessions.live',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Session Review',
                                    'permission_card_id' => 'Session_Review',
                                    'permission_comparison' => 'sessions.review',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Completed Sessions',
                                    'permission_card_id' => 'Completed_Sessions',
                                    'permission_comparison' => 'sessions.daily',
                                    'group' => $group
                                ]
                            ) ?>
                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Data Menu Access',
                                    'permission_card_id' => 'Data_Menu',
                                    'permission_comparison' => 'data-sheet.access',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Datasheets',
                                    'permission_card_id' => 'Data_Sheets',
                                    'permission_comparison' => 'data-sheet.view',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Program Change',
                                    'permission_card_id' => 'Program_Change',
                                    'permission_comparison' => 'sessions.program-change',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Daily Data Management',
                                    'permission_card_id' => 'Daily_Data_Management',
                                    'permission_comparison' => 'daily-data',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Weekly Data Management',
                                    'permission_card_id' => 'Weekly_Data_Management',
                                    'permission_comparison' => 'weekly-data',
                                    'group' => $group
                                ]
                            ) ?>
                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Client Profile',
                                    'permission_card_id' => 'Client_Profile',
                                    'permission_comparison' => 'client-profile',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Client Configuration Menu Access',
                                    'permission_card_id' => 'Client_Configuration_Menu_Access',
                                    'permission_comparison' => 'client-configuration.access',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Client Management',
                                    'permission_card_id' => 'Client_Management',
                                    'permission_comparison' => 'clients',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Client Program Management',
                                    'permission_card_id' => 'Client_Program_Management',
                                    'permission_comparison' => 'client-program',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Master Program Management',
                                    'permission_card_id' => 'Master_Program_Management',
                                    'permission_comparison' => 'master-program',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Mands Reinforcer (Master + Client Default)',
                                    'permission_card_id' => 'Master_Mands_Reinforcer',
                                    'permission_comparison' => 'mands-reinforcer',
                                    'group' => $group
                                ]
                            ) ?>
                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'ABC Data (Master + Client Specific)',
                                    'permission_card_id' => 'Master_Mands_Reinforcer',
                                    'permission_comparison' => 'abc-data',
                                    'group' => $group
                                ]
                            ) ?>
                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'Staff Member Management',
                                    'permission_card_id' => 'Staff_Member_Management',
                                    'permission_comparison' => 'user-configuration',
                                    'group' => $group
                                ]
                            ) ?>

                            <?= view(
                                'UserConfiguration/Groups/_permission_table',
                                [
                                    'permission_card_title' => 'MIS Configuration',
                                    'permission_card_id' => 'MIS_Configuration',
                                    'permission_comparison' => 'app-configuration',
                                    'group' => $group
                                ]
                            ) ?>

                        </div>

                    </fieldset>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line align-bottom me-1"></i>Save Group Permissions</button>
                    </div>

                </form>


            </div>

        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>
<?= $this->endSection() ?>
