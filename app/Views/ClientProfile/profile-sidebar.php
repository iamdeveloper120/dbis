<?php if (auth()->user()->can('client-profile.access')): ?>
    <style>
        .profile-menu-header {
            color: #2074BA;
            font-weight: 600;
            letter-spacing: .01em;
        }

        .file-manager-menu li .sub-menu li {
            padding-left: 20px;
            position: relative;
        }

        .profile-menu-list>li+li {
            border-top: 1px solid #e9ecef;
            padding-top: 2px;
            margin-top: 0px;
        }

        .profile-menu-list>li>a {
            display: flex;
            align-items: center;
            gap: .35rem;
            padding: 5px 8px;
            border-radius: 6px;
            transition: background .15s ease, color .15s ease;
            white-space: nowrap;
        }

        .profile-menu-list>li>a .file-list-link {
            white-space: nowrap;
        }

        .profile-menu-link-nowrap {
            display: inline-block;
            white-space: nowrap;
        }

        .file-manager-menu li {
            padding: 2px 0;
        }

        .profile-menu-list>li>a:hover {
            background: #f5f8fb;
        }

        .profile-menu-list>li>a.active {
            background: #e7f1fb;
            color: #2074BA;
            font-weight: 600;
        }

        .submenu-caret {
            margin-left: auto;
            transition: transform .2s ease;
        }

        .profile-menu-list a[aria-expanded="true"] .submenu-caret {
            transform: rotate(180deg);
        }

        .profile-sidebar-wrap {
            display: flex;
            flex-direction: column;
            height: 100%;
            gap: .35rem;
        }

        .file-menu-sidebar-scroll {
            flex: 1 1 auto;
            min-height: 0;
        }

        .profile-progress-wrap {
            margin-top: auto;
            padding-top: .6rem;
            border-top: 2px solid #d8e1ea;
        }

        .profile-progress-wrap .card {
            margin-bottom: 0;
            box-shadow: none;
            border: 0;
            background: transparent;
        }

        .profile-progress-wrap .card-body {
            padding: .35rem 0 0;
        }

        .profile-progress-text {
            margin-top: .35rem;
            font-size: .72rem;
            color: #6c757d;
            line-height: 1.25;
        }

        .profile-progress-percent {
            font-weight: 600;
            color: #495057;
        }
    </style>
    <div class="p-3 profile-sidebar-wrap">
        <div class="mx-n4 px-4 file-menu-sidebar-scroll pt-2" data-simplebar>
            <ul class="list-unstyled file-manager-menu profile-menu-list">
                <!-- Client Dashboard -->
                <?php if (auth()->user()->can('client-profile.dashboard.view')): ?>
                    <li>
                        <a href="<?= base_url('client-profile/dashboard/' . encodeValue($client->id)) ?>"
                            class="<?= ($mtab === 'dashboard') ? 'active' : '' ?>">
                            <i class="ri-dashboard-line align-bottom me-2"></i>
                            <span class="file-list-link">Client Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>




                <!-- Client -->
                <?php if (auth()->user()->can('client-profile.client.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#clientMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['background', 'keyInformation'], true) ? 'true' : 'false'; ?>"
                            aria-controls="clientMenu">
                            <i class="ri-user-5-line align-bottom me-2"></i>
                            <span class="file-list-link">Client</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['background', 'keyInformation'], true) ? 'show' : '' ?>" id="clientMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.client.client-detail.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/background/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'background') ? 'active' : '' ?>">

                                            <span class="file-list-link">Client Details</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.client.key-information.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/key-information/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'keyInformation') ? 'active' : '' ?>">

                                            <span class="file-list-link">Key Information</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Programs & Skills -->
                <?php if (auth()->user()->can('client-profile.programs-skills.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#programsSkillsMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['currentPrograms', 'activeProgram', 'programs', 'pcTab', 'skillsTab', 'doiTab', 'graphs-daily', 'graphs-stimulus-response-chain', 'graphs-cumulative', 'graphs-rate'], true) ? 'true' : 'false'; ?>"
                            aria-controls="programsSkillsMenu">
                            <i class="ri-book-open-line align-bottom me-2"></i>
                            <span class="file-list-link">Skill Acquisition Programme</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['currentPrograms', 'activeProgram', 'programs', 'pcTab', 'skillsTab', 'doiTab', 'graphs-daily', 'graphs-stimulus-response-chain', 'graphs-cumulative', 'graphs-rate'], true) ? 'show' : '' ?>" id="programsSkillsMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.programs-skills.active-targets.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/activeProgram/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'activeProgram') ? 'active' : '' ?>">
                                            <span class="file-list-link">Active Targets</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.programs-skills.program-history.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/currentPrograms/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'currentPrograms') ? 'active' : '' ?>">
                                            <span class="file-list-link">Programme History</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.programs-skills.program-adjustments.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/pcData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'pcTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Programme Adjustments</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.programs-skills.mastered-skills.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/skillsData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'skillsTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Mastered Skills</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.programs-skills.developing-independence.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/doiData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'doiTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Developing Independence</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.programs-skills.probe-data.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/programData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'programs') ? 'active' : '' ?>">
                                            <span class="file-list-link">Probe Data</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (auth()->user()->can('client-profile.graphs.daily.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/daily/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-daily') ? 'active' : '' ?>">
                                            <span class="file-list-link">Daily Graphs</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.graphs.stimulus-response-chain.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/stimulus-response-chain/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-stimulus-response-chain') ? 'active' : '' ?>">
                                            <span class="file-list-link profile-menu-link-nowrap">Stimulus Response Chain (G)</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.graphs.cumulative.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/cumulative/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-cumulative') ? 'active' : '' ?>">
                                            <span class="file-list-link profile-menu-link-nowrap">Cumulative Weekly Graphs</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.graphs.rate.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/rate/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-rate') ? 'active' : '' ?>">
                                            <span class="file-list-link">Rate Graphs</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Mands -->
                <?php if (auth()->user()->can('client-profile.mands.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#mandsMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['currentMandListTab', 'mandsTab', 'defaultReinforcerTab', 'graphs-mands'], true) ? 'true' : 'false'; ?>"
                            aria-controls="mandsMenu">
                            <i class=" ri-discuss-line align-bottom me-2"></i>
                            <span class="file-list-link">Mands</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['currentMandListTab', 'mandsTab', 'defaultReinforcerTab', 'graphs-mands'], true) ? 'show' : '' ?>" id="mandsMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.mands.active-targets.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/defaultReinforcerData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'defaultReinforcerTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Active Mand Targets</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (auth()->user()->can('client-profile.mands.data.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/mandsData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'mandsTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Mand Data</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.mands.dictionary.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/currentMandList/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'currentMandListTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Mand Dictionary</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.graphs.mands.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/mands/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-mands') ? 'active' : '' ?>">
                                            <span class="file-list-link">Mand Graphs</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Problem Behaviour -->
                <?php if (auth()->user()->can('client-profile.problem-behaviour.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#problemBehaviourMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['pbTab', 'defaultAbcTab', 'graphs-pb'], true) ? 'true' : 'false'; ?>"
                            aria-controls="problemBehaviourMenu">
                            <i class="ri-alert-line align-bottom me-2"></i>
                            <span class="file-list-link">Behaviour Reduction</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['pbTab', 'defaultAbcTab', 'graphs-pb'], true) ? 'show' : '' ?>" id="problemBehaviourMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.problem-behaviour.reduction-data.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/pbData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'pbTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">Behaviour Reduction Data</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.problem-behaviour.abc-template.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dataSheet/defaultAbcData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'defaultAbcTab') ? 'active' : '' ?>">
                                            <span class="file-list-link">ABC Template</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.problem-behaviour.graphs.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/graphs/behaviour-reduction/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'graphs-pb') ? 'active' : '' ?>">
                                            <span class="file-list-link profile-menu-link-nowrap">Behaviour Reduction Graphs</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Summary Data -->
                <?php if (auth()->user()->can('client-profile.summary-data.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#summaryDataMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['sessions', 'dailyData', 'weeklyData'], true) ? 'true' : 'false'; ?>"
                            aria-controls="summaryDataMenu">
                            <i class="ri-file-list-3-line align-bottom me-2"></i>
                            <span class="file-list-link">Summary Data</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['sessions', 'dailyData', 'weeklyData'], true) ? 'show' : '' ?>" id="summaryDataMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.summary-data.session-overview.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/sessions/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'sessions') ? 'active' : '' ?>">
                                            <span class="file-list-link">Session Overview</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.summary-data.daily-data.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/dailyData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'dailyData') ? 'active' : '' ?>">
                                            <span class="file-list-link">Daily Data</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.summary-data.weekly-data.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/weeklyData/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'weeklyData') ? 'active' : '' ?>">
                                            <span class="file-list-link">Weekly Data</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>



                <!-- Reports -->
                <?php if (auth()->user()->can('client-profile.reports.access')): ?>
                    <li>
                        <a data-bs-toggle="collapse" href="#reportsMenu" role="button"
                            aria-expanded="<?= in_array($mtab, ['reports', 'reports-progress'], true) ? 'true' : 'false'; ?>"
                            aria-controls="reportsMenu">
                            <i class="ri-file-chart-line align-bottom me-2"></i>
                            <span class="file-list-link">Reports</span>
                            <i class="ri-arrow-down-s-line submenu-caret"></i>
                        </a>
                        <div class="collapse <?= in_array($mtab, ['reports', 'reports-progress'], true) ? 'show' : '' ?>" id="reportsMenu">
                            <ul class="sub-menu list-unstyled ms-4">
                                <?php if (auth()->user()->can('client-profile.reports.daily.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/reports/daily/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'reports') ? 'active' : '' ?>">
                                            Session Summary
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (auth()->user()->can('client-profile.reports.progress.view')): ?>
                                    <li>
                                        <a href="<?= base_url('client-profile/reports/progress/' . encodeValue($client->id)) ?>"
                                            class="<?= ($mtab === 'reports-progress') ? 'active' : '' ?>">
                                            Progress Report
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Client Progress Section -->
        <div class="profile-progress-wrap">
            <div class="card">
                <div class="card-body">
                    <div class="progress animated-progress custom-progress progress-label">
                        <div class="progress-bar bg-success" role="progressbar"
                            style="width: <?= $programProgress['percentage']; ?>%"
                            aria-valuenow="<?= $programProgress['percentage']; ?>"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="profile-progress-text">
                        Retained: <?= $programProgress['retained']; ?> /
                        Introduced: <?= $programProgress['introduced']; ?> |
                        <span class="profile-progress-percent"><?= $programProgress['percentage']; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
