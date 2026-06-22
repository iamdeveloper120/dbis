<?= $this->extend("layout/master") ?>

<?= $this->section("head_tag") ?>
<!--<link href="<?php echo base_url() ?>/auth/css/auth.css" rel="stylesheet">-->
<style>
    /*
 * --------------------------------------------------------------------------
 * PASSWORDS
 * --------------------------------------------------------------------------
 */
    :root {
        --bf-primary: #0275d8;
        --bf-body-bg: #F1F5F9;
        --bf-black: #222222;
        --bf-blue: #2470dc;
        --bf-grey: #727272;
        --bf-light-grey: #d2d2d2;
        --bf-white: #f7f7f7;
        --bf-success: #5cb85c;
        --info: #5bc0de;
        --warning: #f0ad4e;
        --danger: #d9534f;
        --dark: #292b2c;
    }

    div#pass-meter {
        display: flex;
        flex-direction: column-reverse;
    }

    div#pass-meter .segment {
        display: block;
        background: var(--bf-white);
        border: 1px solid var(--bf-light-grey);
        height: 9px;
        width: 30px;
    }

    div#pass-meter .segment:last-child {
        margin-right: 0;
    }

    div#pass-meter.good .segment {
        background-color: var(--bf-success);
        border-color: var(--bf-success);
        border-bottom: 1px solid var(--bf-white);
    }

    div#pass-meter.warn .segment {
        background-color: var(--warning);
        border-color: var(--warning);
        border-bottom: 1px solid var(--bf-white);
    }

    div#pass-meter.bad .segment {
        background-color: var(--danger);
        border-color: var(--danger);
    }

    div#pass-meter.str-1 .segment:nth-child(2),
    div#pass-meter.str-1 .segment:nth-child(3),
    div#pass-meter.str-1 .segment:nth-child(4) {
        background-color: var(--bf-white);
        border-color: var(--bf-light-grey);
    }

    div#pass-meter.str-2 .segment:nth-child(3),
    div#pass-meter.str-2 .segment:nth-child(4) {
        background-color: var(--bf-white);
        border-color: var(--bf-light-grey);
    }

    div#pass-meter.str-3 .segment:nth-child(4) {
        background-color: var(--bf-white);
        border-color: var(--bf-light-grey);
    }

    #pass-suggestions {
        text-align: center;
        color: var(--bf-grey);
        font-size: 0.8rem;
        margin-top: -15px;
        margin-bottom: 10px;
        min-height: 2.5rem;
    }

    .pass-match-wrap {
        display: flex;
        align-content: center;
        justify-content: center;
        width: 53px;
    }

    .pass-match,
    .pass-not-match {
        display: inline-block;
        margin: auto;
        font-size: 20px;
        color: var(--bf-success);
    }

    .pass-not-match {
        color: var(--danger);
    }


    .collapsible .signal {
        position: absolute;
        right: 5px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Staff Member Password Update</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Update</li>
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
                            <a href="/user-configuration/users/active-list" type="button" class="btn btn-primary"><i class="ri-arrow-go-back-fill align-bottom me-1"></i> Staff Member List</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">


                <?= view('UserConfiguration/Users/_tabs', ['tab' => 'security', 'user' => $user]) ?>
                <div class="tab-content text-muted">
                    <div class="tab-pane active show" role="tabpanel">

                        <fieldset>
                            <legend>Change password</legend>
                            <p><?php
                                // Check if there's a flashed message
                                if (session()->has('message')) {
                                    // Get and display the flashed message
                                    $message = session('message');
                                    echo '<div class="alert alert-success">' . $message . '</div>';
                                }
                                if (session()->has('errors')) {
                                    // Get and display the flashed message
                                    $messages = session('errors');
                                    if (array_key_exists('password', $messages)) {
                                        echo '<div class="alert alert-danger">' . $messages['password'] . '</div>';
                                    }
                                    if (array_key_exists('pass_confirm', $messages)) {
                                        echo '<div class="alert alert-danger">' . $messages['pass_confirm'] . '</div>';
                                    }
                                }
                                ?></p>
                            <?= view('UserConfiguration/Users/password_change', ['user' => $user ?? null]) ?>
                        </fieldset>
                        <fieldset>
                            <legend>Recent Logins</legend>

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IP Address</th>
                                        <th>User Agent</th>
                                        <th>Success?</th>
                                    </tr>
                                </thead>
                                <?php if (isset($logins) && count($logins)) : ?>
                                    <tbody>
                                        <?php foreach ($logins as $login) : ?>
                                            <tr>
                                                <td><?= app_date($login->date, true, true) ?></td>
                                                <td><?= $login->ip_address ?? '' ?></td>
                                                <td><?= $login->user_agent ?? '' ?></td>
                                                <td>
                                                    <?php if ($login->success) : ?>
                                                        <span class="badge rounded-pill bg-success">Success</span>
                                                    <?php else : ?>
                                                        <span class="badge rounded-pill bg-secondary">Failed</span>
                                                    <?php endif ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                <?php else : ?>
                                    <div class="alert alert-secondary">No recent login attempts.</div>
                                <?php endif ?>
                            </table>
                        </fieldset>
                    </div>
                </div>
            </div>


        </div>
        <!--end col-->
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="<?php echo base_url() ?>assets/auth/js/passStrength.js"></script>
<script src="<?php echo base_url() ?>assets/auth/js/zxcvbn.js"></script>
<?= $this->endSection() ?>