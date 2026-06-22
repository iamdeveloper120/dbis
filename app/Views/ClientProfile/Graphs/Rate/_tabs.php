<ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-0" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'graphs-rate') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/rate/' . encodeValue($client->id)) ?>">
            <i class="ri-line-chart-line me-1"></i>Rate Graph
        </a>
    </li> 
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'phase-line') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/rate/phaseline/' . encodeValue($client->id)) ?>">
            <i class="ri-bar-chart-line me-1"></i>Rate Graphs Phase Line
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($tab === 'target-months') : ?> active <?php endif ?>" href="<?= base_url('client-profile/graphs/rate/target-months/' . encodeValue($client->id)) ?>">
           <i class="ri-calendar-line me-1"></i>Rate Graphs Target Months
        </a>
    </li>
</ul>