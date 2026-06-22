<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-0" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graph') : ?> active <?php endif ?>" href="/graphs/rate">
            <i class="ri-line-chart-line me-1"></i>Rate Graphs
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'phase-line') : ?> active <?php endif ?>" href="/graphs/rate/phase-line">
            <i class="ri-bar-chart-line me-1"></i>Rate Graphs Phase Line
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'target-months') : ?> active <?php endif ?>" href="/graphs/rate/target-months">
            <i class="ri-calendar-line me-1"></i>Rate Graphs Target Months
        </a>
    </li>
 

</ul>