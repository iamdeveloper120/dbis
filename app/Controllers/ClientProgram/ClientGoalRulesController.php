<?php

namespace App\Controllers\ClientProgram;

use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Controllers\AdminController;
use App\Models\ClientProgram\ClientProbeSetModel;
use App\Models\ClientProgram\ClientProbeSetRuleModel;

use App\Models\MasterProgram\TargetProbeSetModel;
use App\Models\MasterProgram\TargetProbeSetRuleModel;
use App\Models\MasterProgram\TargetPhaseCombinationModel;

use App\Models\ClientConfiguration\ClientModel;
use App\Entities\ClientConfiguration\Client;
use App\Models\ClientProgram\ClientDomainModel;
use App\Entities\ClientProgram\ClientDomain;
use App\Models\ClientProgram\ClientGoalModel;
use App\Entities\ClientProgram\ClientGoal;

class ClientGoalRulesController extends AdminController
{
    protected $clientProbeSetModel;
    protected $clientProbeSetRuleModel;
    protected $targetProbeSetModel;
    protected $targetProbeSetRuleModel;
    protected $targetPhaseCombinationModel;
    protected $db;

    protected $clientModel;
    protected $clientDomainModel;
    protected $clientGoalModel;

    public function __construct()
    {
        $this->clientProbeSetModel = new ClientProbeSetModel();
        $this->clientProbeSetRuleModel = new ClientProbeSetRuleModel();
        $this->targetProbeSetModel = new TargetProbeSetModel();
        $this->targetProbeSetRuleModel = new TargetProbeSetRuleModel();
        $this->targetPhaseCombinationModel = new TargetPhaseCombinationModel();

        $this->clientModel = new ClientModel();
        $this->clientDomainModel = new ClientDomainModel();
        $this->clientGoalModel = new ClientGoalModel();

        $this->db = \Config\Database::connect();
    }

    public function check_goal_probe_sets()
    {
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        $activeProbeSets = $this->clientProbeSetModel->where('goal_id', $goalId)
            ->where('client_id', $clientId)
            ->findAll();

        $inactiveProbeSets = $this->clientProbeSetModel->where('goal_id', $goalId)
            ->where('client_id', $clientId)
            ->where('is_active', 0)
            ->findAll();

        if (!empty($activeProbeSets)) {
            return $this->response->setJSON([
                'status' => 'success',
                'has_probe_sets' => true,
                'message' => 'This goal has active probe sets linked.'
            ]);
        } else if (!empty($inactiveProbeSets)) {
            return $this->response->setJSON([
                'status' => 'success',
                'has_probe_sets' => true,
                'message' => 'This goal has inactive probe sets linked. you should activate one of them.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'no_config',
                'has_probe_sets' => false,
                'message' => 'This goal does not have any linked probe sets. you should link one.'
            ]);
        }
    }
    public function create_probe_set_configuration()
    {
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        $client = $this->clientModel->find($clientId);
        $goal = $this->clientGoalModel->where(['id' => $goalId, 'client_id' => $clientId])->first();
        $domain = $this->clientDomainModel->where(['id' => $goal->domain_id, 'client_id' => $clientId])->first();

        // Prepare the formatted string
        $infoString = "<strong>Client:</strong> {$client->name()} ({$client->internal_mrn}) "
            . "<strong>Domain:</strong> {$domain->domain_code} - {$domain->name}  "
            . "<strong>Goal:</strong> {$goal->goal_code} - {$goal->name}";

        // Fetch all available probe sets and combinations
        $probeSets = $this->targetProbeSetModel->getAllProbeSets();

        // Render options to create a new configuration
        $newHtml = view('ClientProgram/new_probe_set', [
            'probeSets' => $probeSets,
            'clientId' => $clientId,
            'goalId' => $goalId,
            'infoString' => $infoString,
        ]);


        return $this->response->setJSON([
            'status' => 'no_config',
            'html' => $newHtml,
        ]);
    }
    /************************************************************************************** */
    public function get_probe_set_phase_combinations()
    {
        $probe_set_id = $this->request->getPost('probe_set_id');

        $combinations = $this->targetProbeSetModel->getCombinationsForProbeSet($probe_set_id);
        if (empty($combinations)) {
            return $this->response->setJSON($this->getResponseObject('error', 'Phase Combination', 'No combinations found for the given probe set', [],  []));
        }
        return $this->response->setJSON($this->getResponseObject('success', '', '', [],  $combinations));
    }
    /************************************************************************************** */
    public function get_probe_set_detail_and_rules()
    {
        $probeSetId = $this->request->getPost('probe_set_id');
        $combinationId = $this->request->getPost('combination_id');

        $probeSet =  $this->targetProbeSetModel->getProbeSetById($probeSetId);
        if (!$probeSet) {
            return   $this->response->setJSON($this->getResponseObject('error', 'Probe Set and Phase Combination', 'Probe set not found', [],  []));
        }

        $inputs = json_decode($probeSet['inputs'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            //throw new \Exception('Invalid JSON format');
            return   $this->response->setJSON($this->getResponseObject('error', 'Probe Set Inputs', 'Invalid JSON format', [],  []));
        }

        $inputsHtml = $this->renderInputs($inputs);


        $rules = $this->targetProbeSetRuleModel->getRulesForSelectedProbeSetAndCombination($probeSetId, $combinationId);

        $rulesHtml = $this->renderRules($rules, $probeSet['name']);

        // Render the HTML view for probe set details
        $probeSetHtml = 'Unknown Input Format';
        if ($inputs['type'] == 'percentage_yes_no') {
            $probeSetHtml = view('ClientProgram/probe_set_details_percentage', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        } else if ($inputs['type'] == 'stimulus_program') {
            $probeSetHtml = view('ClientProgram/stimulus_program_probe_set_details', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        } else {
            $probeSetHtml = view('ClientProgram/probe_set_details', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        }


        $data =  [
            'probeSetHtml' => $probeSetHtml,
            'rulesHtml' => $rulesHtml,
        ];
        return   $this->response->setJSON($this->getResponseObject('success', '', '', [],  $data));
    }

    /************************************************************************************** */
    // Probe Set Input Rendering
    private function renderInputs(array $inputs)
    {
        if (!isset($inputs['type'])) {
            throw new \Exception('Invalid input structure: Missing type key');
            return   $this->response->setJSON($this->getResponseObject('error', 'Probe Set Inputs', 'Invalid input structure: Missing type key', [],  []));
        }

        switch ($inputs['type']) {
            case 'yes_no':
                return $this->renderYesNoInputs($inputs['choices']);
            case 'count':
                return $this->renderCountInputs($inputs['range']);
            case 'traffic_light':
                return $this->renderTrafficLightInputs($inputs['choices']);
            case 'prompt_level':
                return $this->renderPromptLevelInputs($inputs['choices']);
            case 'duration':
                return $this->renderDurationInputs($inputs['choices']);
            case 'percentage_yes_no':
                return $this->renderPercentageInputs($inputs['choices']);
            case 'stimulus_program':
                return $this->renderStimulusProgramInputs($inputs);
            default:
                return '<p>Unknown Input Format</p>';
        }
    }
    private function renderYesNoInputs(array $choices)
    {
        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($choices as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$value}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }

    private function renderTrafficLightInputs(array $choices)
    {
        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($choices as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$value}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }

    private function renderPromptLevelInputs(array $choices)
    {
        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($choices as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $choice['value']);
            $html .= "
            <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$safeId}' value='{$value}' autocomplete='off'>
            <label class='btn btn-outline-primary' for='btn_radio_{$safeId}' title='{$label}'>{$value}</label>
        ";
        }
        $html .= '</div>';
        return $html;
    }

    private function renderDurationInputs(array $choices)
    {
        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($choices as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$label}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }

    private function renderCountInputs(array $range)
    {
        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        for ($i = $range['min']; $i <= $range['max']; $i++) {
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$i}' value='{$i}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$i}' title='Count: {$i}'>{$i}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }

    private function renderPercentageInputs(array $choices)
    {

        $html = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($choices as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$value}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }
    private function renderStimulusProgramInputs(array $inputs)
    {
        $html = "<div class='col-lg-4'>Baseline Phase Selection</div>";
        $html .= '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($inputs['baseline_choices'] as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$value}</label>
            ";
        }
        $html .= '</div>';
        $html .= "<div class='col-lg-4'>Acquisition Phase Selection</div>";
        $html .= '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
        foreach ($inputs['teaching_choices'] as $choice) {
            $value = esc($choice['value']);
            $label = esc($choice['label']);
            $html .= "
                <input type='radio' class='btn-check' name='probe_input' id='btn_radio_{$value}' value='{$value}' autocomplete='off'>
                <label class='btn btn-outline-primary' for='btn_radio_{$value}' title='{$label}'>{$value}</label>
            ";
        }
        $html .= '</div>';
        return $html;
    }
    /************************************************************************************** */
    // Rule Rendering  

    private function renderRules(array $rules, string $probeSetName): string
    {
        $rulesHtml = '';
        $combinationName = 'Combination Name';
        // Process each rule to extract JSON data
        foreach ($rules as &$rule) {
            $rule['json_rules'] = json_decode($rule['rules'], true);
            // Map phase IDs to names
            $rule['p_phase_name'] = get_phase_name($rule['json_rules']['p_phase_id'] ?? null);
            $rule['f_phase_name'] = get_phase_name($rule['json_rules']['f_phase_id'] ?? null);
        }
        switch ($probeSetName) {
            case 'Yes/No Probes':
                $rulesHtml .= view('ClientProgram/yes_no_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Traffic Light Probes':
                $rulesHtml .= view('ClientProgram/traffic_light_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Prompt Level Probes':
                $rulesHtml .= view('ClientProgram/prompt_level_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Count Probes':
                $rulesHtml .= view('ClientProgram/count_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Duration Probes':
                $rulesHtml .= view('ClientProgram/duration_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Percentage Yes/No Probes':
                $rulesHtml .= view('ClientProgram/percentage_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            case 'Stimulus Program Probes':
                $rulesHtml .= view('ClientProgram/stimulus_rules', [
                    'combinationName' => $combinationName,
                    'rules' => $rules,
                ]);
                break;
            default:
                $rulesHtml .= '<p>No rules available for this probe set</p>';
                break;
        }

        return $rulesHtml;
    }
    /************************************************************************************* */

    public function save_probe_set_and_rules()
    {
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');
        $probeSetId = $this->request->getPost('probe_set_id');
        $combinationId = $this->request->getPost('combination_id');
        $current_user_id = auth()->user()->id;

        // Validate required fields
        if (!$goalId || !$clientId || !$probeSetId || !$combinationId) {
            log_message('error', 'Client->ProbeSet->Attaching new rules. Required fields are missing.');
            return $this->response->setJSON(['status' => 'error', 'message' => 'Required fields are missing.']);
        }

        // Check if the probe set and combination already exist for the given client and goal
        $existingProbeSet = $this->clientProbeSetModel
            ->where('client_id', $clientId)
            ->where('goal_id', $goalId)
            ->where('probe_set_id', $probeSetId)
            ->first();

        if ($existingProbeSet) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This probe set already linked with selected client and goal.']);
        }


        try {
            // Start a transaction
            $this->db->transException(true)->transStart();

            // Deactivate any existing active probe sets for the given client and goal
            $this->clientProbeSetModel
                ->where('client_id', $clientId)
                ->where('goal_id', $goalId)
                ->where('is_active', 1)
                ->set('is_active', 0)
                ->update();

            // Fetch master data for the selected probe set and combination
            $masterProbeSet = $this->targetProbeSetModel->getProbeSetById($probeSetId);
            $masterRules = $this->targetProbeSetRuleModel->getRulesForSelectedProbeSetAndCombination($probeSetId, $combinationId);

            if (!$masterProbeSet || !$masterRules) {
                log_message('error', 'Client->ProbeSet->Attaching new rules. Invalid probe set or combination.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid probe set or combination.']);
            }

            // Decode the master inputs
            $masterInputs = json_decode($masterProbeSet['inputs'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Client->ProbeSet->Attaching new rules. Invalid JSON format in master probe set inputs.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid JSON format in master probe set inputs.']);
            }

            // Initialize variables to store form inputs
            $updatedInputs = $masterInputs;

            // Process form inputs based on the probe set type
            switch ($masterInputs['type']) {
                case 'count':
                    $minValue = $this->request->getPost('min');
                    $maxValue = $this->request->getPost('max');
                    if (isset($minValue) && isset($maxValue)) {
                        $updatedInputs['range']['min'] = $minValue;
                        $updatedInputs['range']['max'] = $maxValue;
                        $updatedInputs['key'] = $maxValue; // Update key with max value
                    }
                    break;

                case 'duration':
                    $durationValues = $this->request->getPost('duration_values');
                    $durationLabels = $this->request->getPost('duration_labels');
                    if (!empty($durationValues) && !empty($durationLabels)) {
                        $choices = [];
                        foreach ($durationValues as $index => $value) {
                            $choices[] = [
                                'value' => $value,
                                'label' => $durationLabels[$index] ?? ''
                            ];
                        }
                        $updatedInputs['choices'] = $choices;
                        $updatedInputs['key'] = end($durationValues); // Update key with max duration value
                    }
                    break;

                case 'prompt_level':
                    $promptLevelValues = $this->request->getPost('prompt_level_values');
                    $promptLevelLabels = $this->request->getPost('prompt_level_labels');
                    if (!empty($promptLevelValues) && !empty($promptLevelLabels)) {
                        $choices = [];
                        foreach ($promptLevelValues as $index => $value) {
                            $choices[] = [
                                'value' => $value,
                                'label' => $promptLevelLabels[$index] ?? ''
                            ];
                        }
                        $updatedInputs['choices'] = $choices;
                        $updatedInputs['key'] = end($promptLevelValues); // Update key with max duration value
                    }
                    break;

                case 'percentage_yes_no':
                    $c_key = $this->request->getPost('c_key');
                    if ($c_key != '' && is_numeric($c_key) && $c_key >= 0 && $c_key <= 100) {
                        $updatedInputs['key'] = $c_key;
                    }
                    break;


                default:
                    // For other types, no changes in inputs, use master inputs as-is
                    $updatedInputs = $masterInputs;
                    break;
            }

            // Save the updated probe set for the client
            $clientProbeSetData = [
                'client_id' => $clientId,
                'goal_id' => $goalId,
                'probe_set_id' => $probeSetId,
                'combination_id' => $combinationId,
                'inputs' => json_encode($updatedInputs),
                'created_by' => $current_user_id,
            ];

            $clientProbeSetId = $this->clientProbeSetModel->saveProbeSet($clientProbeSetData);

            if (!$clientProbeSetId) {
                log_message('error', 'Client->ProbeSet->Attaching new rules. Failed to save client probe set.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save client probe set.']);
            }

            // Process rules based on the probe set name
            foreach ($masterRules as $masterRule) {
                $phaseId = $masterRule['phase_id'];
                $jsonRules = json_decode($masterRule['rules'], true);

                // Update rule fields based on form data
                switch ($masterProbeSet['name']) {
                    case 'Yes/No Probes':
                        $jsonRules = $this->updateYesNoRules($jsonRules, $phaseId);
                        break;
                    case 'Traffic Light Probes':
                        $jsonRules = $this->updateTrafficLightRules($jsonRules, $phaseId);
                        break;
                    case 'Prompt Level Probes':
                        $jsonRules = $this->updatePromptLevelRules($jsonRules, $phaseId);
                        break;
                    case 'Count Probes':
                        $jsonRules = $this->updateCountRules($jsonRules, $phaseId);
                        break;
                    case 'Duration Probes':
                        $jsonRules = $this->updateDurationRules($jsonRules, $phaseId);
                        break;
                    case 'Percentage Yes/No Probes':
                        $jsonRules = $this->updatePercentageRules($jsonRules, $phaseId);
                        break;
                    default:
                        break;
                }

                // Prepare rule data for saving
                $ruleData = [
                    'client_probe_set_id' => $clientProbeSetId, // Use the generated ID from the probe set
                    'phase_id' => $phaseId,
                    'rules' => json_encode($jsonRules),
                ];
                $ruleSaved = $this->clientProbeSetRuleModel->saveRule($ruleData);

                if (!$ruleSaved) {
                    log_message('error', 'Client->ProbeSet->Attaching new rules. Failed to save rule for phase ID: ' . $phaseId);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save rules for phase ID: ' . $phaseId]);
                }
            }

            // Complete the transaction, which will commit if everything is OK
            $this->db->transComplete();

            // Check if the transaction was successful
            if ($this->db->transStatus() === FALSE) {
                log_message('error', 'Client->ProbeSet->Attaching new rules. Database transaction failed.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Database transaction failed.']);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Probe set and rules linked successfully.']);
        } catch (\Exception $e) {
            // Log the error and rollback the transaction
            log_message('error', 'Client->ProbeSet->Attaching new rules. Exception caught: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while processing your request.']);
        }
    }

    private function updateYesNoRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }

        // Check and update 'frame_check' only if it exists in the request        
        $programChange = $this->request->getPost("frame_check_{$phaseId}");
        $jsonRules['frame_check'] = $programChange !== null ? true : false;

        // Check and update 'program_change' only if it exists in the request
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    // Similar functions for other probe sets (Traffic Light, Prompt Level, Count, Duration)
    private function updateTrafficLightRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }


        // Check and update 'program_change' only if it exists in the request
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    private function updatePromptLevelRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }


        // Check and update 'program_change' only if it exists in the request
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    private function updateCountRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }


        // Check and update 'program_change' only if it exists in the request
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    private function updateDurationRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }


        // Check and update 'program_change' only if it exists in the request
        /* if ($this->request->getPost("program_change_{$phaseId}") !== null) {
            $jsonRules['program_change'] = $this->request->getPost("program_change_{$phaseId}") ? true : false;
        }*/
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    private function updatePercentageRules($jsonRules, $phaseId)
    {
        // Check and update 'consecutive_criteria' only if it exists in the request
        $consecutiveCriteria = $this->request->getPost("consecutive_criteria_{$phaseId}");
        if ($consecutiveCriteria !== null) {
            $jsonRules['consecutive_criteria'] = $consecutiveCriteria;
        }


        // Check and update 'program_change' only if it exists in the request
        /* if ($this->request->getPost("program_change_{$phaseId}") !== null) {
            $jsonRules['program_change'] = $this->request->getPost("program_change_{$phaseId}") ? true : false;
        }*/
        $programChange = $this->request->getPost("program_change_{$phaseId}");
        $jsonRules['program_change'] = $programChange !== null ? true : false;

        // Check and update 'session_limit' only if it exists in the request
        $sessionLimit = $this->request->getPost("session_limit_{$phaseId}");
        if ($sessionLimit !== null) {
            $jsonRules['session_limit'] = $sessionLimit;
        }

        // Check and update 'activation_days' only if it exists in the request and is relevant to phase 3
        if ($phaseId == 3) {
            $activationDays = $this->request->getPost("activation_days_{$phaseId}");
            if ($activationDays !== null) {
                $jsonRules['activation_days'] = $activationDays;
            }
        }

        return $jsonRules;
    }

    /************************************************************************************* */
    // update client existing probe set rules
    public function load_client_existing_probe_sets_list()
    {
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        $client = $this->clientModel->find($clientId);
        $goal = $this->clientGoalModel->where(['id' => $goalId, 'client_id' => $clientId])->first();
        $domain = $this->clientDomainModel->where(['id' => $goal->domain_id, 'client_id' => $clientId])->first();

        // Prepare the formatted string
        $infoString = "<strong>Client:</strong> {$client->name()} ({$client->internal_mrn}) "
            . "<strong>Domain:</strong> {$domain->domain_code} - {$domain->name}  "
            . "<strong>Goal:</strong> {$goal->goal_code} - {$goal->name}";

        // Get probe sets with all related details
        $probeSets = $this->clientProbeSetModel->getProbeSetsWithDetails($clientId, $goalId);

        $html = view('ClientProgram/client_existing_probe_sets_list', [
            'probeSets' => $probeSets,
            'clientId' => $clientId,
            'goalId' => $goalId,
            'infoString' => $infoString
        ]);

        return $this->response->setJSON(['status' => 'success', 'html' => $html]);
    }

    public function load_client_existing_probe_sets_edit_form()
    {
        $probeSetId = $this->request->getPost('probe_set_id');
        $clientProbeSetId = $this->request->getPost('client_probe_set_id');
        $combinationId = $this->request->getPost('combination_id');
        $clientId = $this->request->getPost('client_id');
        $goalId = $this->request->getPost('goal_id');

        // Fetch the client-specific probe set inputs
        $clientProbeSet = $this->clientProbeSetModel
            ->where('id', $clientProbeSetId)
            ->where('client_id', $clientId)
            ->where('goal_id', $goalId)
            ->where('combination_id', $combinationId)
            ->first();

        // Fetch the master probe set
        $masterProbeSet = $this->targetProbeSetModel->getProbeSetById($clientProbeSet['probe_set_id']);
        if (!$masterProbeSet) {
            return $this->response->setJSON($this->getResponseObject('error', 'Probe Set', 'Master probe set not found', [], []));
        }



        if (!$clientProbeSet) {
            return $this->response->setJSON($this->getResponseObject('error', 'Client Probe Set', 'Client probe set not found', [], []));
        }

        // Override the master inputs with client-specific inputs
        $inputs = json_decode($clientProbeSet['inputs'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON($this->getResponseObject('error', 'Client Probe Set Inputs', 'Invalid JSON format in client probe set inputs', [], []));
        }

        $inputsHtml = $this->renderInputs($inputs);

        // Fetch client-specific rules for the given combination
        $clientRules = $this->clientProbeSetRuleModel
            ->where('client_probe_set_id', $clientProbeSet['id'])
            ->findAll();

        // Get the master rules for the combination as a base
        $masterRules = $this->targetProbeSetRuleModel->getRulesForSelectedProbeSetAndCombination($probeSetId, $combinationId);

        // Replace master rules with client-specific rules
        foreach ($masterRules as &$masterRule) {
            foreach ($clientRules as $clientRule) {
                if ($masterRule['phase_id'] === $clientRule['phase_id']) {
                    $masterRule['rules'] = $clientRule['rules'];
                    break;
                }
            }
        }

        $rulesHtml = $this->renderRules($masterRules, $masterProbeSet['name']);

        // Render the probe set details with the modified inputs and rules 
        $probeSetHtml = 'Unknown Input Format';
        if ($inputs['type'] == 'percentage_yes_no') {
            $probeSetHtml = view('ClientProgram/probe_set_details_percentage', [
                'probeSet' => $masterProbeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        } else if ($inputs['type'] == 'stimulus_program') {
            $probeSetHtml = view('ClientProgram/stimulus_program_probe_set_details', [
                'probeSet' => $masterProbeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        } else {
            $probeSetHtml = view('ClientProgram/probe_set_details', [
                'probeSet' => $masterProbeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        }



        $data = [
            'probeSetHtml' => $probeSetHtml,
            'rulesHtml' => $rulesHtml,
        ];

        return $this->response->setJSON($this->getResponseObject('success', '', '', [], $data));
    }
    public function clientActiveProbeSetRules()
    {
         
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        $client = $this->clientModel->find($clientId);
        $goal = $this->clientGoalModel->where(['id' => $goalId, 'client_id' => $clientId])->first();
        $domain = $this->clientDomainModel->where(['id' => $goal->domain_id, 'client_id' => $clientId])->first();

        // Prepare the formatted string
        $infoString = "<strong>Client:</strong> {$client->name()} ({$client->internal_mrn}) "
            . "<strong>Domain:</strong> {$domain->domain_code} - {$domain->name}  "
            . "<strong>Goal:</strong> {$goal->goal_code} - {$goal->name}";

        // Fetch the client-specific probe set inputs
        $clientProbeSet = $this->clientProbeSetModel
            ->where('client_id', $clientId)
            ->where('goal_id', $goalId)
            ->where('is_active', 1)
            ->first();

        // Fetch the master probe set
        $masterProbeSet = $this->targetProbeSetModel->getProbeSetById($clientProbeSet['probe_set_id']);
        if (!$masterProbeSet) {
            return $this->response->setJSON($this->getResponseObject('error', 'Probe Set', 'Master probe set not found', [], []));
        }



        if (!$clientProbeSet) {
            return $this->response->setJSON($this->getResponseObject('error', 'Client Probe Set', 'Client probe set not found', [], []));
        }

        // Override the master inputs with client-specific inputs
        $inputs = json_decode($clientProbeSet['inputs'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON($this->getResponseObject('error', 'Client Probe Set Inputs', 'Invalid JSON format in client probe set inputs', [], []));
        }

        $inputsHtml = $this->renderInputs($inputs);

        // Fetch client-specific rules for the given combination
        $clientRules = $this->clientProbeSetRuleModel
            ->where('client_probe_set_id', $clientProbeSet['id'])
            ->findAll();

        // Get the master rules for the combination as a base
        $masterRules = $this->targetProbeSetRuleModel->getRulesForSelectedProbeSetAndCombination($clientProbeSet['probe_set_id'], $clientProbeSet['combination_id']);

        // Replace master rules with client-specific rules
        foreach ($masterRules as &$masterRule) {
            foreach ($clientRules as $clientRule) {
                if ($masterRule['phase_id'] === $clientRule['phase_id']) {
                    $masterRule['rules'] = $clientRule['rules'];
                    break;
                }
            }
        }

        $rulesHtml = $this->renderRules($masterRules, $masterProbeSet['name']);

        // Render the probe set details with the modified inputs and rules

        $probeSetHtml = view('ClientProgram/probe_set_details_view', [
            'probeSet' => $masterProbeSet,
            'inputsHtml' => $inputsHtml,
            'inputs' => $inputs,
            'infoString' => $infoString
        ]);

        $html = $probeSetHtml . $rulesHtml;

        return $this->response->setJSON(['status' => 'success', 'html' => $html]);
    }

    public function update_client_existing_probe_set()
    {
        $clientProbeSetId = $this->request->getPost('update_form_client_probe_set_id');
        $goalId = $this->request->getPost('update_form_goal_id');
        $clientId = $this->request->getPost('update_form_client_id');
        $probeSetId = $this->request->getPost('update_form_probe_set_id');
        $combinationId = $this->request->getPost('update_form_combination_id');
        $current_user_id = auth()->user()->id;

        // Validate required fields
        if (!$clientProbeSetId || !$goalId || !$clientId || !$probeSetId || !$combinationId) {
            log_message('error', 'Client->ProbeSet->Updating rules. Required fields are missing.');
            return $this->response->setJSON(['status' => 'error', 'message' => 'Required fields are missing.']);
        }

        // Get existing client probe set
        $existingClientProbeSet = $this->clientProbeSetModel
            ->where('id', $clientProbeSetId)
            ->first();

        if (!$existingClientProbeSet) {
            log_message('error', 'Client->ProbeSet->Updating rules. This probe set does not exist.');
            return $this->response->setJSON(['status' => 'error', 'message' => 'This probe set does not exist.']);
        }

        try {
            // Start a transaction
            $this->db->transException(true)->transStart();

            // Decode the existing inputs
            $existingInputs = json_decode($existingClientProbeSet['inputs'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Client->ProbeSet->Updating rules. Invalid JSON format in client probe set inputs.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid JSON format in client probe set inputs.']);
            }

            // Update inputs based on the type of probe set
            $updatedInputs = $existingInputs; // Start with the existing inputs

            switch ($existingInputs['type']) {
                case 'count':
                    $minValue = $this->request->getPost('min');
                    $maxValue = $this->request->getPost('max');
                    if (isset($minValue) && isset($maxValue)) {
                        $updatedInputs['range']['min'] = $minValue;
                        $updatedInputs['range']['max'] = $maxValue;
                        $updatedInputs['key'] = $maxValue; // Update key with max value
                    }
                    break;

                case 'duration':
                    $durationValues = $this->request->getPost('duration_values');
                    $durationLabels = $this->request->getPost('duration_labels');
                    if (!empty($durationValues) && !empty($durationLabels)) {
                        $choices = [];
                        foreach ($durationValues as $index => $value) {
                            $choices[] = [
                                'value' => $value,
                                'label' => $durationLabels[$index] ?? ''
                            ];
                        }
                        $updatedInputs['choices'] = $choices;
                        $updatedInputs['key'] = end($durationValues); // Update key with max duration value
                    }
                    break;
                case 'prompt_level':
                    $promptLevelValues = $this->request->getPost('prompt_level_values');
                    $promptLevelLabels = $this->request->getPost('prompt_level_labels');
                    if (!empty($promptLevelValues) && !empty($promptLevelLabels)) {
                        $choices = [];
                        foreach ($promptLevelValues as $index => $value) {
                            $choices[] = [
                                'value' => $value,
                                'label' => $promptLevelLabels[$index] ?? ''
                            ];
                        }
                        $updatedInputs['choices'] = $choices;
                        $updatedInputs['key'] = end($promptLevelValues); // Update key with max duration value
                    }
                    break;
                case 'percentage_yes_no':
                    $c_key = $this->request->getPost('c_key');
                    if ($c_key != '' && is_numeric($c_key) && $c_key >= 0 && $c_key <= 100) {
                        $updatedInputs['key'] = $c_key;
                    }
                    break;


                // Add more cases if other types need specific processing
                default:
                    // No changes for other types, use existing inputs as-is
                    break;
            }

            // Update the probe set inputs in the database
            $this->clientProbeSetModel
                ->where('id', $clientProbeSetId)
                ->set('inputs', json_encode($updatedInputs))
                ->update();

            // Now update the rules for each phase
            $clientRules = $this->clientProbeSetRuleModel
                ->where('client_probe_set_id', $clientProbeSetId)
                ->findAll();

            foreach ($clientRules as $clientRule) {
                $phaseId = $clientRule['phase_id'];
                $jsonRules = json_decode($clientRule['rules'], true);

                // Update rule fields based on form data for this phase
                switch ($existingInputs['type']) {
                    case 'yes_no':
                        $jsonRules = $this->updateYesNoRules($jsonRules, $phaseId);
                        break;
                    case 'traffic_light':
                        $jsonRules = $this->updateTrafficLightRules($jsonRules, $phaseId);
                        break;
                    case 'prompt_level':
                        $jsonRules = $this->updatePromptLevelRules($jsonRules, $phaseId);
                        break;
                    case 'count':
                        $jsonRules = $this->updateCountRules($jsonRules, $phaseId);
                        break;
                    case 'duration':
                        $jsonRules = $this->updateDurationRules($jsonRules, $phaseId);
                        break;
                    case 'percentage_yes_no':
                        $jsonRules = $this->updatePercentageRules($jsonRules, $phaseId);
                        break;
                    default:
                        break;
                }

                log_message('info', 'Before update: ' . json_encode($clientRule));
                log_message('info', 'After update: ' . json_encode($jsonRules));
                // Save the updated rules
                $this->clientProbeSetRuleModel
                    ->where('id', $clientRule['id'])
                    ->set('rules', json_encode($jsonRules))
                    ->update();
                $updated = $this->clientProbeSetRuleModel
                    ->where('id', $clientRule['id'])
                    ->set('rules', json_encode($jsonRules))
                    ->update();

                if ($updated) {
                    log_message('info', 'Successfully updated rule for client_probe_set_rule_id: ' . $clientRule['id']);
                } else {
                    log_message('error', 'Failed to update rule for client_probe_set_rule_id: ' . $clientRule['id']);
                }
            }

            // Complete the transaction, which will commit if everything is OK
            $this->db->transComplete();

            // Check if the transaction was successful
            if ($this->db->transStatus() === FALSE) {
                log_message('error', 'Client->ProbeSet->Updating rules. Database transaction failed.');
                return $this->response->setJSON(['status' => 'error', 'message' => 'Database transaction failed.']);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Probe set and rules updated successfully.']);
        } catch (\Exception $e) {
            // Log the error and rollback the transaction
            log_message('error', 'Client->ProbeSet->Updating rules. Exception caught: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * Activate a client probe set and deactivate the current active one.
     */
    public function activateClientProbeSet()
    {
        $clientProbeSetId = $this->request->getPost('client_probe_set_id');
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        if (!$clientProbeSetId || !$goalId || !$clientId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Required parameters are missing.']);
        }

        try {
            // Start a transaction to ensure data integrity
            $this->db->transException(true)->transStart();

            // Deactivate the currently active probe set
            $this->clientProbeSetModel
                ->where('client_id', $clientId)
                ->where('goal_id', $goalId)
                ->where('is_active', 1)
                ->set('is_active', 0)
                ->update();

            // Activate the selected probe set
            $this->clientProbeSetModel
                ->where('id', $clientProbeSetId)
                ->set('is_active', 1)
                ->update();

            // Commit the transaction
            $this->db->transComplete();

            // Fetch the updated list of probe sets
            $probeSets = $this->clientProbeSetModel->getProbeSetsWithDetails($clientId, $goalId);

            // Generate the HTML for the updated table
            $html = view('ClientProgram/client_existing_probe_sets_table', [
                'probeSets' => $probeSets
            ]);

            return $this->response->setJSON(['status' => 'success', 'html' => $html]);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            $this->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while activating the probe set.']);
        }
    }

    /**
     * Delete a client probe set and deactivate the current active one.
     */
    public function deleteClientProbeSet()
    {
        $clientProbeSetId = $this->request->getPost('client_probe_set_id');
        $goalId = $this->request->getPost('goal_id');
        $clientId = $this->request->getPost('client_id');

        if (!$clientProbeSetId || !$goalId || !$clientId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Required parameters are missing.']);
        }

        try {
            // Start a transaction to ensure data integrity
            $isUsed = $this->clientProbeSetModel->isUsed($clientProbeSetId, $goalId, $clientId);
            if ($isUsed) {
                $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this probe set and goal for selected client', [], []);
                return $this->response->setJSON($response);
            }

            $this->db->transException(true)->transStart();

            // Check if the probe set being deleted is the active one
            $isActive = $this->clientProbeSetModel
                ->where('id', $clientProbeSetId)
                ->where('is_active', 1)
                ->first();

            // Delete the probe set
            $this->clientProbeSetModel->delete($clientProbeSetId);

            // If the deleted probe set was active, make the last created one active
            if ($isActive) {
                $lastCreatedProbeSet = $this->clientProbeSetModel
                    ->where('client_id', $clientId)
                    ->where('goal_id', $goalId)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if ($lastCreatedProbeSet) {
                    $this->clientProbeSetModel
                        ->where('id', $lastCreatedProbeSet['id'])
                        ->set('is_active', 1)
                        ->update();
                }
            }

            // Commit the transaction
            $this->db->transComplete();

            // Fetch the updated list of probe sets
            $probeSets = $this->clientProbeSetModel->getProbeSetsWithDetails($clientId, $goalId);

            // Generate the HTML for the updated table (including table structure)
            $html = view('ClientProgram/client_existing_probe_sets_table', [
                'probeSets' => $probeSets
            ]);

            return $this->response->setJSON(['status' => 'success', 'html' => $html]);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            $this->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while deleting the probe set.']);
        }
    }


    /************************************************************************************** */
}
