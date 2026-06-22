<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'details') : ?> active <?php endif ?>"
            href="<?= isset($user) ? '/user-configuration/users/edit/' . $user->id : '#' ?>">
            <i class=" ri-user-settings-line align-middle me-1"></i>Staff Member Details
        </a>
    </li>
    <?php if (isset($user) && $user !== null) : ?>

        <li class="nav-item" role="presentation">
            <a class="nav-link <?php if ($tab === 'permissions') : ?> active <?php endif ?>"
                href="/user-configuration/users/edit/<?= $user->id ?>/permissions">
                <i class="ri-shield-user-line align-middle me-1"></i>Permissions
            </a>
        </li>

    <?php endif ?>

    <li class="nav-item" role="presentation">
        <?php if (isset($user) && $user !== null) : ?>
            <a class="nav-link <?php if ($tab === 'security') : ?> active <?php endif ?>"
                href="/user-configuration/users/edit/<?= $user->id ?>/security">
                <i class=" ri-key-line align-middle me-1"></i>Security
            </a>
        <?php endif ?>
    </li>

</ul>