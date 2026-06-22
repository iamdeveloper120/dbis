<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">New/Edit Staff Member</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Edit</li>
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
                            <h5 class="card-title mb-0"><?= isset($user) ? $user->name()  : 'New Staff Member' ?></h5>
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
                <?php if (isset($user) && $user->deleted_at !== null) : ?>
                    <div class="alert alert-borderless alert-danger" role="alert">
                        This staff member was deleted on <?= $user->deleted_at->humanize() ?>.
                        <a href="#">Restore staff member?</a>
                    </div>
                <?php endif ?>

                <?= view('UserConfiguration/Users/_tabs', ['tab' => 'details', 'user' => $user ?? null]) ?>
                <div class="tab-content text-muted">
                    <div class="tab-pane active show" role="tabpanel">

                        <?php if (isset($user) && $user !== null) : ?>
                            <form action="/user-configuration/users/<?= $user->id ?>/save" method="post" enctype="multipart/form-data">
                            <?php else : ?>
                                <form action="/user-configuration/users/save" method="post" enctype="multipart/form-data">
                                <?php endif ?>
                                <?= csrf_field() ?>

                                <?php if (isset($user) && $user !== null) : ?>
                                    <input type="hidden" name="id" value="<?= $user->id ?>">
                                <?php endif ?>

                                <fieldset>
                                    <legend>Basic Info</legend>
                                    <p><?php
                                        // Check if there's a flashed message
                                        if (session()->has('message')) {
                                            // Get and display the flashed message
                                            $message = session('message');
                                            echo '<div class="alert alert-success">' . $message . '</div>';
                                        }
                                        ?></p>
                                    <div class="row g-3">
                                        <!-- Email Address -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div><label for="email" class="form-label">Email Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="email">@</span>
                                                    <input type="text" name="email" class="form-control" autocomplete="email" value="<?= old('email', $user->email ?? '') ?>">
                                                </div>
                                                <?php if (has_error('email')) : ?>
                                                    <p class="text-danger"><?= error('email') ?></p>
                                                <?php endif ?>
                                            </div>

                                        </div>
                                        <!-- Username -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div><label for="username" class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control" autocomplete="username" value="<?= old('username', $user->username ?? '') ?>">
                                                <?php if (has_error('username')) : ?>
                                                    <p class="text-danger"><?= error('username') ?></p>
                                                <?php endif ?>
                                            </div>

                                        </div>
                                        <!-- First Name -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div><label for="first_name" class="form-label">First Name</label>
                                                <input type="text" name="first_name" class="form-control" autocomplete="first_name" value="<?= old('first_name', $user->first_name ?? '') ?>">
                                                <?php if (has_error('first_name')) : ?>
                                                    <p class="text-danger"><?= error('first_name') ?></p>
                                                <?php endif ?>
                                            </div>

                                        </div>
                                        <!-- Last Name -->
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div> <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" name="last_name" class="form-control" autocomplete="last_name" value="<?= old('last_name', $user->last_name ?? '') ?>">
                                                <?php if (has_error('last_name')) : ?>
                                                    <p class="text-danger"><?= error('last_name') ?></p>
                                                <?php endif ?>
                                            </div>

                                        </div>
                                    </div>
                                </fieldset>
                                <hr>
                                <fieldset>
                                    <legend>Groups</legend>
                                    <p>Select one or more groups for the staff member to belong to.</p>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <select name="groups[]" multiple="multiple" class="form-control" style="height: 200px;">
                                                <?php foreach ($groups as $group => $info) : ?>
                                                    <?php if (! ($group == 'superadmin')): ?>
                                                        <option value="<?= $group ?>" <?php if (isset($user) && $user->inGroup($group)) : ?> selected <?php endif ?>>
                                                            <?= $info['title'] ?? $group ?>
                                                        </option>
                                                    <?php endif ?>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                    </div>



                    <!-- User Meta Fields -->


                    <div class="text-end py-3" style="float:right">
                        <button type="submit" value="Save Staff Member" class="btn btn-primary"><i class="ri-save-line align-bottom me-1"></i>Save Staff Member</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>


    </div>
    <!--end col-->
</div>
</div>
<?= $this->endSection() ?>