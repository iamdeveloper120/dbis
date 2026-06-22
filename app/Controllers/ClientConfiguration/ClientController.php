<?php

namespace App\Controllers\ClientConfiguration;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;
use App\Entities\ClientConfiguration\Client;


class ClientController extends AdminController
{

    public function index()
    {
        $clientModel = model(ClientModel::class);
        $clients = $clientModel->findAll();

        $client_list = [];
        if (isset($clients) && count($clients)) {
            foreach ($clients as $data) {
                $client_list[] = $this->format_row_data($data);
            }
        }


        $this->page_title = 'Client Management';
        return  view('ClientConfiguration/Clients/index.php', ['clients' => $client_list, 'page_title' => $this->page_title]);
    }
    /******************************************************************** */
    /*  CLIENT BACKGROUND DETAIL VIEW  */
    public function detail_view($id)
    {
        $id = decodeValue($id);
        $clientModel = model(\App\Models\ClientConfiguration\ClientModel::class);
        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/clients')->with('error', 'Client not found.');
        }

        // Related models
        $infoModel       = model(\App\Models\ClientConfiguration\ClientInfoModel::class);
        $otherDiagModel  = model(\App\Models\ClientConfiguration\ClientOtherDiagnosisModel::class);
        $guardianModel   = model(\App\Models\ClientConfiguration\ClientGuardianModel::class);
        $householdModel  = model(\App\Models\ClientConfiguration\ClientHouseholdModel::class);
        $medicalModel    = model(\App\Models\ClientConfiguration\ClientMedicalModel::class);
        $medicationModel = model(\App\Models\ClientConfiguration\ClientMedicationModel::class);
        $educationModel  = model(\App\Models\ClientConfiguration\ClientEducationModel::class);
        $effectiveTeachingProcedureModel = model(\App\Models\ClientConfiguration\ClientEffectiveTeachingProcedureModel::class);

        $info       = $infoModel->where('client_id', $id)->first();
        $education  = $educationModel->where('client_id', $id)->first();
        $effectiveTeachingProcedure = $effectiveTeachingProcedureModel->where('client_id', $id)->first();
        $otherDiags = $otherDiagModel->where('client_id', $id)->findAll();

       

        // ✅ Combine for view
        $data = [
            'client'          => $client,
            'info'            => $info,
            'otherDiagnoses'  => $otherDiags,
            'guardians'       => $guardianModel->where('client_id', $id)->findAll(),
            'household'       => $householdModel->where('client_id', $id)->findAll(),
            'medical'         => $medicalModel->where('client_id', $id)->first(),
            'medications'     => $medicationModel->where('client_id', $id)->findAll(),
            'education'       => $education,
            'effective_teaching_procedure' => $effectiveTeachingProcedure,
            'page_title'      => 'Client Background Information',
        ];

        return view('ClientConfiguration/Clients/detail_view', $data);
    }


    /******************************************************************** */
    public function save()
    {
        $response = [];
        $clientModel = new ClientModel();
        $client = new Client();

        $rules =    [
            'internal_mrn' => [
                'label'  => 'Internal Client No',
                'rules'  => 'required|min_length[5]|is_unique[clients.internal_mrn]',
                'errors' => [
                    'required' => '{field} Required',
                    'alpha_numeric' => '{field} except only Alpha Numeric values',
                    'min_length' => '{field} min lenth is 5',
                    'is_unique' => '{field} must be unique',
                ],
            ],
            'first_name' => [
                'label'  => 'First Name',
                'rules'  => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} minimun length is 3',
                    'max_length' => '{field} maximum length is 50',
                ],
            ],
            'last_name' => [
                'label'  => 'Last Name',
                'rules'  => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} minimun length is 3',
                    'max_length' => '{field} maximum length is 50',
                ],
            ],
            'description' => [
                'label'  => 'Last Name',
                'rules'  => 'permit_empty'
            ],
        ];


        $data = [
            'internal_mrn' => $this->request->getPost('internal_mrn'),
            'first_name'   => $this->request->getPost('fname'),
            'last_name'   => $this->request->getPost('lname'),
            'description'   => $this->request->getPost('description'),
            'created_by'   => auth()->user()->id,
            'updated_by'   => NULL,
            'updated_at'   => NULL,
        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Validation Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {

            $client->fill($data);
            $clientModel->save($client);
            $client = $clientModel->find($clientModel->getInsertID());
            $res = $this->format_row_data($client);
            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record created successfully',
                'data' => $res
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function get_selected()
    {

        $response = [];
        $clientModel = new ClientModel();
        $rules =    [
            'id' => [
                'label'  => 'Reocrd ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];
        $data = [
            'id'   => $this->request->getPost('id'),

        ];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $client = $clientModel->find($data['id']);

            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => '',
                'data' => $client
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function update_client()
    {

        $response = [];
        $clientModel = new ClientModel();
        $client = new Client();
        $rules =    [
            'id' => [
                'label'  => 'Reocrd ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'internal_mrn' => [
                'label'  => 'Internal Client No',
                'rules'  => 'required|min_length[5]|is_unique[clients.internal_mrn,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'alpha_numeric' => '{field} except only Alpha Numeric values',
                    'min_length' => '{field} min lenth is 5',
                    'is_unique' => '{field} must be unique',
                ],
            ],
            'first_name' => [
                'label'  => 'First Name',
                'rules'  => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} minimun length is 3',
                    'max_length' => '{field} maximum length is 50',
                ],
            ],
            'last_name' => [
                'label'  => 'Last Name',
                'rules'  => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} minimun length is 3',
                    'max_length' => '{field} maximum length is 50',
                ],
            ],
            'description' => [
                'label'  => 'Last Name',
                'rules'  => 'permit_empty'
            ],
        ];
        $data = [
            'id'   => $this->request->getPost('id'),
            'internal_mrn' => $this->request->getPost('internal_mrn'),
            'first_name'   => $this->request->getPost('fname'),
            'last_name'   => $this->request->getPost('lname'),
            'description'   => $this->request->getPost('description'),
            'updated_by'   => auth()->user()->id,

        ];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $client->fill($data);
            $clientModel->save($client);
            $client = $clientModel->find($data['id']);
            $res = $this->format_row_data($client);
            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record updated Successfully',
                'data' => $res
            ];
        }
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function change_status()
    {

        $response = [];
        $clientModel = new ClientModel();
        $client = new Client();
        $rules =    [
            'id' => [
                'label'  => 'Reocrd ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ]
            ],
            'status' => [
                'label'  => 'Status',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        $data = [
            'id'   => $this->request->getPost('id'),
            'status' =>  $this->request->getPost('status'),
            'updated_by'   => auth()->user()->id

        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $client->fill($data);
            $clientModel->save($client);
            if ($data['status'] == 0) {
                $clientModel->detach_client_from_all_user($data['id']);
            }
            $client = $clientModel->find($data['id']);
            $res = $this->format_row_data($client);
            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => '',
                'data' => $res
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function delete()
    {

        if (!auth()->user()->can('clients.delete')) {
            $response = [
                'status' => 'error',
                'statusText' => '',
                'message' => 'Not Authorized',
                'data' => ''
            ];
            return $this->response->setJSON($response);
        }

        $data = [
            'id' => $this->request->getPost('id'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'Session ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {

            $clientModel = new ClientModel();
            $isExists = $clientModel->isClientInUse($data['id']);
            if ($isExists) {
                $response = [
                    'status' => 'error',
                    'statusText' => 'Error',
                    'message' => 'Client exists in the client clinical data. First delete entries from daily, weekly, phase line and target dates data belong to user. You need to deactivate client first',
                    'data' => ''
                ];
            } else {
                $result = $clientModel->delete($data['id']);
                if ($result) {
                    $response = [
                        'status' => 'success',
                        'statusText' => '',
                        'message' => 'Client Deleted successfully',
                        'data' => ''
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'statusText' => '',
                        'message' => 'Contact system administrator',
                        'data' => ''
                    ];
                }
            }
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    private function format_row_data($data)
    {
        $client = [];
        //$client[] = $data->id;
        $client[] = $data->internal_mrn;
        $client[] = $data->name();
        $client[] = ($data->description) ? $data->description : '';
        if ($data->status == 1) {
            $client[] = '<span class="badge border border-success text-success">Active</span>';
        } else {
            $client[] = '<span class="badge badge border border-success text-danger">In-active</span>';
        }

        $client[] = $data->created_at->humanize();
        $action_btn = '';

        if (auth()->user()->can('clients.update')) {
            $action_btn .= '<button id="' .  encodeValue($data->id) . '" mrn="' . $data->internal_mrn . '" type="button" class="btn btn-sm btn-outline-warning update-client"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>&nbsp;';
        }
        if ($data->status == 1) {
            if (auth()->user()->can('clients.deactivate')) {
                $action_btn .= '<button id="' . $data->id . '" status="' . $data->status . '" mrn="' . $data->internal_mrn . '" type="button" class="btn  btn-sm btn-outline-info status-change "><i class="ri-link-unlink align-bottom me-1"></i>De-activate</button>';
            }
        } else {
            if (auth()->user()->can('clients.activate')) {
                $action_btn .= '<button id="' . $data->id . '" status="' . $data->status . '"  mrn="' . $data->internal_mrn . '" type="button" class="btn  btn-sm btn-outline-success status-change" style="width: 100px;"><i class="ri-link align-bottom me-1"></i>Activate</button>';
            }
        }
        if (auth()->user()->can('clients.delete')) {
            $action_btn .= '&nbsp;<button id="' . $data->id . '" status="' . $data->status . '" mrn="' . $data->internal_mrn . '" type="button" class="btn  btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>';
        }

        $client[] = $action_btn;

        return $client;
    }

    /******************************************************************** */
    /*  SAVE CLIENT BASIC & DIAGNOSIS INFO  */
    public function save_info()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $infoModel = model(\App\Models\ClientConfiguration\ClientInfoModel::class);
        $otherDiagModel = model(\App\Models\ClientConfiguration\ClientOtherDiagnosisModel::class);

        // --- Main client info ---
        $data = [
            'date_of_birth'            => to_sql_date($this->request->getPost('date_of_birth')),
            'address'                  => $this->request->getPost('address'),
            'primary_diagnosis'        => $this->request->getPost('primary_diagnosis'),
            'date_primary_diagnosis'   => to_sql_date($this->request->getPost('date_primary_diagnosis')),
            'age_primary_diagnosis'    => $this->request->getPost('age_primary_diagnosis'),
            'secondary_diagnosis'      => $this->request->getPost('secondary_diagnosis'),
            'date_secondary_diagnosis' => to_sql_date($this->request->getPost('date_secondary_diagnosis')),
            'age_secondary_diagnosis'  => $this->request->getPost('age_secondary_diagnosis'),
        ];

        $infoResult = $infoModel->upsertByClientId($clientId, $data);

        // --- Other Diagnoses (array style) ---
        $names = $this->request->getPost('diagnosis_name');
        $dates = $this->request->getPost('diagnosis_date');
        $ages  = $this->request->getPost('diagnosis_age');

        $rows = [];
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                if (trim($name) === '') continue;
                $rows[] = [
                    'diagnosis_name' => $name,
                    'diagnosis_date' => to_sql_date($dates[$i] ?? null),
                    'diagnosis_age'  => $ages[$i] ?? null,
                ];
            }
        }

        $otherDiagModel->replaceClientDiagnoses($clientId, $rows);

        return $this->response->setJSON([
            'status'  => $infoResult ? 'success' : 'error',
            'message' => $infoResult
                ? 'Client information saved successfully.'
                : 'Unable to save client information.'
        ]);
    }


    /********************************************************************
     *  SAVE PARENT & LEGAL GUARDIAN INFO
     ********************************************************************/
    public function save_guardians()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $names    = $this->request->getPost('name');
        $addresses = $this->request->getPost('address');
        $telephones = $this->request->getPost('telephone');
        $emails   = $this->request->getPost('email');

        $model = model(\App\Models\ClientConfiguration\ClientGuardianModel::class);

        // First remove all existing guardians for this client
        $model->where('client_id', $clientId)->delete();

        // Prepare and insert new guardians
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                if (empty(trim($names[$i] ?? ''))) continue; // Skip empty rows
                $model->insert([
                    'client_id' => $clientId,
                    'name'      => $names[$i] ?? null,
                    'address'   => $addresses[$i] ?? null,
                    'telephone' => $telephones[$i] ?? null,
                    'email'     => $emails[$i] ?? null,
                ]);
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Guardian information saved successfully.'
        ]);
    }

    /******************************************************************** */
    /*  SAVE HOUSEHOLD MEMBERS  */
    public function save_household()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $names = $this->request->getPost('household')['name'] ?? [];
        $ages = $this->request->getPost('household')['age'] ?? [];
        $relations = $this->request->getPost('household')['relationship'] ?? [];
        $ids = $this->request->getPost('household')['id'] ?? []; // hidden IDs in form (for existing rows)

        $model = model(\App\Models\ClientConfiguration\ClientHouseholdModel::class);

        // 1️⃣ Fetch existing records
        $existing = $model->where('client_id', $clientId)->findAll();
        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[$row['id']] = $row;
        }

        // 2️⃣ Track which IDs we keep
        $keepIds = [];

        // 3️⃣ Loop through form data
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                $id = $ids[$i] ?? null;
                $record = [
                    'client_id'    => $clientId,
                    'name'         => trim($name) ?: null,
                    'age'          => $ages[$i] ?? null,
                    'relationship' => $relations[$i] ?? null,
                ];

                if ($id && isset($existingMap[$id])) {
                    // ✅ Update existing
                    $model->update($id, $record);
                    $keepIds[] = $id;
                } else {
                    // ✅ Insert new
                    if (!empty(trim($name))) {
                        $newId = $model->insert($record);
                        $keepIds[] = $newId;
                    }
                }
            }
        }

        // 4️⃣ Delete removed ones (not present anymore)
        if (!empty($existingMap)) {
            $toDelete = array_diff(array_keys($existingMap), $keepIds);
            if (!empty($toDelete)) {
                $model->whereIn('id', $toDelete)->delete();
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Household members saved successfully.'
        ]);
    }

    /******************************************************************** */
    /*  SAVE MEDICAL INFO  */
    public function save_medical()
    {
        $clientId = $this->request->getPost('client_id');
        $model = model(\App\Models\ClientConfiguration\ClientMedicalModel::class);

        $data = [
            'prescribing_doctor'       => $this->request->getPost('prescribing_doctor'),
            'previous_medications'     => $this->request->getPost('previous_medications'),
            'medical_conditions'       => $this->request->getPost('medical_conditions'),
            'allergies'                => $this->request->getPost('allergies'),
            'current_medical_provider' => $this->request->getPost('current_medical_provider'),
            'sleeping_habits'          => $this->request->getPost('sleeping_habits'),
            'eating_habits'            => $this->request->getPost('eating_habits'),
        ];

        $result = $model->upsertByClientId($clientId, $data);

        return $this->response->setJSON([
            'status'  => $result ? 'success' : 'error',
            'message' => $result ? 'Medical information saved.' : 'Unable to save medical info.'
        ]);
    }

    /******************************************************************** */
    /*  SAVE MEDICATIONS & SUPPLEMENTS  */
    public function save_medications()
    {
        $clientId = $this->request->getPost('client_id');
        $medications = $this->request->getPost('medications'); // associative array of columns

        $model = model(\App\Models\ClientConfiguration\ClientMedicationModel::class);

        // Remove old records first (or use upsert logic later)
        $model->where('client_id', $clientId)->delete();

        if (is_array($medications) && isset($medications['name'])) {
            $count = count($medications['name']);

            for ($i = 0; $i < $count; $i++) {
                // Skip completely empty rows
                if (empty($medications['name'][$i]) && empty($medications['category'][$i])) {
                    continue;
                }

                $model->insert([
                    'client_id'      => $clientId,
                    'category'       => $medications['category'][$i] ?? null,
                    'name'           => $medications['name'][$i] ?? null,
                    'dosage'         => $medications['dosage'][$i] ?? null,
                    'frequency'      => $medications['frequency'][$i] ?? null,
                    'prescribed_for' => $medications['prescribed_for'][$i] ?? null,
                ]);
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Medications & supplements saved successfully.'
        ]);
    }


    /******************************************************************** */
    /*  SAVE EDUCATION INFO  */
    public function save_education()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $model = model(\App\Models\ClientConfiguration\ClientEducationModel::class);

        // enums whitelist
        $allowedSetting = ['Home', 'School', 'Both'];
        $allowedType    = ['Mainstream', 'Special Education'];

        $educational_setting = $this->request->getPost('educational_setting');
        if (!in_array($educational_setting, $allowedSetting, true)) {
            $educational_setting = null;
        }

        $school_type = $this->request->getPost('school_type');
        if (!in_array($school_type, $allowedType, true)) {
            $school_type = null;
        }

        $data = [
            'educational_setting'      => $educational_setting,
            'school_name'              => $this->request->getPost('school_name'),
            'one_to_one_support'       => (int) ($this->request->getPost('one_to_one_support') ? 1 : 0),
            'school_type'              => $school_type,
            'date_enrolled'            => to_sql_date($this->request->getPost('date_enrolled')),
            'attendance_schedule'      => $this->request->getPost('attendance_schedule'),
            'home_program'             => (int) ($this->request->getPost('home_program') ? 1 : 0),
            'weekly_hours'             => $this->request->getPost('weekly_hours') !== null
                ? (float) $this->request->getPost('weekly_hours')
                : null,
            'home_program_start_date'  => to_sql_date($this->request->getPost('home_program_start_date')),
        ];

        $result = $model->upsertByClientId($clientId, $data);

        return $this->response->setJSON([
            'status'  => $result ? 'success' : 'error',
            'message' => $result
                ? 'Education information saved successfully.'
                : 'Unable to save education information.'
        ]);
    }

    /******************************************************************** */
    /*  SAVE KEY INFORMATION  */
    public function save_effective_teaching_procedures()
    {
        $clientId = (int) $this->request->getPost('client_id');
        if ($clientId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid client id.',
            ]);
        }

        $model = model(\App\Models\ClientConfiguration\ClientEffectiveTeachingProcedureModel::class);

        $toNullableText = static function ($value) {
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $data = [
            'competing_positive_reinforcers' => $toNullableText($this->request->getPost('competing_positive_reinforcers')),
            'mix_and_vary_tasks' => $toNullableText($this->request->getPost('mix_and_vary_tasks')),
            'errorless_teaching_procedures' => $toNullableText($this->request->getPost('errorless_teaching_procedures')),
            'easy_to_hard_percentage' => $toNullableText($this->request->getPost('easy_to_hard_percentage')),
            'easy_responses_fade_start' => $toNullableText($this->request->getPost('easy_responses_fade_start')),
            'schedule_of_reinforcement' => $toNullableText($this->request->getPost('schedule_of_reinforcement')),
            'general_comment' => $toNullableText($this->request->getPost('general_comment')),
        ];

        $result = $model->upsertByClientId($clientId, $data);

        return $this->response->setJSON([
            'status'  => $result ? 'success' : 'error',
            'message' => $result
                ? 'Key information saved successfully.'
                : 'Unable to save key information.',
        ]);
    }
}
