 
    <div class="col-md-12">
        <div class="card card border card-border-info">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0"><?= $permission_card_title ?></h6>
                    </div>
                    <div class="flex-shrink-0">
                        <ul class="list-inline card-toolbar-menu d-flex align-items-center mb-0">

                            <li class="list-inline-item">
                                <a class="align-middle minimize-card" data-bs-toggle="collapse" href="#<?= $permission_card_id ?>" role="button" aria-expanded="true" aria-controls="collapseExample2">
                                    <i class="mdi mdi-plus align-middle plus"></i>
                                    <i class="mdi mdi-minus align-middle minus"></i>
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body collapse show" id="<?= $permission_card_id ?>">
                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <tbody>
                            <?php foreach ($permissions as $permission => $description) : ?>
                                <?php if (strpos($permission, $permission_comparison) === 0) : ?>                                  
                                    <tr>
                                        <td style="width: 2%;"><input type="checkbox" name="permissions[]" value="<?= $permission ?>" <?= $group->can($permission) ? 'checked' : '' ?>></td>
                                      
                                        <td style="width: 98%;"><?= $description ?></td>
                                    </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- end col -->
 
