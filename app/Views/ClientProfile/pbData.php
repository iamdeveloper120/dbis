 <?= $this->extend("layout/master-profile") ?>

 <?= $this->section("head_tag") ?>
 <?= view('Shared/DataSheet/pbData/head') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_content") ?>
  <div class="mx-n3 pt-2 px-2">
     <?= view('Shared/DataSheet/pbData/content') ?>
 </div>

 <?= $this->endSection() ?>

 <?= $this->section("page_modal") ?>
 <?= view('Shared/DataSheet/pbData/modals') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_js") ?>
 <?= view('Shared/DataSheet/pbData/js') ?>
 <?= $this->endSection() ?>