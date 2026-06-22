<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'basics') : ?> active <?php endif ?>" href="<?= isset($group) ? base_url().'user-configuration/groups/' . $group : '#' ?>">
            <i class=" ri-team-line align-middle me-1"></i>Group Details
        </a>
    </li>
     
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php if ($tab === 'permissions') : ?> active <?php endif ?>" href="<?= base_url().'user-configuration/groups/' . $group . '/permissions' ?>">
                <i class="ri-shield-user-line align-middle me-1"></i>Permissions
            </a>
        </li>
 
</ul>