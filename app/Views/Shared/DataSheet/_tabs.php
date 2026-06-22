<ul class="nav nav-pills arrow-navtabs nav-primary bg-light mb-3" role="tablist">
    <?php
    $tabs = [
        'yesNoTab'   => ['route' => 'programData',        'label' => 'Programs'],
        'mandsTab'   => ['route' => 'mandsData',          'label' => 'Mands'],
        'pbTab'      => ['route' => 'pbData',             'label' => 'Problem Behaviour'],
        'pcTab'      => ['route' => 'pcData',             'label' => 'Program Change'],
        'skillsTab'  => ['route' => 'skillsData',         'label' => 'Skills Retained'],
        'doiTab'     => ['route' => 'doiData',            'label' => 'DOI'],
    ];

    foreach ($tabs as $key => $info):
        $isActive = ($tab === $key) ? 'active' : '';
        $url = base_url("{$tabRoutePrefix}/{$info['route']}/" . encodeValue($client->id));
    ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $isActive ?>" href="<?= $url ?>">
                <?= esc($info['label']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>