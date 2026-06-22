<?= $this->include('layout/main') ?>
<head>
    <?php echo view('layout/title-meta', array('title' => 'Access Denied')); ?>
    <?= $this->include('layout/head-css') ?>
</head>
<body>
    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <img src="<?= base_url()?>assets/images/auth-offline.gif" alt="" height="210">
                                    <h3 class="mt-4 fw-semibold">Access defined</h3>
                                    <p class="text-muted mb-4 fs-14"></p>
                                    <a class="btn btn-success btn-border" href="/"><i class="ri-home-line align-bottom"></i> Back to home</a>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->
    </div>
    <!-- end auth-page-wrapper -->

    <?= $this->include('layout/vendor-scripts') ?>

</body>

</html>