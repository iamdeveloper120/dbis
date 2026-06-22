<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
    div.form-block select * {
        color: #000 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?> 
<div class="alert bg-light border-light text-body material-shadow" role="alert" style="font-size: 15px;">
    <strong>Program Wizard:</strong> The Client Wizard is used to add Domains and Goals from the organisation&rsquo;s Master Program into an individual client program. You must first link the Domain to the client before Goals within that Domain become available to link.
</div>
<div id="client-program-wizard-app"></div> <!-- Root element for React -->
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="/react/ClientProgramWizard/ClientProgramWizard.js" type="module"></script>
<?= $this->endSection() ?>
