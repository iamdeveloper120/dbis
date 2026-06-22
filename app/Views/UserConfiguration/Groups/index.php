<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Staff Groups/Roles</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Groups/Roles</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered  nowrap  align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Description</th>
                                <th>Users in Group</th>
                                <th style="width:100px">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (isset($groups) && count($groups)) : ?>
                                <?php foreach ($groups as $alias => $group) : ?>
                                    <tr>
                                        <td>
                                            <a href="/user-configuration/groups/<?= $alias ?>">
                                                <?= esc($group['title']) ?>
                                            </a>
                                        </td>
                                        <td><?= esc($group['description']) ?></td>
                                        <td class="text-center"><?= esc(number_format($group['user_count'])) ?></td>
                                        <td><a href="/user-configuration/groups/<?= $alias ?>" type="button" class="btn btn-sm  btn-outline-warning"><i class="ri-edit-line align-bottom me-1"></i>Edit</a></td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
        <!--end col-->
    </div>
</div>
<?= $this->endSection() ?>