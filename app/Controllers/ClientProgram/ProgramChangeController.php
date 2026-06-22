<?php

namespace App\Controllers\ClientProgram;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\Auth\UserModel;

use App\Models\ClientSessions\DailySessionModel;

use App\Models\ClientProgram\ClientProgramTargetOverridesModel;
use App\Models\ClientProgram\ClientProgramChangeAlertModel;
use App\Models\ClientProgram\ClientProgramChangeModel;
use App\Models\ClientProgram\ClientProgramChangeAntModel;
use App\Models\ClientProgram\ClientProgramChangeConModel;
use App\Models\ClientProgram\ClientTargetModel;




//use function PHPUnit\Framework\isNull;

class ProgramChangeController extends AdminController
{
    use ResponseTrait;

    protected $dailySessionModel;
    protected $clientModel;
    protected $userModel;

    protected $targetOverridesModel;
    protected $alertModel;
    protected $programChangeModel;
    protected $antModel;
    protected $conModel;
    protected $targetModel;



    public function __construct()
    {
        // Load your model in the constructor

        $this->dailySessionModel = new DailySessionModel();

        $this->clientModel = new ClientModel();
        $this->userModel = new UserModel();

        $this->targetOverridesModel = new ClientProgramTargetOverridesModel();
        $this->alertModel = new ClientProgramChangeAlertModel();
        $this->programChangeModel = new ClientProgramChangeModel();
        $this->antModel = new ClientProgramChangeAntModel();
        $this->conModel = new ClientProgramChangeConModel();
        $this->targetModel = new ClientTargetModel();
    }

    public function getForm()
    {
        $pg_alert_id = $this->request->getPost('pg_alert_id');
        $alert = $this->alertModel->asObject()->find($pg_alert_id);

        $is_change_made = $alert->is_change_made;


        if ($is_change_made) {
            return $this->getChangeRecord();
        } {
            return $this->getChangeForm();
        }
    }

    private function getChangeRecord()
    {
        $pg_change_id = $this->request->getPost('pg_change_id');
        $program_change = null;
        if ($pg_change_id == null || $pg_change_id == '') {
            $pg_alert_id = $this->request->getPost('pg_alert_id');
            $program_change = $this->programChangeModel->getProgramChangeWithUserByAlert($pg_alert_id);
            //$this->programChangeModel->asObject()->where('alert_id', $pg_alert_id)->first();
        } else {
            $program_change = $this->programChangeModel->getProgramChangeWithUser($pg_change_id);
        }

        $ant = $this->antModel->where('prog_ch_id', $program_change->id)->findAll();
        $con =  $this->conModel->where('prog_ch_id', $program_change->id)->findAll();


        $client_id = $program_change->client_id;
        $target_id = $program_change->target_id;
        $client_probe_set_id = $program_change->client_probe_set_id;

        // Get the count from ClientProgramChangeModel
        $changeCount = $this->programChangeModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        // Get the alert count from ClientProgramChangeAlertModel (if needed)
        $alertCount = $this->alertModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        $session = $this->dailySessionModel->get_client_executed_session($program_change->session_id);
        $client = $this->clientModel->find($program_change->client_id);
        $target = $this->targetModel->single($program_change->target_id);

        // Pass data to the view
        $data = [
            'program_change' => $program_change,
            'changeCount' => $changeCount,
            'alertCount' => $alertCount,
            'session' => $session,
            'client' => $client,
            'target' => $target,
            'ant' => $ant,
            'con' => $con,
        ];

        return  view('ProgramChange/program_change_data', $data);
    }

    private function getChangeForm()
    {
        $pg_alert_id = $this->request->getPost('pg_alert_id');
        $alert = $this->alertModel->asObject()->find($pg_alert_id);

        $alert_date = $alert->session_date;
        $client_id = $alert->client_id;
        $target_id = $alert->target_id;
        $client_probe_set_id = $alert->client_probe_set_id;
        // Check if an alert exists after the given date
        $alertExists = $this->alertModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('session_date >', $alert_date)  // Check if session_date is greater than alert_date
            ->first();  // Returns the first matching record if exists

        if ($alertExists) {
            // Alert exists after the given date
            return '';
        }

        // Get the count from ClientProgramChangeModel
        $changeCount = $this->programChangeModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        // Get the alert count from ClientProgramChangeAlertModel (if needed)
        $alertCount = $this->alertModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        $session = $this->dailySessionModel->get_client_executed_session($alert->session_id);
        $client = $this->clientModel->find($alert->client_id);
        $target = $this->targetModel->single($alert->target_id);

        // Pass data to the view
        $data = [
            'pg_alert_id' => $pg_alert_id,
            'changeCount' => $changeCount,
            'alertCount' => $alertCount,
            'session' => $session,
            'client' => $client,
            'target' => $target
        ];

        return  view('ProgramChange/program_change_form', $data);
    }


    public function saveProgramChange()
    {
        // Check if program change already exists for the given alert
        $existingProgramChange = $this->programChangeModel->where('alert_id', $this->request->getPost('alert_id'))->find();
        if ($existingProgramChange) {
            $response = $this->getResponseObject('success', '', 'Program change exists for given alert. You cannot add another one.', [], []);
            return $this->response->setJSON($response);
        }

        // Extract POST data
        $antecedents = $this->request->getPost('ant');
        $consequences = $this->request->getPost('consequence');
        $alert = $this->alertModel->asObject()->find($this->request->getPost('alert_id'));

        // Data for validation and insertion
        $ruleData = [
            'alert_id' => $this->request->getPost('alert_id'),
            'c_yes' => $this->request->getPost('c_yes'),
            'incorrect_response' => $this->request->getPost('incorrect_response'),
            'behavioral_variables' => $this->request->getPost('behavioral_variables'),
            'description' => $this->request->getPost('description'),
            'consequence' => $consequences,
            'ant' => $antecedents,
        ];

        // Validation rules
        $rules = [
            'alert_id' => [
                'label' => 'Alert ID',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Alert ID is required.',
                ]
            ],
            'ant' => [
                'label' => 'Antecedent',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please select at least one antecedent.',
                ]
            ],
            'consequence' => [
                'label' => 'Consequence',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please select at least one consequence.',
                ]
            ],
            'incorrect_response' => [
                'label' => 'Incorrect Response',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Please provide an incorrect response.',
                    'min_length' => 'Incorrect response must be at least 3 characters.',
                ]
            ],
            'behavioral_variables' => [
                'label' => 'Behavioral Variables',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Please provide behavioral variables.',
                    'min_length' => 'Behavioral variables must be at least 3 characters.',
                ]
            ],
            'description' => [
                'label' => 'Description',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Please provide a description.',
                    'min_length' => 'Description must be at least 3 characters.',
                ]
            ],
            'c_yes' => [
                'label' => 'Consecutive Criteria',
                'rules' => 'permit_empty|greater_than[0]',
                'errors' => [
                    'integer' => 'Consecutive Criteria must be a valid number.',
                    'greater_than' => 'Consecutive Criteria must be greater than 0.',
                ]
            ]
        ];

        // Perform validation
        if (!$this->validateData($ruleData, $rules)) {
            // Validation failed, return validation errors
            $errors = $this->validator->getErrors();
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $errors, []);
            return $this->response->setJSON($response);
        }

        // Custom validation for 'other_ant' field
        if (isset($antecedents) && in_array('18', $antecedents)) {
            if (empty($this->request->getPost('other_antecedent')) || strlen($this->request->getPost('other_antecedent')) < 3) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'Other Antecedent Validation Errors', ['other_ant' => 'Other antecedent must be at least 3 characters.'], []);
                return $this->response->setJSON($response);
            }
        }

        // Custom validation for 'other_con' field
        if (isset($consequences) && in_array('9', $consequences)) {
            if (empty($this->request->getPost('other_consequence')) || strlen($this->request->getPost('other_consequence')) < 3) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'Other Consequence Validation Errors', ['other_con' => 'Other consequence must be at least 3 characters.'], []);
                return $this->response->setJSON($response);
            }
        }

        // Data for insertion
        $data = [
            'alert_id' => $this->request->getPost('alert_id'),
            'client_id' => $alert->client_id,
            'domain_id' => $alert->domain_id,
            'goal_id' => $alert->goal_id,
            'target_id' => $alert->target_id,
            'collection_id' => $alert->collection_id,
            'processed_data_id' => $alert->processed_data_id,
            'session_id' => $alert->session_id,
            'session_date' => $alert->session_date,
            'client_probe_set_id' => $alert->client_probe_set_id,
            'consecutive_criteria' => (!empty($this->request->getPost('c_yes'))) ? $this->request->getPost('c_yes') : null,
            'other_ant' => (string) $this->request->getPost('other_antecedent'),
            'other_con' => (string) $this->request->getPost('other_consequence'),
            'incorrect_response' => $this->request->getPost('incorrect_response'),
            'behavioral_variables' => $this->request->getPost('behavioral_variables'),
            'description' => $this->request->getPost('description'),
            'created_by' => auth()->user()->id, // Assuming you have a function to get the current user ID
        ];

        // Insert into dp_client_program_change
        $pg_id = $this->programChangeModel->saveChanges($data);

        // Insert antecedents into dp_client_program_change_ant
        if (!empty($antecedents)) {
            foreach ($antecedents as $antecedent) {
                $this->antModel->insert(['prog_ch_id' => $pg_id, 'ant_id' => $antecedent]);
            }
        }

        // Insert consequences into dp_client_program_change_con
        if (!empty($consequences)) {
            foreach ($consequences as $consequence) {
                $this->conModel->insert(['prog_ch_id' => $pg_id, 'con_id' => $consequence]);
            }
        }

        // Check for override update
        if (!empty($this->request->getPost('c_yes'))) {
            $dataOverride = [
                'client_id' => $alert->client_id,
                'domain_id' => $alert->domain_id,
                'goal_id' => $alert->goal_id,
                'target_id' => $alert->target_id,
                'probe_set_id' => $alert->client_probe_set_id,
                'consecutive_criteria' => $this->request->getPost('c_yes'),
                'updated_by' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s'), // Assuming you're not using automatic timestamps
            ];

            // Check if an override already exists
            $existingOverride = $this->targetOverridesModel
                ->where([
                    'client_id' => $alert->client_id,
                    'target_id' => $alert->target_id,
                    'probe_set_id' => $alert->client_probe_set_id
                ])->first();

            if ($existingOverride) {
                // Update existing override                
                $this->targetOverridesModel->update($existingOverride->id, $dataOverride);
            } else {
                // Insert new override
                $dataOverride['created_by'] = auth()->user()->id; // Set created_by for new entry
                $this->targetOverridesModel->insert($dataOverride);
            }
        }
        $this->alertModel->update($alert->id, ['is_change_made' => 1]);
        $data = $this->getUpdatedRecord($pg_id);
        $response = $this->getResponseObject('success', '', 'Program change made successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    private function getUpdatedRecord($pg_change_id)
    {

        $program_change = $this->programChangeModel->asObject()->find($pg_change_id);

        $client_id = $program_change->client_id;
        $target_id = $program_change->target_id;
        $client_probe_set_id = $program_change->client_probe_set_id;

        // Get the count from ClientProgramChangeModel
        $changeCount = $this->programChangeModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        // Get the alert count from ClientProgramChangeAlertModel (if needed)
        $alertCount = $this->alertModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->countAllResults();

        $alertFrequencyHtml = '';
        if ($alertCount) {
            if ($alertCount) {
                $alertFrequencyHtml = '
                    <p id="target-alert-frequency-' . $target_id . '" class="list-text mb-0 fs-12 fs-6 text">
                        <b>Program Alert Frequency:</b> 
                        <span class="badge bg-danger-subtle text-danger">' . $alertCount . '</span>&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>Program Change Frequency:</b> 
                        <span class="badge bg-danger-subtle text-danger">' . $changeCount . '</span>
                    </p>';
            }
        }

        $existingOverride = $this->targetOverridesModel->asObject()
            ->where([
                'client_id' => $program_change->client_id,
                'target_id' => $program_change->target_id,
                'probe_set_id' => $program_change->client_probe_set_id
            ])->first();

        $overrideCriteriaHtml = '';
        if ($existingOverride) {
            $overrideCriteriaHtml = '<span class="override-criteria">
                <em class="link-warning fs-6 text"> (Override teaching trials: ' . $existingOverride->consecutive_criteria . ')</em>
            </span>';
        }


        // Pass data to the view
        $data = [
            'targetId' => $target_id,
            'overrideCriteriaHtml' => $overrideCriteriaHtml,
            'alertFrequencyHtml' => $alertFrequencyHtml
        ];
        return   $data;
    }
}
