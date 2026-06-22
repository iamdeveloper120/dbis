<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Group/Role Edit</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Edit Detail</li>
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
                            <h5 class="card-title mb-0">Edit Group "<span class="text-primary"><i><?= esc($group['title']) ?></i></span>" &amp; Permissions</h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex flex-wrap align-items-start gap-2">
                            <a href="/user-configuration/groups" type="button" class="btn btn-primary"><i class="ri-arrow-go-back-fill align-bottom me-1"></i> Groups</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('UserConfiguration/Groups/_tabs', ['tab' => 'basics', 'group' => $groupAlias]) ?>
                <div class="tab-content text-muted">
                    <div class="tab-pane active show" role="tabpanel">
                        <form action="<?= current_url() ?>" method="post">
                            <?= csrf_field() ?>
                            <fieldset>
                                <p>Update Group Information.</p>
                                <?php if (session()->getFlashdata('message')) : ?>
                                    <div class="alert alert-success">
                                        <?= session()->getFlashdata('message') ?>
                                    </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <!-- First Name -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" autocomplete="title" value="<?= old('title', $group['title'] ?? '') ?>">
                                            <?php if (has_error('title')) : ?>
                                                <p class="text-danger"><?= error('title') ?></p>
                                            <?php endif ?>
                                        </div>

                                        <!-- Description -->
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" rows="3" class="form-control"><?= old('description', $group['description'] ?? '') ?></textarea>
                                            <?php if (has_error('description')) : ?>
                                                <p class="text-danger"><?= error('description') ?></p>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>

                            </fieldset>
                            <hr>
                            <div class="text-end" style="float:right">
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line align-bottom me-1"></i>Save Group</button>
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