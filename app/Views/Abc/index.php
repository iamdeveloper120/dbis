<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
  
<div class="card mb-3">
    <div class="card-header">
        <h6 class="card-title mb-0">Configure ABC data collection</h6>
    </div>
    <div class="card-body">
        <p class="text-black fs-5 mb-0">
            This tool is configured to use the organisation’s default ABC template. To individualise ABC data for a learner, select the learner and configure a learner-specific ABC template.
        </p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <select id="client_abc_client" class="form-control" aria-label="Select Client">
                    <option value="">Select Client</option>
                    <?php foreach (($clients ?? []) as $client): ?>
                        <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-primary" id="save_client_abc">
                    <i class="ri-save-line align-bottom me-1"></i>Save Client ABC
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Antecedent</h6></div>
                    <div class="card-body">
                        <div id="client_antecedent_container"></div>
                        <div class="input-group mt-2">
                            <input type="text" id="antecedent_input" class="form-control" placeholder="Add antecedent">
                            <button class="btn btn-outline-primary" type="button" id="add_antecedent_btn">Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Behavior</h6></div>
                    <div class="card-body">
                        <div id="client_behavior_container"></div>
                        <div class="input-group mt-2">
                            <input type="text" id="behavior_input" class="form-control" placeholder="Add behavior">
                            <button class="btn btn-outline-primary" type="button" id="add_behavior_btn">Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Consequence</h6></div>
                    <div class="card-body">
                        <div id="client_consequence_container"></div>
                        <div class="input-group mt-2">
                            <input type="text" id="consequence_input" class="form-control" placeholder="Add consequence">
                            <button class="btn btn-outline-primary" type="button" id="add_consequence_btn">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title mb-1">Master ABC Default</h6>
        <p class="text-black mb-0">Default ABC data used for clients who do not have client-specific ABC data.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Antecedent</h6></div>
                    <div class="card-body"><ul id="master_antecedent_list" class="mb-0 ps-3"></ul></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Behavior</h6></div>
                    <div class="card-body"><ul id="master_behavior_list" class="mb-0 ps-3"></ul></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border card-border-primary h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Consequence</h6></div>
                    <div class="card-body"><ul id="master_consequence_list" class="mb-0 ps-3"></ul></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
$(function() {
    const csrfToken = "<?= csrf_hash() ?>";
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    const endpoints = {
        masterList: '<?= base_url('abc-data/master/list') ?>',
        clientList: '<?= base_url('abc-data/client/list') ?>',
        clientSave: '<?= base_url('abc-data/client/save') ?>'
    };
    const state = { antecedent: [], behavior: [], consequence: [] };
    if ($.fn.select2) $('#client_abc_client').select2({ width: '100%' });
    const esc = (v) => $('<div>').text(v || '').html();
    const normalize = (list) => Object.values((list || []).reduce((a, i) => { const v = String(i || '').trim(); if (v) a[v.toLowerCase()] = v; return a; }, {}));
    function renderMaster(list, target) {
        const el = $(target); el.empty();
        if (!list || !list.length) return el.append('<li class="text-black">No data.</li>');
        list.forEach(v => el.append('<li>' + esc(v) + '</li>'));
    }
    function renderSection(section, container) {
        const c = $(container); c.empty();
        const items = state[section] || [];
        if (!items.length) return c.append('<div class="text-black small">No data added.</div>');
        items.forEach((v, idx) => c.append(`<div class="input-group mb-2" data-section="${section}" data-index="${idx}"><input type="text" class="form-control section-item-input" value="${esc(v)}"><button type="button" class="btn btn-outline-danger remove-item-btn"><i class="ri-delete-bin-line"></i></button></div>`));
    }
    function renderAll() { renderSection('antecedent', '#client_antecedent_container'); renderSection('behavior', '#client_behavior_container'); renderSection('consequence', '#client_consequence_container'); }
    function loadMaster() {
        $.getJSON(endpoints.masterList, (r) => {
            const d = (r && r.data) || {};
            renderMaster(d.antecedent || [], '#master_antecedent_list');
            renderMaster(d.behavior || [], '#master_behavior_list');
            renderMaster(d.consequence || [], '#master_consequence_list');
        });
    }
    function clearClient() { state.antecedent = []; state.behavior = []; state.consequence = []; renderAll(); }
    function loadClient() {
        const clientId = $('#client_abc_client').val();
        if (!clientId) return clearClient();
        $.getJSON(endpoints.clientList, { client_id: clientId }, (r) => {
            const d = (r && r.data) || {};
            state.antecedent = normalize(d.antecedent || []);
            state.behavior = normalize(d.behavior || []);
            state.consequence = normalize(d.consequence || []);
            renderAll();
        });
    }
    function requireClientSelected() {
        if ($('#client_abc_client').val()) return true;
        showAlert('Validation', 'Please select a client before adding ABC data.', 'error');
        $('#client_abc_client').focus();
        return false;
    }
    function add(section, inputSel) {
        if (!requireClientSelected()) return;
        const input = $(inputSel), v = String(input.val() || '').trim();
        if (!v) return;
        if ((state[section] || []).map(x => x.toLowerCase()).includes(v.toLowerCase())) return input.val('');
        state[section].push(v); input.val(''); renderAll();
    }
    $('#add_antecedent_btn').on('click', () => add('antecedent', '#antecedent_input'));
    $('#add_behavior_btn').on('click', () => add('behavior', '#behavior_input'));
    $('#add_consequence_btn').on('click', () => add('consequence', '#consequence_input'));
    $(document).on('click', '.remove-item-btn', function() {
        const g = $(this).closest('[data-section]'), s = g.data('section'), i = parseInt(g.data('index'), 10);
        if (s && !Number.isNaN(i)) { state[s].splice(i, 1); renderAll(); }
    });
    $(document).on('change', '.section-item-input', function() {
        const g = $(this).closest('[data-section]'), s = g.data('section'), i = parseInt(g.data('index'), 10);
        if (!s || Number.isNaN(i)) return;
        const v = String($(this).val() || '').trim();
        if (!v) state[s].splice(i, 1); else { state[s][i] = v; state[s] = normalize(state[s]); }
        renderAll();
    });
    $('#client_abc_client').on('change', loadClient);
    $('#save_client_abc').on('click', function() {
        const clientId = $('#client_abc_client').val();
        if (!clientId) return showAlert('Validation', 'Please select a client.', 'error');
        const antecedents = normalize(state.antecedent), behaviors = normalize(state.behavior), consequences = normalize(state.consequence);
        if (!antecedents.length || !behaviors.length || !consequences.length) return displayValidationErrors(['Antecedent, Behavior and Consequence each require at least one value before save.']);
        $.ajax({
            url: endpoints.clientSave, method: 'POST', dataType: 'json',
            data: { client_id: clientId, antecedents: JSON.stringify(antecedents), behaviors: JSON.stringify(behaviors), consequences: JSON.stringify(consequences) },
            success: function(r) {
                if (r.status === 'success') { showAlert(r.statusText, r.message, r.status); loadClient(); }
                else if (r.status === 'error' && r.statusText === 'Validation_Error') displayValidationErrors(Object.values(r.validationErrors || {}));
                else showAlert(r.statusText || 'Error', r.message || 'Save failed', r.status || 'error');
            }
        });
    });
    loadMaster(); clearClient();
});
</script>
<?= $this->endSection() ?>
