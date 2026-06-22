<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-0" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph') : ?> active <?php endif ?>" href="/graphs/cumulative">
            <i class="ri-line-chart-line me-1"></i>Cumulative Graph
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'phase-line') : ?> active <?php endif ?>" href="/graphs/cumulative/phase-line">
            <i class="ri-bar-chart-line me-1"></i>Cumulative Graph Phase Line
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph-domain') : ?> active <?php endif ?>" href="/graphs/cumulative/domains-and-goals">
            <i class="ri-line-chart-line me-1"></i>Cumulative Graph (Domains and Goals)
        </a>
    </li>

</ul>