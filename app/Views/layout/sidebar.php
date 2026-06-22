<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-light.png" alt="" height="60">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <hr class="sidebar-divider my-0">
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/">
                        <i class="ri-home-2-line"></i> <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider my-0">

                <!-- KPI's Section -->
                <?php if (auth()->user()->can('kpi.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#KPIMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="KPIMenu">
                            <i class="bx bx-tachometer"></i>
                            <span data-key="clientMenu">KPI's</span>
                        </a>
                        <div class="collapse menu-dropdown" id="KPIMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('kpi.rate-data.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/kpi/rate-data" data-key="t-kpi-rate-data">Rate Data</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('kpi.client-target.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/kpi/client-target" data-key="t-client-target">Client's Target</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('kpi.supervisor-target.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/kpi/supervisor-target" data-key="t-supervisor-target">Supervisor's Target</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>

                <!-- Sessions Section -->
                <?php if (auth()->user()->can('sessions.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#SessionMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="SessionMenu">
                            <i class="bx bxs-calendar-check"></i>
                            <span data-key="SessionMenu">Sessions</span>
                        </a>
                        <div class="collapse menu-dropdown" id="SessionMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('sessions.live.run')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/sessions/live" data-key="t-sessions">Run Session</a>
                                    </li>

                                <?php endif ?>
                                <?php if (auth()->user()->can('sessions.daily.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/sessions/daily" data-key="t-daily-sessions">Completed Sessions</a>
                                    </li>
                                <?php endif ?>

                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>

                <!-- Data Sheet Section -->
                <?php if (auth()->user()->can('data-sheet.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#SessionDataMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="SessionDataMenu">
                            <i class="bx bx-collection"></i>
                            <span data-key="SessionDataMenu">Data</span>
                        </a>
                        <div class="collapse menu-dropdown" id="SessionDataMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('data-sheet.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link menu-link" href="/dataSheet" data-key="t-clients-dataSheet">
                                            Data Sheet
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if (auth()->user()->can('daily-data.computed-data.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/dailyData/computedData">
                                            Daily Data
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if (auth()->user()->can('weekly-data.manual.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/sessions/weekly" data-key="t-weekly-data">
                                            Weekly Data
                                        </a>
                                    </li>
                                <?php endif ?>

                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>

                <!-- Graphs Section -->
                <?php if (auth()->user()->can('graphs.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#GraphMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="GraphMenu">
                            <i class="ri-line-chart-line"></i>
                            <span data-key="GraphMenu">Graphs</span>
                        </a>
                        <div class="collapse menu-dropdown" id="GraphMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('graphs.daily-data.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link menu-link" href="/graphs/dailyData" data-key="t-graphs-daily">
                                            Daily Data Graphs
                                        </a>
                                    </li>

                                <?php endif ?>

                                <?php if (auth()->user()->can('graphs.cumulative.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/graphs/cumulative" data-key="t-graphs-cumulative">
                                            Cumulative Graph
                                        </a>
                                    </li>

                                <?php endif ?>



                                <?php if (auth()->user()->can('graphs.rate.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/graphs/rate" data-key="t-graphs-rate">
                                            Rate Graphs
                                        </a>
                                    </li>

                                <?php endif ?>
                                <?php if (auth()->user()->can('graphs.mands.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/graphs/mands" data-key="t-graphs-mands">
                                            Mand Graphs
                                        </a>
                                    </li>

                                <?php endif ?>
                                <?php if (auth()->user()->can('graphs.stimulus-response-chain.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/graphs/stimulus-response-chain" data-key="t-graphs-stimulus-response-chain">
                                            Stimulus Response Chain
                                        </a>
                                    </li>

                                <?php endif ?>

                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>


                <!-- Reporting -->
                <?php if (auth()->user()->can('reporting.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#ReportingMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="ReportingMenu">
                            <i class="ri-file-chart-line"></i>
                            <span data-key="ReportingMenu">Reports</span>
                        </a>
                        <div class="collapse menu-dropdown" id="ReportingMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('reporting.daily.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/reports/daily" data-key="t-reporting-daily">Daily Report</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('reporting.progress.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/reports/progress" data-key="t-reporting-progress">Progress Report</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>


                <!-- Client Profile -->
                <?php if (auth()->user()->can('client-profile.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="/client-profile/list">
                            <i class="ri-group-line"></i> <span data-key="t-client-profile">Clients Profile</span>
                        </a>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>


                <!-- Client Configuration -->
                <?php if (auth()->user()->can('client-configuration.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#clientMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="clientMenu">
                            <i class="ri-user-settings-line"></i>
                            <span data-key="clientMenu">Client Configuration</span>
                        </a>
                        <div class="collapse menu-dropdown" id="clientMenu">
                            <ul class="nav nav-sm flex-column">

                                <?php if (auth()->user()->can('clients.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/clients" data-key="t-clients">Client Management</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('clients.permissions.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/clients/permissions" data-key="t-client-permissions">Client Access Permissions</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('client-program.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/client-program/treeView" data-key="t-client-program">Client Program</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('client-program.wizard-access')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/client-program/wizard" data-key="t-wizard-program">Client Program Wizard</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('master-program.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/master-program" data-key="t-master-program">Master Program</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('master-program.phases-rules.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/master-program/phases-rules-setup" data-key="t-phases-rules-setup">Phases & Rules Setup</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('mands-reinforcer.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/mands/reinforcer" data-key="t-mands-reinforcer">Manage Reinforcer</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('abc-data.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/abc-data" data-key="t-abc-data">Configure ABC data</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>

                <!-- Staff Management -->
                <?php if (auth()->user()->can('user-configuration.access')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#usersManagementMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="usersManagementMenu">
                            <i class="ri-shield-user-line"></i>
                            <span data-key="usersManagementMenu">Staff Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="usersManagementMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('user-configuration.users.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-configuration/users/active-list" data-key="t-active-users-list">Active Staff List</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('user-configuration.users.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-configuration/users/inactive-list" data-key="t-inactive-users-list">Inactive Staff List</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('user-configuration.groups.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-configuration/groups" data-key="t-groups">Staff Groups</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('user-configuration.users.logs')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-configuration/users/logged-in-logs" data-key="t-logged-in-log">Staff Logs</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>

                <!-- MIS Configuration -->
                <?php if (auth()->user()->can('app-configuration.access') || auth()->user()->inGroup('superadmin')) : ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#appConfigurationMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="appConfigurationMenu">
                            <i class="ri-settings-5-line"></i>
                            <span data-key="t-app-configuration-menu">MIS Configuration</span>
                        </a>
                        <div class="collapse menu-dropdown" id="appConfigurationMenu">
                            <ul class="nav nav-sm flex-column">
                                <?php if (auth()->user()->can('app-configuration.general-settings.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/app-configuration/general-settings" data-key="t-general-settings">General Settings</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('app-configuration.report-settings.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/app-configuration/report-settings" data-key="t-report-settings">Report Settings</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->can('app-configuration.module-settings.view')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/app-configuration/module-settings" data-key="t-module-settings">Module Settings</a>
                                    </li>
                                <?php endif ?>
                                <?php if (auth()->user()->inGroup('superadmin')) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/app-configuration/permission-sync" data-key="t-permission-sync">Permission Sync</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </li>
                    <hr class="sidebar-divider my-0">
                <?php endif ?>       
               
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
