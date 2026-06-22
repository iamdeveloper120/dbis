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
    <!-- Profile Header Card --> 
    <!-- Accordions Bordered (Read-only) -->
    <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="accordionBordered">

        <!-- 1️⃣ Client Details -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header" id="accordionborderedClient">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accor_borderedClient" aria-expanded="true" aria-controls="accor_borderedClient">
                    Client information
                </button>
            </h2>
            <div id="accor_borderedClient" class="accordion-collapse collapse show">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">

                    <div class="row gy-3 gx-4">
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Client No.</small>
                                <span class="text-black fs-6"><?= esc($client->internal_mrn ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">First Name</small>
                                <span class="text-black fs-6"><?= esc($client->first_name ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Last Name</small>
                                <span class="text-black fs-6"><?= esc($client->last_name ?? '-') ?></span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="row gy-3 gx-4">
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Date of Birth</small>
                                <span class="text-black"><?= $fmtDate($info['date_of_birth'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Address</small>
                                <span class="text-black"><?= nl2br(esc($info['address'] ?? '-')) ?></span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="fw-semibold text-primary mb-3 mt-2"><i class="ri-hospital-line me-1"></i> Diagnosis Details</h6>

                    <div class="row gy-3 gx-4">
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Primary Diagnosis</small>
                                <span class="text-black"><?= esc($info['primary_diagnosis'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Date of Primary Diagnosis</small>
                                <span class="text-black"><?= $fmtDate($info['date_primary_diagnosis'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Age at Primary Diagnosis</small>
                                <span class="text-black"><?= esc($info['age_primary_diagnosis'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-3 gx-4 mt-1">
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Secondary Diagnosis</small>
                                <span class="text-black"><?= esc($info['secondary_diagnosis'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Date of Secondary Diagnosis</small>
                                <span class="text-black"><?= $fmtDate($info['date_secondary_diagnosis'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <small class="text-black fw-semibold">Age at Secondary Diagnosis</small>
                                <span class="text-black"><?= esc($info['age_secondary_diagnosis'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($otherDiagnoses)): ?>
                        <hr class="my-4">
                        <h6 class="fw-semibold text-primary mb-2"><i class="ri-file-list-3-line me-1"></i> Other Diagnoses</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Diagnosis</th>
                                        <th>Date</th>
                                        <th>Age</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($otherDiagnoses as $od): ?>
                                        <tr>
                                            <td><?= esc($od['diagnosis_name']) ?></td>
                                            <td><?= $fmtDate($od['diagnosis_date'] ?? '') ?></td>
                                            <td><?= esc($od['diagnosis_age']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>

        <!-- 2️⃣ Guardian Info -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header" id="accordionborderedGuardians">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accor_borderedGuardians" aria-expanded="false">
                    Parent / Legal Guardian Information
                </button>
            </h2>
            <div id="accor_borderedGuardians" class="accordion-collapse collapse">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">

                    <div class="table-responsive">
                        <table class="table align-middle table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr class="text-black small">
                                    <th scope="col">Name</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Telephone</th>
                                    <th scope="col">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($guardians)): ?>
                                    <?php foreach ($guardians as $g): ?>
                                        <tr>
                                            <td><?= esc($g['name']) ?></td>
                                            <td><?= esc($g['address']) ?></td>
                                            <td><?= esc($g['telephone']) ?></td>
                                            <td><?= esc($g['email']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-black fst-italic py-3">
                                            No guardian information available.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3️⃣ Others Living with the Child -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header" id="accordionborderedHousehold">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accor_borderedHousehold" aria-expanded="false">
                    Others Living with the Child
                </button>
            </h2>
            <div id="accor_borderedHousehold" class="accordion-collapse collapse">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">


                    <div class="table-responsive">
                        <table class="table align-middle table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr class="text-black small">
                                    <th scope="col">Name</th>
                                    <th scope="col" style="width:120px;">Age</th>
                                    <th scope="col" style="width:200px;">Relationship</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($household)): ?>
                                    <?php foreach ($household as $h): ?>
                                        <tr>
                                            <td class="text-black fw-medium"><?= esc($h['name']) ?></td>
                                            <td>
                                                <?php if (is_numeric($h['age']) && $h['age'] !== ''): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border">
                                                        <?= esc($h['age']) ?> yrs
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-black">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($h['relationship'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary border">
                                                        <?= esc($h['relationship']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-black">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-black fst-italic py-3">
                                            No household members recorded.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>


        <!-- 4️⃣ Medical Information -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accor_borderedMedical">
                    Medical Information
                </button>
            </h2>
            <div id="accor_borderedMedical" class="accordion-collapse collapse">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">

                    <!-- Header grid -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Prescribing Doctor</div>
                                <div class="text-black fw-medium">
                                    <?= esc($medical['prescribing_doctor'] ?? '-') ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Current Medical Provider</div>
                                <div class="text-black fw-medium">
                                    <?= esc($medical['current_medical_provider'] ?? '-') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Long text blocks -->
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-heart-pulse-line me-2 text-primary"></i>
                                    <h6 class="mb-0">Medical Conditions</h6>
                                </div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($medical['medical_conditions'] ?? '-')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-capsule-line me-2 text-primary"></i>
                                    <h6 class="mb-0">Previous Medications</h6>
                                </div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($medical['previous_medications'] ?? '-')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-alert-line me-2 text-primary"></i>
                                    <h6 class="mb-0">Allergies</h6>
                                </div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($medical['allergies'] ?? '-')) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Habits -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Sleeping Habits</div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($medical['sleeping_habits'] ?? '-')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Eating Habits</div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($medical['eating_habits'] ?? '-')) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- /accordion-body -->
            </div>
        </div>

        <!-- 5️⃣ Medications -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accor_borderedMedications">
                    Medications & Supplements
                </button>
            </h2>
            <div id="accor_borderedMedications" class="accordion-collapse collapse">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">

                    <div class="table-responsive">
                        <table class="table align-middle table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr class="text-black small">
                                    <th scope="col">Category</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Dosage</th>
                                    <th scope="col">Frequency</th>
                                    <th scope="col">Prescribed For</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($medications)): ?>
                                    <?php foreach ($medications as $m): ?>
                                        <tr>
                                            <td class="text-black fw-medium"><?= esc($m['category']) ?></td>
                                            <td class="text-black"><?= esc($m['name']) ?></td>
                                            <td>
                                                <?php if (!empty($m['dosage'])): ?>
                                                    <span class="badge bg-info-subtle text-info border"><?= esc($m['dosage']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-black">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($m['frequency'])): ?>
                                                    <span class="badge bg-success-subtle text-success border"><?= esc($m['frequency']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-black">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-black"><?= esc($m['prescribed_for']) ?: '<span class="text-black">-</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-black fst-italic py-3">
                                            No medication or supplement information available.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>


        <!-- 6️⃣ Educational Services -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseEdu">
                    Educational Services
                </button>
            </h2>
            <div id="collapseEdu" class="accordion-collapse collapse">
                <div class="accordion-body bg-light-subtle rounded-3 p-3">


                    <!-- Row 1: Key Flags -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">1:1 Support</div>
                                <?php if (!empty($education['one_to_one_support'])): ?>
                                    <span class="badge bg-success-subtle text-success border">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border">No</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Home Program</div>
                                <?php if (!empty($education['home_program'])): ?>
                                    <span class="badge bg-success-subtle text-success border">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border">No</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Educational Setting</div>
                                <div class="text-black fw-medium"><?= esc($education['educational_setting'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">School Type</div>
                                <div class="text-black fw-medium"><?= esc($education['school_type'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Enrollment Info -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">School Name</div>
                                <div class="text-black fw-medium"><?= esc($education['school_name'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Date Enrolled</div>
                                <div class="text-black fw-medium"><?= $fmtDate($education['date_enrolled'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Home Program Start</div>
                                <div class="text-black fw-medium"><?= $fmtDate($education['home_program_start_date'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Schedule -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="text-black small mb-1">Weekly Hours</div>
                                <?php if (!empty($education['weekly_hours'])): ?>
                                    <span class="badge bg-info-subtle text-info border">
                                        <?= esc($education['weekly_hours']) ?> hrs
                                    </span>
                                <?php else: ?>
                                    <span class="text-black">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="p-3 border rounded-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-calendar-line me-2 text-primary"></i>
                                    <h6 class="mb-0">Attendance Schedule</h6>
                                </div>
                                <div class="text-black text-break">
                                    <?= nl2br(esc($education['attendance_schedule'] ?? '-')) ?>
                                </div>
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
