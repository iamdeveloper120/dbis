<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<?php
$fmtDate = static function ($v) {
    return !empty($v) ? esc(app_date($v)) : '';
};
$effectiveTeachingProcedure = $effective_teaching_procedure ?? [];
if (!is_array($effectiveTeachingProcedure)) {
    $effectiveTeachingProcedure = [];
}
?>

<style>
    #accordionBordered .form-label {
        color: #000;
    }
</style>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0"><?= esc($page_title) ?></h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url('clients') ?>">Clients</a></li>
            <li class="breadcrumb-item active"><?= esc($client->internal_mrn) ?></li>
        </ol>
    </div>
</div>

<div class="card" id="orderList">
    <div class="card-header border-0">
        <div class="row align-items-center gy-3">
            <div class="col-sm">
                <h5 class="card-title mb-0"><?= esc($client->internal_mrn) ?></h5>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex gap-1 flex-wrap">
                    <a href="<?= base_url('clients') ?>" class="btn btn-soft-primary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accordions Bordered -->
<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="accordionBordered">

    <!-- 0️⃣ Basic Details -->
    <div class="accordion-item material-shadow">
        <h2 class="accordion-header" id="accordionborderedBasic">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedBasic" aria-expanded="true" aria-controls="accor_borderedBasic">
                Basic Information
            </button>
        </h2>
        <div id="accor_borderedBasic" class="accordion-collapse collapse show"
            aria-labelledby="accordionborderedBasic" data-bs-parent="#accordionBordered">
            <div class="accordion-body">

                <!-- ✅ Basic Details Form -->
                <form id="form_basic_details">
                    <input type="hidden" name="id" value="<?= esc($client->id) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Client No.</label>
                            <input type="text" class="form-control" name="internal_mrn" value="<?= esc($client->internal_mrn) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="fname" value="<?= esc($client->first_name) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="lname" value="<?= esc($client->last_name) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">List of Programs</label>
                        <textarea class="form-control" name="description" rows="2"><?= esc($client->description) ?></textarea>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-basic">
                            <i class="ri-save-3-line me-1"></i> Save Basic Details
                        </button>
                    </div>
                </form>
                <!-- ✅ End Basic Details Form -->

            </div>
        </div>
    </div>

    <!-- 1️⃣ Client Details -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="accordionborderedClient">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedClient" aria-expanded="false" aria-controls="accor_borderedClient">
                Client Details
            </button>
        </h2>
        <div id="accor_borderedClient" class="accordion-collapse collapse"
            aria-labelledby="accordionborderedClient" data-bs-parent="#accordionBordered">
            <div class="accordion-body">
                <!-- ✅ Client Details Form -->
                <form id="form_client_details">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth </label>
                            <input type="text" class="form-control flatpickr" name="date_of_birth"
                                value="<?= $fmtDate($info['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= esc($info['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Primary Diagnosis</label>
                            <input type="text" class="form-control" name="primary_diagnosis" value="<?= esc($info['primary_diagnosis'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Primary Diagnosis</label>
                            <input type="text" class="form-control flatpickr" name="date_primary_diagnosis"
                                value="<?= esc($fmtDate($info['date_primary_diagnosis'] ?? null)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age at Primary Diagnosis</label>
                            <input type="number" class="form-control" name="age_primary_diagnosis" value="<?= esc($info['age_primary_diagnosis'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Secondary Diagnosis</label>
                            <input type="text" class="form-control" name="secondary_diagnosis" value="<?= esc($info['secondary_diagnosis'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Secondary Diagnosis</label>
                            <input type="text" class="form-control flatpickr" name="date_secondary_diagnosis"
                                value="<?= esc($fmtDate($info['date_secondary_diagnosis'] ?? null)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age at Secondary Diagnosis</label>
                            <input type="number" class="form-control" name="age_secondary_diagnosis" value="<?= esc($info['age_secondary_diagnosis'] ?? '') ?>">
                        </div>
                    </div>
                    <hr>
                    <!-- Other Diagnoses Table -->
                    <div class="mb-3">
                        <!-- Other Diagnoses -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Other Diagnoses</h6>
                            <button type="button" class="btn btn-soft-secondary btn-sm" id="add_other_diag">
                                <i class="ri-add-line me-1"></i> Add Diagnosis
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle table-bordered" id="table_other_diagnoses">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:45%">Diagnosis</th>
                                        <th style="width:20%">Date</th>
                                        <th style="width:15%">Age</th>
                                        <th style="width:10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($otherDiagnoses)): foreach ($otherDiagnoses as $od): ?>
                                            <tr>
                                                <td><input type="text" class="form-control form-control-sm" name="diagnosis_name[]" value="<?= esc($od['diagnosis_name']) ?>"></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm flatpickr" name="diagnosis_date[]"
                                                        value="<?= esc($fmtDate($od['diagnosis_date'] ?? null)) ?>">
                                                </td>
                                                <td><input type="number" class="form-control form-control-sm" name="diagnosis_age[]" value="<?= esc($od['diagnosis_age']) ?>"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-light btn-sm btn-remove-row" title="Remove">
                                                        <i class="ri-delete-bin-6-line text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php endforeach;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-client-details">
                            <i class="ri-save-3-line me-1"></i> Save Client Details
                        </button>
                    </div>
                </form>
                <!-- ✅ End Client Details Form -->

            </div>
        </div>
    </div>

    <!-- 2️⃣ Parent / Legal Guardian Information -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="accordionborderedGuardians">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedGuardians" aria-expanded="false" aria-controls="accor_borderedGuardians">
                Parent / Legal Guardian Information
            </button>
        </h2>
        <div id="accor_borderedGuardians" class="accordion-collapse collapse"
            aria-labelledby="accordionborderedGuardians" data-bs-parent="#accordionBordered">
            <div class="accordion-body">

                <!-- ✅ Guardian Info Form -->
                <form id="form_guardians">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        
                        <button type="button" class="btn btn-soft-secondary btn-sm" id="add_guardian">
                            <i class="ri-add-line me-1"></i> Add Parent/Guardian
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-bordered" id="table_guardians">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%">Name</th>
                                    <th style="width:30%">Address</th>
                                    <th style="width:20%">Telephone</th>
                                    <th style="width:20%">Email</th>
                                    <th style="width:5%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($guardians)): foreach ($guardians as $g): ?>
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" name="name[]" value="<?= esc($g['name']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="address[]" value="<?= esc($g['address']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="telephone[]" value="<?= esc($g['telephone']) ?>"></td>
                                            <td><input type="email" class="form-control form-control-sm" name="email[]" value="<?= esc($g['email']) ?>"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-light btn-sm btn-remove-row" title="Remove">
                                                    <i class="ri-delete-bin-6-line text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-guardians">
                            <i class="ri-save-3-line me-1"></i> Save Guardian Info
                        </button>
                    </div>
                </form>
                <!-- ✅ End Guardian Info Form -->

            </div>
        </div>
    </div>

    <!-- 3️⃣ Others Living with the Child -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="accordionborderedHousehold">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedHousehold" aria-expanded="false" aria-controls="accor_borderedHousehold">
                Others Living with the Child
            </button>
        </h2>
        <div id="accor_borderedHousehold" class="accordion-collapse collapse"
            aria-labelledby="accordionborderedHousehold" data-bs-parent="#accordionBordered">
            <div class="accordion-body">

                <!-- ✅ Household Members Form -->
                <form id="form_household">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        
                        <button type="button" class="btn btn-soft-secondary btn-sm" id="add_household">
                            <i class="ri-add-line me-1"></i> Add Member
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-bordered" id="table_household">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Name</th>
                                    <th style="width:20%">Age</th>
                                    <th style="width:25%">Relationship</th>
                                    <th style="width:10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($household)): foreach ($household as $row): ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="household[id][]" value="<?= esc($row['id']) ?>">
                                                <input type="text" class="form-control form-control-sm" name="household[name][]" value="<?= esc($row['name']) ?>">
                                            </td>
                                            <td><input type="number" class="form-control form-control-sm" name="household[age][]" value="<?= esc($row['age']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="household[relationship][]" value="<?= esc($row['relationship']) ?>"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-light btn-sm btn-remove-row"><i class="ri-delete-bin-6-line text-danger"></i></button>
                                            </td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>

                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-household">
                            <i class="ri-save-3-line me-1"></i> Save Household Info
                        </button>
                    </div>
                </form>
                <!-- ✅ End Household Members Form -->

            </div>
        </div>
    </div>

    <!-- 4️⃣ Medical Information -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="accordionborderedMedical">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedMedical" aria-expanded="false" aria-controls="accor_borderedMedical">
                Medical Information
            </button>
        </h2>
        <div id="accor_borderedMedical" class="accordion-collapse collapse"
            aria-labelledby="accordionborderedMedical" data-bs-parent="#accordionBordered">
            <div class="accordion-body">

                <!-- ✅ Medical Information Form -->
                <form id="form_medical_info">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Prescribing Doctor</label>
                            <input type="text" class="form-control" name="prescribing_doctor"
                                value="<?= esc($medical['prescribing_doctor'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Medical Provider</label>
                            <input type="text" class="form-control" name="current_medical_provider"
                                value="<?= esc($medical['current_medical_provider'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Medical Conditions</label>
                        <textarea class="form-control" name="medical_conditions" rows="2"><?= esc($medical['medical_conditions'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Previous Medications</label>
                        <textarea class="form-control" name="previous_medications" rows="2"><?= esc($medical['previous_medications'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Allergies</label>
                        <textarea class="form-control" name="allergies" rows="2"><?= esc($medical['allergies'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sleeping Habits</label>
                            <textarea class="form-control" name="sleeping_habits" rows="1"><?= esc($medical['sleeping_habits'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Eating Habits</label>
                            <textarea class="form-control" name="eating_habits" rows="1"><?= esc($medical['eating_habits'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-medical">
                            <i class="ri-save-3-line me-1"></i> Save Medical Info
                        </button>
                    </div>
                </form>
                <!-- ✅ End Medical Information Form -->

            </div>
        </div>
    </div>
    <!-- 5️⃣ Medications & Supplements -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="accordionborderedMedications">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#accor_borderedMedications" aria-expanded="false" aria-controls="accor_borderedMedications">
                Medications & Supplements
            </button>
        </h2>
        <div id="accor_borderedMedications" class="accordion-collapse collapse"
            aria-labelledby="accordionborderedMedications" data-bs-parent="#accordionBordered">
            <div class="accordion-body">

                <!-- ✅ Medications Form -->
                <form id="form_medications">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                    <div class="d-flex justify-content-between align-items-center mb-2"> 
                        <button type="button" class="btn btn-soft-secondary btn-sm" id="add_medication_row">
                            <i class="ri-add-line me-1"></i> Add Medication/Supplement
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-bordered" id="table_medications">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:15%">Category</th>
                                    <th style="width:20%">Name</th>
                                    <th style="width:15%">Dosage</th>
                                    <th style="width:15%">Frequency</th>
                                    <th style="width:25%">Prescribed For</th>
                                    <th style="width:10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($medications)): foreach ($medications as $m): ?>
                                        <tr>
                                            <td>
                                                <select class="form-select form-select-sm" name="medications[category][]">
                                                    <option <?= $m['category'] == 'Medication' ? 'selected' : '' ?>>Medication</option>
                                                    <option <?= $m['category'] == 'Supplement' ? 'selected' : '' ?>>Supplement</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" name="medications[name][]" value="<?= esc($m['name']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="medications[dosage][]" value="<?= esc($m['dosage']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="medications[frequency][]" value="<?= esc($m['frequency']) ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="medications[prescribed_for][]" value="<?= esc($m['prescribed_for']) ?>"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-light btn-sm btn-remove-row" title="Remove">
                                                    <i class="ri-delete-bin-6-line text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary btn-save-medications">
                            <i class="ri-save-3-line me-1"></i> Save Medications
                        </button>
                    </div>
                </form>
                <!-- ✅ End Medications Form -->

            </div>
        </div>
    </div>
    <!-- 6️⃣ Educational Services -->
    <div class="accordion-item mt-2 material-shadow">
        <h2 class="accordion-header" id="headingEdu">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseEdu"
                aria-expanded="false" aria-controls="collapseEdu">
                Educational Services
            </button>
        </h2>
        <div id="collapseEdu" class="accordion-collapse collapse" aria-labelledby="headingEdu">
            <div class="accordion-body">
                <form id="form_education">
                    <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3 form-check form-switch">
                            <!-- hidden ensures 0 is sent when unchecked -->
                            <input type="hidden" name="one_to_one_support" value="0">
                            <input class="form-check-input" type="checkbox" id="one_to_one_support"
                                name="one_to_one_support" value="1"
                                <?= !empty($education['one_to_one_support']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="one_to_one_support">1:1 Support</label>
                        </div>
                        <div class="col-md-3 form-check form-switch">
                            <input type="hidden" name="home_program" value="0">
                            <input class="form-check-input" type="checkbox" id="home_program"
                                name="home_program" value="1"
                                <?= !empty($education['home_program']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="home_program">Home Program</label>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Educational Setting</label>
                            <select class="form-select" name="educational_setting">
                                <option value="Home" <?= ($education['educational_setting'] ?? '') === 'Home'   ? 'selected' : '' ?>>Home</option>
                                <option value="School" <?= ($education['educational_setting'] ?? '') === 'School' ? 'selected' : '' ?>>School</option>
                                <option value="Both" <?= ($education['educational_setting'] ?? '') === 'Both'   ? 'selected' : '' ?>>Both</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">School Name</label>
                            <input type="text" class="form-control" name="school_name"
                                value="<?= esc($education['school_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">School Type</label>
                            <select class="form-select" name="school_type">
                                <option value="Mainstream" <?= ($education['school_type'] ?? '') === 'Mainstream' ? 'selected' : '' ?>>Mainstream</option>
                                <option value="Special Education" <?= ($education['school_type'] ?? '') === 'Special Education' ? 'selected' : '' ?>>Special Education</option>
                            </select>
                        </div>
                    </div>



                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Date Enrolled</label>
                            <input type="text" class="form-control flatpickr" name="date_enrolled"
                                value="<?= esc($fmtDate($education['date_enrolled'] ?? null)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Weekly Hours</label>
                            <input type="number" class="form-control" name="weekly_hours"
                                min="0" step="0.25"
                                value="<?= esc($education['weekly_hours'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Home Program Start Date</label>
                            <input type="text" class="form-control flatpickr" name="home_program_start_date"
                                value="<?= esc($fmtDate($education['home_program_start_date'] ?? null)) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attendance Schedule</label>
                        <textarea class="form-control" name="attendance_schedule" rows="2"><?= esc($education['attendance_schedule'] ?? '') ?></textarea>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-save-education"
                            data-url="<?= base_url('clients/save-education') ?>">
                            <i class="ri-save-line me-1"></i>Save Education Info
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php if (auth()->user()->can('clients.update')): ?>
        <!-- 7️⃣ Key Information -->
        <div class="accordion-item mt-2 material-shadow">
            <h2 class="accordion-header" id="headingEffectiveTeachingProcedures">
                <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseEffectiveTeachingProcedures"
                    aria-expanded="false" aria-controls="collapseEffectiveTeachingProcedures">
                   Key Information
                </button>
            </h2>
            <div id="collapseEffectiveTeachingProcedures" class="accordion-collapse collapse" aria-labelledby="headingEffectiveTeachingProcedures">
                <div class="accordion-body">
                    <form id="form_effective_teaching_procedures">
                        <input type="hidden" name="client_id" value="<?= esc($client->id) ?>">

                        <div class="mb-3 d-none">
                            <label class="form-label">Competing positive reinforcers</label>
                            <small class="text-muted d-block mb-1">(what positive reinforcers will compete with interfering behaviours)</small>
                            <textarea class="form-control" name="competing_positive_reinforcers" rows="2"><?= esc($effectiveTeachingProcedure['competing_positive_reinforcers'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Mix and vary tasks</label>
                            <small class="text-muted d-block mb-1">(Which skills?)</small>
                            <textarea class="form-control" name="mix_and_vary_tasks" rows="2"><?= esc($effectiveTeachingProcedure['mix_and_vary_tasks'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Errorless teaching procedures</label>
                            <small class="text-muted d-block mb-1">(What errorless teaching procedures will be used)</small>
                            <textarea class="form-control" name="errorless_teaching_procedures" rows="2"><?= esc($effectiveTeachingProcedure['errorless_teaching_procedures'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Percentage of easy to hard tasks</label>
                            <textarea class="form-control" name="easy_to_hard_percentage" rows="2"><?= esc($effectiveTeachingProcedure['easy_to_hard_percentage'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Easy responses that can be faded in at start of instruction:</label>
                            <textarea class="form-control" name="easy_responses_fade_start" rows="2"><?= esc($effectiveTeachingProcedure['easy_responses_fade_start'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Schedule of reinforcement</label>
                            <textarea class="form-control" name="schedule_of_reinforcement" rows="2"><?= esc($effectiveTeachingProcedure['schedule_of_reinforcement'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">General comments</label>
                            <textarea class="form-control" name="general_comment" rows="2"><?= esc($effectiveTeachingProcedure['general_comment'] ?? '') ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-primary btn-save-effective-teaching-procedures">
                                <i class="ri-save-line me-1"></i>Save Key Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>


</div> <!-- End Accordion -->
<br>
<?= $this->endSection() ?>


<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {

        // ✅ CSRF Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "<?= csrf_hash() ?>"
            }
        });
        // ✅ Independent Accordions
        $(".accordion-collapse").removeAttr("data-bs-parent");
        /**********************************************
         * Global Flatpickr Init for All Date Inputs
         **********************************************/

        function initFlatpickr(scope = document) {
            $(scope).find('input.flatpickr').each(function() {
                if (this._flatpickr) return;
                flatpickr(this, {
                    dateFormat: "<?= CC_DATE_FORMAT ?>",
                    weekNumbers: true,
                    maxDate: "today",
                });
            });
        }


        // Initialize once on page load
        initFlatpickr();




        // ✅ Save Basic Details AJAX
        $(".btn-save-basic").on("click", function() {
            const btn = $(this);
            const form = $("#form_basic_details");
            const url = "<?= base_url('clients/update') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    if (res.status === "success") {

                        showAlert(res.statusText, res.message, res.status);
                    } else {
                        showAlert(res.statusText, res.message, res.status);
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Basic Details');
                }
            });
        });
        // =============================
        // Client Details Section
        // =============================

        // Reuse when dynamically adding new rows
        $(document).on('click', '#add_other_diag', function() {
            const newRow = `
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="diagnosis_name[]"></td>
            <td><input type="text" class="form-control form-control-sm flatpickr" name="diagnosis_date[]"></td>
            <td><input type="number" class="form-control form-control-sm" name="diagnosis_age[]"></td>
            <td class="text-center">
                <button type="button" class="btn btn-light btn-sm btn-remove-row">
                    <i class="ri-delete-bin-6-line text-danger"></i>
                </button>
            </td>
        </tr>
    `;
            $("#table_other_diagnoses tbody").append(newRow);
            initFlatpickr($("#table_other_diagnoses tbody tr:last"));
        });

        // Remove row handler (reusable)
        $(document).on("click", ".btn-remove-row", function() {
            $(this).closest("tr").remove();
        });

        // Save Client Details
        $(".btn-save-client-details").on("click", function() {
            const btn = $(this);
            const form = $("#form_client_details");
            const url = "<?= base_url('clients/save-info') ?>"; // adjust endpoint as per your controller

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Client Details');
                }
            });
        });

        /*******************************
         * Guardian Section
         *******************************/
        $("#add_guardian").on("click", function() {
            $("#table_guardians tbody").append(`
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="name[]"></td>
            <td><input type="text" class="form-control form-control-sm" name="address[]"></td>
            <td><input type="text" class="form-control form-control-sm" name="telephone[]"></td>
            <td><input type="email" class="form-control form-control-sm" name="email[]"></td>
            <td class="text-center">
                <button type="button" class="btn btn-light btn-sm btn-remove-row">
                    <i class="ri-delete-bin-6-line text-danger"></i>
                </button>
            </td>
        </tr>
    `);
        });

        // Remove guardian row
        $(document).on("click", ".btn-remove-row", function() {
            $(this).closest("tr").remove();
        });

        // Save guardians via AJAX
        $(".btn-save-guardians").on("click", function() {
            const btn = $(this);
            const form = $("#form_guardians");
            const url = "<?= base_url('clients/save-guardians') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Guardian Info');
                }
            });
        });

        /*************************************** */

        /*******************************
         * Household Section
         *******************************/
        $("#add_household").on("click", function() {
            $("#table_household tbody").append(`
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="household[name][]"></td>
            <td><input type="number" class="form-control form-control-sm" name="household[age][]"></td>
            <td><input type="text" class="form-control form-control-sm" name="household[relationship][]"></td>
            <td class="text-center">
                <button type="button" class="btn btn-light btn-sm btn-remove-row">
                    <i class="ri-delete-bin-6-line text-danger"></i>
                </button>
            </td>
        </tr>
    `);
        });

        // Remove row handler (reuse existing)
        $(document).on("click", ".btn-remove-row", function() {
            $(this).closest("tr").remove();
        });

        // Save household members via AJAX
        $(".btn-save-household").on("click", function() {
            const btn = $(this);
            const form = $("#form_household");
            const url = "<?= base_url('clients/save-household') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Household Info');
                }
            });
        });

        /*************************************** */
        /*******************************
         * Medical Information Section
         *******************************/
        $(".btn-save-medical").on("click", function() {
            const btn = $(this);
            const form = $("#form_medical_info");
            const url = "<?= base_url('clients/save-medical') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Medical Info');
                }
            });
        });


        /*************************************** */
        /*******************************
         * Medications & Supplements
         *******************************/
        $("#add_medication_row").on("click", function() {
            $("#table_medications tbody").append(`
        <tr>
            <td>
                <select class="form-select form-select-sm" name="medications[category][]">
                    <option>Medication</option>
                    <option>Supplement</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="medications[name][]"></td>
            <td><input type="text" class="form-control form-control-sm" name="medications[dosage][]"></td>
            <td><input type="text" class="form-control form-control-sm" name="medications[frequency][]"></td>
            <td><input type="text" class="form-control form-control-sm" name="medications[prescribed_for][]"></td>
            <td class="text-center">
                <button type="button" class="btn btn-light btn-sm btn-remove-row">
                    <i class="ri-delete-bin-6-line text-danger"></i>
                </button>
            </td>
        </tr>
    `);
        });

        // Reuse existing row removal handler
        $(document).on("click", ".btn-remove-row", function() {
            $(this).closest("tr").remove();
        });

        // Save Medications via AJAX
        $(".btn-save-medications").on("click", function() {
            const btn = $(this);
            const form = $("#form_medications");
            const url = "<?= base_url('clients/save-medications') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="ri-save-3-line me-1"></i> Save Medications');
                }
            });
        });

        /*******************************
         * Educational Services Section education
         *******************************/
        // Save Education (keeps same pattern as other sections)
        $(".btn-save-education").on("click", function() {
            const btn = $(this);
            const form = $("#form_education");
            const url = "<?= base_url('clients/save-education') ?>";
            const labelHtml = '<i class="ri-save-line me-1"></i>Save Education Info';

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true)
                        .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html(labelHtml);
                }
            });
        });

        /*******************************
         * Key Information Section
         *******************************/
        $(".btn-save-effective-teaching-procedures").on("click", function() {
            const btn = $(this);
            const form = $("#form_effective_teaching_procedures");
            const url = "<?= base_url('clients/save-effective-teaching-procedures') ?>";
            const labelHtml = '<i class="ri-save-line me-1"></i>Save Key Information';

            $.ajax({
                url: url,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    btn.prop("disabled", true)
                        .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
                },
                success: function(res) {
                    showAlert(res.statusText, res.message, res.status);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: xhr.responseText
                    });
                },
                complete: function() {
                    btn.prop("disabled", false).html(labelHtml);
                }
            });
        });


        /*********ٌEnd of Ready******** */
    });
</script>
<?= $this->endSection() ?>
