<?php

namespace App\Controllers\MasterProgram;

use App\Controllers\AdminController;
use App\Models\MasterProgram\TargetPhaseModel;
use App\Models\MasterProgram\TargetPhaseCombinationModel;
use App\Models\MasterProgram\TargetProbeSetModel;
use App\Models\MasterProgram\TargetProbeSetRuleModel;

class TargetPhasesRulesSetupController extends AdminController
{
    public function index()
    {
        $phaseModel = new TargetPhaseModel();
        $combinationModel = new TargetPhaseCombinationModel();
        $probeSetModel = new TargetProbeSetModel();

        $data = [
            'targetPhases' => $phaseModel->findAll(),
            'phaseCombinations' => $combinationModel->getCombinationsWithPhaseNames(),
            'probeSets' => $probeSetModel->getAllProbeSets(),
            'page_title' => 'Phases & Rules Setup',
        ];

        return view('MasterProgram/phases-rules-setup', $data);
    }

    public function getProbeSetDetails($probeSetId)
    {
        $probeSetModel = new TargetProbeSetModel();

        // Fetch the selected probe set details
        $probeSet = $probeSetModel->getProbeSetById($probeSetId);
        if (!$probeSet) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Probe set not found']);
        }

        // Decode the JSON inputs for display
        $inputs = json_decode($probeSet['inputs'], true);

        // Check for JSON errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format');
        }

        // Render inputs HTML
        $inputsHtml = $this->renderInputs($inputs);

        // Fetch rules related to the probe set
        $ruleModel = new TargetProbeSetRuleModel();
        $rules = $ruleModel->getRulesWithDetails($probeSetId);

        // Group rules by combination
        $groupedRules = $this->groupRulesByCombination($rules);

        // Render rules HTML
        $rulesHtml = $this->renderRules($groupedRules, $probeSet['name']);

        // Render the HTML view for probe set details
        $probeSetHtml = 'Unknown Input Format';
        if ($inputs['type'] == 'percentage_yes_no') {
            $probeSetHtml = view('MasterProgram/percentage_probe_set_details', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        }
        if ($inputs['type'] == 'stimulus_program') {
            $probeSetHtml = view('MasterProgram/stimulus_program_probe_set_details', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        } else {
            $probeSetHtml = view('MasterProgram/probe_set_details', [
                'probeSet' => $probeSet,
                'inputsHtml' => $inputsHtml,
                'inputs' =>  $inputs,
            ]);
        }


        // Send HTML as response
        return $this->response->setJSON([
            'probeSetHtml' => $probeSetHtml,
            'rulesHtml' => $rulesHtml,
        ]);
    }

    private function renderInputs(array $inputs)
    {
        // Check if inputs have the correct type key
        if (!isset($inputs['type'])) {
            throw new \Exception('Invalid input structure: Missing type key');
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
        $html = "<div class='col-lg-4'><textarea id='additional_info' name='additional_info' placeholder='Trial data' rows='3' class='form-control mb-2'></textarea></div>";
        $html .= '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';
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



    private function groupRulesByCombination(array $rules): array
    {
        $groupedRules = [];
        foreach ($rules as $rule) {
            $combinationName = $rule['combination_name'];
            $groupedRules[$combinationName][] = $rule;
        }
        return $groupedRules;
    }

    private function renderRules(array $groupedRules, string $probeSetName): string
    {
        $rulesHtml = '';
        foreach ($groupedRules as $combinationName => $rules) {
            // Process each rule to extract JSON data
            foreach ($rules as &$rule) {
                $rule['json_rules'] = json_decode($rule['rules'], true);
                // Map phase IDs to names
                $rule['p_phase_name'] = get_phase_name($rule['json_rules']['p_phase_id'] ?? null);
                $rule['f_phase_name'] = get_phase_name($rule['json_rules']['f_phase_id'] ?? null);
            }

            // Choose view based on probe set name
            switch ($probeSetName) {
                case 'Yes/No Probes':
                    $rulesHtml .= view('MasterProgram/yes_no_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Traffic Light Probes':
                    $rulesHtml .= view('MasterProgram/traffic_light_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Prompt Level Probes':
                    $rulesHtml .= view('MasterProgram/prompt_level_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Count Probes':
                    $rulesHtml .= view('MasterProgram/count_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Duration Probes':
                    $rulesHtml .= view('MasterProgram/duration_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Percentage Yes/No Probes':
                    $rulesHtml .= view('MasterProgram/percentage_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                case 'Stimulus Program Probes':
                    $rulesHtml .= view('MasterProgram/stimulus_rules', [
                        'combinationName' => $combinationName,
                        'rules' => $rules,
                    ]);
                    break;
                // Add more cases as needed for different probe sets
                default:
                    $rulesHtml .= '<p>No rules available for this probe set</p>';
                    break;
            }
        }

        return $rulesHtml;
    }
}
