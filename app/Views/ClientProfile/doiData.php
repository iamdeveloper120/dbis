  <?= $this->extend("layout/master-profile") ?>

 <?= $this->section("head_tag") ?>
 <?= view('Shared/DataSheet/doiData/head') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_content") ?>
  <div class="mx-n3 pt-2 px-2">
     <?= view('Shared/DataSheet/doiData/content') ?>
 </div>

 <?= $this->endSection() ?>

 <?= $this->section("page_modal") ?>
 <?= view('Shared/DataSheet/doiData/modals') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_js") ?>
 <?= view('Shared/DataSheet/doiData/js') ?>
 <?= $this->endSection() ?>