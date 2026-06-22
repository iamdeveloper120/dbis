<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <?php echo view('layout/title-meta', array('title' => $page_title)); ?>
    <?= $this->include('layout/head-css') ?>
    <?= $this->renderSection('head_tag') ?>
    <style>
        .main-content {
            margin-left: 26px;
            margin-right: 26px;
        }
    </style>

</head>

<body class="body-bg">
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= $this->renderSection('page_content') ?>
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->
    <?= $this->renderSection('page_modal') ?>
    <?= $this->include('layout/vendor-scripts') ?>
    <!-- App js -->
    <?= $this->include('layout/app-js') ?>
    <?= $this->renderSection('page_js') ?>
</body>

</html>