 <?= $this->extend("layout/master-profile") ?>

 <?= $this->section("head_tag") ?>
 <?= view('Shared/DataSheet/pcData/head') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_content") ?>
 <div class="mx-n3 pt-2 px-2">
     <?= view('Shared/DataSheet/pcData/content') ?>
 </div>

 <?= $this->endSection() ?>

 <?= $this->section("page_modal") ?>
 <?= view('Shared/DataSheet/pcData/modals') ?>
 <?= $this->endSection() ?>

 <?= $this->section("page_js") ?>
 <?= view('Shared/DataSheet/pcData/js') ?>
 <?= $this->endSection() ?>