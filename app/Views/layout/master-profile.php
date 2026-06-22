<?= $this->include('layout/main') ?>

<head>
    <?php echo view('layout/title-meta', array('title' => $page_title)); ?>
    <?= $this->include('layout/head-css') ?>
    <style>
        @media (min-width: 768px) {
            .main-content {
                margin-left: 0px;
            }

            :is([data-layout=vertical], [data-layout=semibox])[data-sidebar-size=sm] .main-content {
                margin-left: 0px;
            }

            #page-topbar {
                left: 0;
            }

            [data-layout=vertical]:is([data-sidebar-size=sm], [data-sidebar-size=sm-hover]) #page-topbar {
                left: 0;
            }
        }

        .footer {
            bottom: 0;
            padding: 20px calc(1.5rem * .5);
            position: absolute;
            right: 0;
            color: var(--vz-footer-color);
            left: 0;
            height: 60px;
            background-color: var(--vz-footer-bg);
        }

        [data-layout=vertical]:is([data-sidebar-size=sm], [data-sidebar-size=sm-hover]) .footer {
            left: 0;
        }

        :is([data-layout=vertical], [data-layout=semibox]) .horizontal-logo {
            display: block;
        }

        :is([data-layout=vertical], [data-layout=semibox])[data-sidebar-size=sm] .navbar-brand-box {
            position: fixed;
            padding: 0;
            width: var(--vz-vertical-menu-width-sm);
            z-index: 1;
            top: 0;
            background-color: white;
        }


        @media (max-width: 991.98px) {
            .file-manager-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: 250px;
                max-width: 100%;
                z-index: 1003;
                -webkit-box-shadow: 0 5px 10px rgba(30, 32, 37, .12);
                box-shadow: 0 5px 10px rgba(30, 32, 37, .12);
                -webkit-transform: translateX(-100%);
                transform: translateX(-100%);
                visibility: hidden;
                height: 100vh;
            }
        }

        /* ===== Left Sidebar ===== */
        .file-manager-sidebar {
            min-width: 260px;
            border-right: 1px solid var(--vz-border-color, #e9ecef);
            background: var(--vz-card-bg, #fff);
        }

        .file-manager-sidebar .sidebar-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--vz-primary, #2074BA);
            border-bottom: 1px solid rgba(64, 81, 137, .15);
            padding-bottom: .5rem;
        }

        .file-manager-menu>li>a {
            display: flex;
            align-items: center;
            border-radius: .5rem;
            transition: background .15s ease, color .15s ease;
        }

        .file-manager-menu>li>a:hover {

            color: var(--vz-primary, #2074BA);
        }

        /* active helper class you can add on server side */
        .file-manager-menu>li>a.active {

            color: var(--vz-primary, #2074BA);
            font-weight: 600;
        }

        file-manager-menu li a.active,
        .file-manager-menu li a:hover,
        .file-manager-menu li a[aria-expanded=true] {
            color: #2074BA;
        }

        /* Submenu indent */
        .file-manager-menu .sub-menu a {
            display: block;
            border-radius: .375rem;
        }

        .file-manager-menu li a.active,
        .file-manager-menu li a:hover,
        .file-manager-menu li a[aria-expanded=true] {
            color: #2074BA;
        }

        /* ===== Right Content Header ===== */
        .content-header {
            border-bottom: 1px solid rgba(64, 81, 137, .15);
            /* same as sidebar underline */
            padding-bottom: .5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Title chunk matches the sidebar-title look */
        .content-header .title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--vz-body-color, #495057);
            /* same neutral as sidebar text */
        }

        /* Icon tint to match sidebar */
        .content-header .title i {
            color: var(--vz-primary, #2074BA);
        }

        /* Back button (grey outline) */
        .btn-back {
            border-color: var(--vz-border-color, #dfe3e8);
            color: #6c757d;
        }

        .btn-back:hover {
            background: #f1f3f5;
            border-color: #cfd4da;
            color: #5c636a;
        }

        /* ===== Progress Bar (blue) ===== */
        .progress.custom-progress {
            height: .75rem;
            border-radius: 999px;
            background: #eef2f7;
        }

        .progress.custom-progress .progress-bar {
            background-color: var(--vz-primary, #2074BA);
            border-radius: 999px;
        }

        .progress-label .label {
            font-size: .7rem;
            font-weight: 600;
            margin-left: .5rem;
            color: var(--vz-primary, #2074BA);
        }

        /* ===== Content container scroll strip border top fix ===== */
        .file-manager-content {
            background: var(--vz-card-bg, #fff);
        }

        .profile-wid-bg {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 90px;
        }

        .profile-wrapper {
            position: relative;
        }

        .profile-wrapper .profile-actions {
            position: absolute;
            top: 0.75rem;
            /* vertical position */
            right: 0;
            /* anchor to right edge */
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            /* force right alignment of buttons */
        }

        .profile-meta-row {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: .35rem;
            white-space: nowrap;
        }

        .profile-meta-label {
            font-weight: 600;
            color: rgba(255, 255, 255, .95);
        }

        .profile-pill {
            display: inline-flex;
            align-items: center;
            padding: .12rem .5rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            color: #212529;
            border: 1px solid rgba(0, 0, 0, .08);
            font-size: .74rem;
            line-height: 1.2;
        }

        .profile-pill.more {
            background: rgba(255, 255, 255, .24);
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: .25rem .65rem;
            line-height: 1;
            border: 0;
            appearance: none;
            -webkit-appearance: none;
        }

        .profile-header-meta {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }
    </style>
    <?= $this->renderSection('head_tag') ?>
</head>

<body class="body-bg">
    <!-- Global Loader (page-level, always on top) -->
    <div id="global_loader"
        class="position-fixed top-0 start-0 w-100 h-100 d-none"
        style="z-index: 20000; background: rgba(255,255,255,0.75);">
        <div class="d-flex justify-content-center align-items-center w-100 h-100">
            <div class="text-center">
                <div class="spinner-border text-info" role="status"></div>
                <div class="mt-2 fw-medium">Loading...</div>
            </div>
        </div>
    </div>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <header id="page-topbar">
                <div class="layout-width">
                    <div class="navbar-header flex-column align-items-stretch p-0">
                        <div class="profile-foreground position-relative mx-n4">
                            <div class="profile-wid-bg">
                                <img src="/assets/images/profile-bg.jpg" alt="" class="profile-wid-img" />
                            </div>
                        </div>
                        <div class="profile-wrapper position-relative px-3 py-3">
                            <div class="row g-3 align-items-center">
                                <div class="col">
                                    <div class="p-2 pe-5">
                                        <h3 class="text-white mb-1">
                                            <?= $client->internal_mrn; ?>
                                        </h3>
                                        <div class="d-flex flex-nowrap align-items-center gap-3 text-white-50 mb-1 profile-header-meta">
                                            <div class="profile-meta-row">
                                                <i class="mdi mdi-account-tie-outline me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                                                <span class="profile-meta-label">Supervisor:</span>
                                                <span class="profile-pill">
                                                    <?= isset($supervisor) ? esc(trim($supervisor->first_name . ' ' . $supervisor->last_name)) : 'No supervisor'; ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($tutors)): ?>
                                                <?php
                                                $visibleTutors = array_slice($tutors, 0, 1);
                                                $remainingTutors = array_slice($tutors, 1);
                                                $remainingTutorNames = implode(', ', array_map(static function ($t) {
                                                    return trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? ''));
                                                }, $remainingTutors));
                                                ?>
                                                <div class="profile-meta-row">
                                                    <i class="mdi mdi-account-group-outline me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                                                    <span class="profile-meta-label">Tutors:</span>
                                                    <?php foreach ($visibleTutors as $tutor): ?>
                                                        <span class="profile-pill"><?= esc(trim(($tutor->first_name ?? '') . ' ' . ($tutor->last_name ?? ''))) ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($remainingTutors) > 0): ?>
                                                        <button type="button" class="profile-pill more"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            data-bs-trigger="hover focus"
                                                            data-bs-container="body"
                                                            title="<?= esc($remainingTutorNames) ?>">
                                                            +<?= count($remainingTutors) ?> more
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="profile-meta-row">
                                                    <i class="mdi mdi-account-group-outline me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                                                    <span class="profile-meta-label">Tutors:</span>
                                                    <span class="profile-pill">No tutor</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-actions">
                                    <a href="<?= base_url() . 'client-profile/list' ?>" class="btn btn-light btn-icon fs-16 me-1"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Back to Client List">
                                        <i class="ri-arrow-left-line align-middle"></i>
                                    </a>
                                    <a href="#" class="btn btn-light btn-icon fs-16 me-1"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Chat (Coming Soon)">
                                        <i class="ri-discuss-line align-middle"></i>
                                    </a>
                                    <a href="<?= base_url() . 'sessions/live/client/' . encodeValue($client->id); ?>" class="btn btn-light btn-icon fs-16 me-1"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Take Session">
                                        <i class="ri-user-smile-line align-middle"></i>
                                    </a>
                                    <button type="button" class="btn btn-light btn-icon fs-16 file-menu-btn d-lg-none">
                                        <i class="ri-menu-2-fill align-middle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="page-content">
                <div class="container-fluid">
                    <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
                        <div class="file-manager-sidebar">
                            <?= $this->include('ClientProfile/profile-sidebar') ?>
                        </div>
                        <div class="file-manager-content w-100 p-3 py-0">
                            <div class="mx-n3 pt-4 px-4 file-manager-content-scroll" data-simplebar>
                                <div>
                                    <?= $this->renderSection('page_content') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            <?= $this->include('layout/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->
    <?= $this->renderSection('page_modal') ?>
    <?= $this->include('layout/vendor-scripts') ?>
    <!-- App js -->
    <?= $this->include('layout/app-js') ?>
    <script>
        // Sidebar toggle
        var sidebar = document.querySelector(".file-manager-sidebar");
        document.querySelectorAll(".file-menu-btn").forEach(function(btn) {
            btn.addEventListener("click", function() {
                sidebar.classList.add("menubar-show");
            });
        });

        window.addEventListener("click", function(e) {
            if (sidebar.classList.contains("menubar-show") &&
                !e.target.closest(".file-manager-sidebar") &&
                !e.target.closest(".file-menu-btn")) {
                sidebar.classList.remove("menubar-show");
            }
        });
    </script>
    <script>
        // Global Loader Helpers (usable in all pages)
        window.showPageLoader = function() {
            $("#global_loader").removeClass("d-none");
        };

        window.hidePageLoader = function() {
            $("#global_loader").addClass("d-none");
        };
    </script>
    <script>
        // Initialize Bootstrap tooltips for profile header action buttons
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
    </script>
    <?= $this->renderSection('page_js') ?>
</body>

</html>
