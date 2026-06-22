<?php
$fmtDate = static function ($v) {
    return !empty($v) ? esc(app_date($v)) : '-';
};
$effectiveTeachingProcedure = $effective_teaching_procedure ?? [];
if (!is_array($effectiveTeachingProcedure)) {
    $effectiveTeachingProcedure = [];
}
$effectiveField = static function (array $data, string $key): string {
    $value = trim((string) ($data[$key] ?? ''));
    return $value !== '' ? $value : '-';
};
?>
<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .file-manager-content-scroll {
        padding-bottom: 150px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <!-- Accordions Bordered (Read-only) -->
    <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="accordionBordered">
  
            <!-- 7️⃣ Key Information -->
            <div class="accordion-item mt-2 material-shadow">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseEffectiveTeachingProcedures" ria-expanded="true">
                        Key Information
                    </button>
                </h2>
                <div id="collapseEffectiveTeachingProcedures" class="accordion-collapse collapse show">
                    <div class="accordion-body bg-light-subtle rounded-3 p-3">
                        <div class="row g-3">
                            <div class="col-12 d-none">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Competing Positive Reinforcers</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'competing_positive_reinforcers'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Mix and Vary Tasks</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'mix_and_vary_tasks'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Errorless Teaching Procedures</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'errorless_teaching_procedures'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Percentage of Easy to Hard Tasks</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'easy_to_hard_percentage'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Easy Responses That Can Be Faded In at Start of Instruction</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'easy_responses_fade_start'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">Schedule of Reinforcement</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'schedule_of_reinforcement'))) ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 border rounded-3 h-100">
                                    <div class="text-black small mb-1">General Comments</div>
                                    <div class="text-black text-break"><?= nl2br(esc($effectiveField($effectiveTeachingProcedure, 'general_comment'))) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


    </div>



</div>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    // Debounce helper
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function initOrRecalcSimpleBars() {
        document.querySelectorAll("[data-simplebar]").forEach(el => {
            // Ensure SimpleBar instance exists
            if (!el.SimpleBar && window.SimpleBar) {
                new SimpleBar(el, {
                    autoHide: false
                }); // or your preferred options
            }
            // Recalculate if present
            if (el.SimpleBar) el.SimpleBar.recalculate();
        });
    }

    const recalcSimpleBars = debounce(initOrRecalcSimpleBars, 60);

    // Existing: recalc on Bootstrap tab shown
    document.addEventListener('shown.bs.tab', recalcSimpleBars);

    // ✅ NEW: recalc when a collapse finishes opening/closing (height changed)
    document.addEventListener('shown.bs.collapse', recalcSimpleBars);
    document.addEventListener('hidden.bs.collapse', recalcSimpleBars);

    // (Optional) If you use modals that inject content heights
    document.addEventListener('shown.bs.modal', recalcSimpleBars);
    document.addEventListener('hidden.bs.modal', recalcSimpleBars);

    // Existing: on full load (after charts/styles settle)
    window.addEventListener('load', () => {
        setTimeout(recalcSimpleBars, 300);
    });

    // Existing: on resize
    window.addEventListener('resize', recalcSimpleBars);

    // ✅ Optional safety net: observe DOM mutations inside the scroll area
    //    (useful when tables render async or charts update)
    const scrollHost = document.querySelector('.file-manager-content-scroll');
    if (scrollHost && window.MutationObserver) {
        const mo = new MutationObserver(recalcSimpleBars);
        mo.observe(scrollHost, {
            childList: true,
            subtree: true
        });
    }

    // ✅ Optional: if you render ApexCharts, recalc after they mount/update
    // Example if you have chart instances:
    // chart.on('mounted', recalcSimpleBars);
    // chart.on('updated', recalcSimpleBars);
</script>
<script>
    $(document).ready(function() {
        // ✅ Independent Accordions
        $(".accordion-collapse").removeAttr("data-bs-parent");
        // ✅ Smooth scroll when a domain or goal is expanded


    });
</script>
<?= $this->endSection() ?>
