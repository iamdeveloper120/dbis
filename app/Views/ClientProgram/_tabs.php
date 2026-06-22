<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-0" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'domains') : ?> active <?php endif ?>" href="/client-program/<?= $client_id ?>/domains">
            <i class="bx bx-folder align-middle me-1"></i>Domains
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'goals') : ?> active <?php endif ?>" href="/client-program/<?= $client_id ?>/goals">
            <i class="bx bx-flag  align-middle me-1"></i>Goals
        </a>
    </li>


    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'targets') : ?> active <?php endif ?>" href="/client-program/<?= $client_id ?>/targets">
            <i class="bx bx-bullseye align-middle me-1"></i>Targets
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'tree-view') : ?> active <?php endif ?>" href="/client-program/treeView">
            <i class="ri-check-double-fill align-bottom me-1"></i>Review Complete Program
        </a>
    </li>

</ul>