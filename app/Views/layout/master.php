<?= $this->include('layout/main') ?>

<head>
    <?php echo view('layout/title-meta', array('title' => isset($page_title) ? $page_title : "MIS")); ?>
    <?= $this->include('layout/head-css') ?>
    <?= $this->renderSection('head_tag') ?>
</head>

<body class="body-bg">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('layout/menu') ?>
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
            <?= $this->include('layout/footer') ?>
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