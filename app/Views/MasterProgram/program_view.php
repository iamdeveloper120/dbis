<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="alert bg-light border-light text-body material-shadow" role="alert" style="font-size: 15px;">
     The Master Program stores Domains, Goals, and Targets that can be added to individual client programs. This allows teams to create, organise, and reuse program content across the organisation.
     When setting up a Domain, add a short abbreviation that can be used within Goals and Targets and displayed consistently across the tool. For example, the Domain &ldquo;Imitation&rdquo; may use the abbreviation &ldquo;IM&rdquo;, with Goals labelled as &ldquo;IM-1 Object Imitation&rdquo;.
</div>
<div id="master-program-app"></div> <!-- Root element for React -->
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="<?= base_url() ?>react/MasterProgramTreeView/MasterProgramTreeView.js" type="module"></script>
<?= $this->endSection() ?>
