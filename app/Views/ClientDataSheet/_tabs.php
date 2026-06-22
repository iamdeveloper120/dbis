 <ul class="nav nav-pills arrow-navtabs nav-primary bg-light mb-3" role="tablist">
     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'yesNoTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/programData/' . encodeValue($client->id); ?>">
             Programs
         </a>
     </li>    

     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'mandsTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/mandsDataSheet/' . encodeValue($client->id); ?>">
             Mands
         </a>
     </li>
     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'pbTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/pbDataSheet/' . encodeValue($client->id); ?>">
             Problem Behaviour
         </a>
     </li>
     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'pgTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/getProgramChange/' . encodeValue($client->id); ?>">
             Program Change
         </a>
     </li>
     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'skillsTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/getSkillsRetained/' . encodeValue($client->id); ?>">
             Skills Retained
         </a>
     </li>
     <li class="nav-item" role="presentation">
         <a class="nav-link <?php if ($tab === 'doiTab') : ?> active <?php endif ?>" href="<?= base_url() . 'dataSheet/getDOITargets/' . encodeValue($client->id); ?>">
             DOI
         </a>
     </li>



 </ul>