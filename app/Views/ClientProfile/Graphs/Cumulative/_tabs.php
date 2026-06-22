<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-0" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/cumulative/' . encodeValue($client->id)) ?>">
            <i class="ri-line-chart-line me-1"></i>Cumulative Graph
        </a>
    </li> 
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph-domain') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/cumulative/domains-and-goals/' . encodeValue($client->id)) ?>">
            <i class="ri-line-chart-line me-1"></i>Cumulative Graph (Domains and Goals)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph-phaseline') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/cumulative/phaseline/' . encodeValue($client->id)) ?>">
            <i class="ri-line-chart-line me-1"></i>Cumulative Graph Phase Line
        </a>
    </li>
</ul>