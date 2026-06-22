<?php

namespace App\Services\ProbeSetProcessing;

use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientSessions\DailySessionDataProcessedModel;
use App\Models\ClientProgram\ClientProgramModel;
use App\Models\ClientProgram\ClientProgramTargetOverridesModel;
use App\Models\ClientProgram\ClientProbeSetModel;

use App\Models\ClientProgram\ClientTargetsRetainedModel;
use App\Models\ClientProgram\ClientTargetsDOIModel;
use App\Models\ClientProgram\ClientProgramChangeAlertModel;

use App\Models\ClientProgram\ClientStimulusChainModel;

class StimulusProcessingService implements ProbeSetProcessingServiceInterface
{
    protected $collectionModel;
    protected $processedModel;
    protected $clientProgramModel;
    protected $targetOverridesModel;
    protected $clientProbeSetModel;

    protected $clientTargetsRetainedModel;
    protected $clientTargetsDOIModel;
    protected $clientProgramChangeAlertModel;

    protected $clientStimulusChainModel;

    public function __construct()
    {
        $this->collectionModel = new DailySessionDataCollectionModel();
        $this->processedModel = new DailySessionDataProcessedModel();
        $this->clientProgramModel = new ClientProgramModel();
        $this->targetOverridesModel = new ClientProgramTargetOverridesModel();
        $this->clientProbeSetModel = new ClientProbeSetModel();

        $this->clientTargetsRetainedModel = new ClientTargetsRetainedModel();
        $this->clientTargetsDOIModel = new ClientTargetsDOIModel();
        $this->clientProgramChangeAlertModel = new ClientProgramChangeAlertModel();

        $this->clientStimulusChainModel = new ClientStimulusChainModel();
    }

    public function process(array $data, array $collectedData): array
    {

        // Decode rules and inputs
        $rule = json_decode($collectedData['rule']['default_rule'], true);
        $result = $collectedData['result'];

        // Check if rule_end is true
        if ($rule['rule_end']) {
            return [
                'success' => false,
                'message' => 'Target has already been retained; no further processing needed.',
            ];
        }

        if ($data['current_phase_id'] == 1) {
            return $this->performBaselineProcessing($data, $rule, $result);
        }

        // ✅ Recompile and update collectedData (if needed)
        $chainRow = $this->clientStimulusChainModel
            ->where('target_id', $data['target_id'])
            ->first();

        $method = strtolower($chainRow->method ?? '');

        if (in_array($method, ['forward', 'backward'], true)) {
            return $this->performForwardBackwardProcessing($data, $rule, $result);
        }


        if ($method === 'total_task') {
            $ruleOverrides = json_decode($chainRow->rule_override ?? '{}', true);
            $methodRule = $ruleOverrides[$method] ?? [];
            $consecutiveCriteria = $methodRule['overall_mastery']['value'] ?? null;
            $check = $methodRule['overall_mastery']['check'] ?? null;
            return $this->performTotalTaskProcessing($data, $rule, $check, $result, $consecutiveCriteria);
        }

        return [
            'success' => false,
            'message' => 'Processing issue in Stimulus probe.',
        ];
    }

    protected function performBaselineProcessing($data, $rule, $result)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isDOIRequired = false;

        // Update phase and possibly trigger a program change
        // Check: all three attempts must be 100%
        $allAttemptsArePerfect = count($result) === 3 &&
            $result[0] == 100 &&
            $result[1] == 100 &&
            $result[2] == 100;

        // Determine next phase
        $nextPhaseId = $allAttemptsArePerfect ? $rule['p_phase_id'] : $rule['f_phase_id'];


        // Save the processed data
        $processedData = [
            'next_frame_set_no' => null,
        ];

        $isRetained = ($nextPhaseId == 4) ? true : false;

        $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, $isDOIRequired);
        return [
            'success' => true,
            'message' => 'Count probe processed successfully.',
        ];
    }
    protected function performForwardBackwardProcessing($data, $rule, $result)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isDOIRequired = true;

        // Update phase and possibly trigger a program change
        $nextPhaseId = ($result[0] == 100) ? $rule['p_phase_id'] : $rule['f_phase_id'];


        // Save the processed data
        $processedData = [
            'next_frame_set_no' => null,
        ];

        $isRetained = ($nextPhaseId == 4) ? true : false;

        $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, $isDOIRequired);
        return [
            'success' => true,
            'message' => 'Count probe processed successfully.',
        ];
    }

    protected function performTotalTaskProcessing($data, $rule, $key, $result, $consecutiveCriteria)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isDOIRequired = true;
        // Check if the number of consecutive days matches the criteria
        $consecutiveDaysMet = $this->collectionModel->countConsecutiveDays(
            $data['client_id'],
            $data['target_id'],
            $data['client_probe_set_id'],
            $data['current_phase_id']
        );

        if ($consecutiveDaysMet < $consecutiveCriteria) {
            // Not enough consecutive days, continue in the current phase
            $nextPhaseId = $data['current_phase_id'];
            $isProgramChanged = false;
        } else {
            // Process according to the same_day_check rule
            $isCriteriaMet = $this->collectionModel->countDifferentDayCriteriaMetStimulusProbeTotalTask(
                $data['client_id'],
                $data['target_id'],
                $data['client_probe_set_id'],
                $data['current_phase_id'],  // Pass session date for same day check
                $key,
                $result,
                $consecutiveCriteria
            );

            // Update phase and possibly trigger a program change
            $nextPhaseId = $isCriteriaMet ? $rule['p_phase_id'] : $rule['f_phase_id'];
        }

        // Save the processed data
        $processedData = [
            'next_frame_set_no' => null,
        ];

        $isRetained = ($nextPhaseId == 4) ? true : false;

        $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, $isDOIRequired);
        return [
            'success' => true,
            'message' => 'Stimulus probe processed successfully.',
        ];
    }


    protected function performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, $isDOIRequired)
    {
        $processedDataId = $this->processedModel->saveProcessedData($data, $processedData, $nextPhaseId, $isProgramChanged);
        $this->collectionModel->update($data['id'], ['is_processed' => true, 'processed_at' => currentDate('Y-m-d H:i:s'), 'processed_by' => auth()->user()->id]);

        // If the target is retained
        if ($isRetained) {
            $retainedData = [
                'processed_data_id' => $processedDataId,
                'collection_id' => $data['id'],
                'session_id' => $data['session_id'],
                'session_date' => $data['session_date'],
                'client_id' => $data['client_id'],
                'domain_id' => $data['domain_id'],
                'goal_id' => $data['goal_id'],
                'target_id' => $data['target_id'],
                'client_probe_set_id' => $data['client_probe_set_id'],
                'created_by' => auth()->user()->id
            ];
            $this->clientTargetsRetainedModel->saveRetainedTarget($retainedData);
        }
        // If DOI is required to be calculated and saved
        if ($isDOIRequired) {
            $doiValue = $this->calculateDOI($data);

            if (!is_null($doiValue)) {
                $doiData = [
                    'processed_data_id' => $processedDataId,
                    'collection_id' => $data['id'],
                    'session_id' => $data['session_id'],
                    'session_date' => $data['session_date'],
                    'client_id' => $data['client_id'],
                    'domain_id' => $data['domain_id'],
                    'goal_id' => $data['goal_id'],
                    'target_id' => $data['target_id'],
                    'client_probe_set_id' => $data['client_probe_set_id'],
                    'doi_value' => $doiValue,
                    'created_by' => auth()->user()->id
                ];
                $this->clientTargetsDOIModel->saveDOITarget($doiData);
            }
        }
    }
    private function calculateDOI(array $data)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();

        $clientId     = $data['client_id'];
        $targetId     = $data['target_id'];
        $currentDate  = $data['session_date'];
        $collectionId = $data['id'];

        log_message('debug', "[DOI] Starting calculation for client_id=$clientId, target_id=$targetId, session_date=$currentDate, collection_id=$collectionId");

        // 1. Get current session step data
        $currentSteps = $stepSessionModel
            ->asArray()
            ->where('collection_id', $collectionId)
            ->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('method !=', 'baseline')
            ->findAll();

        log_message('debug', '[DOI] Current steps count: ' . count($currentSteps));

        if (empty($currentSteps)) {
            log_message('debug', '[DOI] No current step data found, skipping DOI.');
            return null;
        }

        // 2. Get most recent previous session date (non-baseline) Need to check if privous session target is processed or not
        $prevSession = $stepSessionModel
            ->asArray()
            ->select('session_date')
            ->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('method !=', 'baseline')
            ->where('session_date <', $currentDate)
            ->orderBy('session_date', 'DESC')
            ->limit(1)
            ->first();

        if (!$prevSession) {
            log_message('debug', '[DOI] No previous session found before ' . $currentDate);
            return null;
        }

        log_message('debug', '[DOI] Found previous session date: ' . $prevSession['session_date']);

        // 3. Get previous session step data
        $previousSteps = $stepSessionModel
            ->asArray()
            ->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('session_date', $prevSession['session_date'])
            ->where('method !=', 'baseline')
            ->findAll();

        log_message('debug', '[DOI] Previous steps count: ' . count($previousSteps));

        if (empty($previousSteps)) {
            log_message('debug', '[DOI] No step data found for previous session.');
            return null;
        }

        // 4. Map previous step results by step_id
        $prevMap = [];
        foreach ($previousSteps as $step) {
            $prevMap[$step['step_id']] = strtoupper($step['input_result']);
        }
        log_message('debug', '[DOI] Previous step result map: ' . json_encode($prevMap));

        // 5. Calculate DOI
        $totalDoi = 0;
        foreach ($currentSteps as $step) {
            $stepId   = $step['step_id'];
            $current  = strtoupper($step['input_result']);
            $previous = $prevMap[$stepId] ?? null;

            if (!$previous) {
                log_message('debug', "[DOI] No previous value for step_id=$stepId");
                continue;
            }

            $rule = "{$previous}->{$current}";

            if ($previous == 'FP' && $current == 'PP') {
                $totalDoi += 1;
                log_message('debug', "[DOI] Step $stepId matched rule FP->PP (DOI+1)");
            } elseif ($previous == 'PP' && $current == 'IND') {
                $totalDoi += 1;
                log_message('debug', "[DOI] Step $stepId matched rule PP->IND (DOI+1)");
            } elseif ($previous == 'FP' && $current == 'IND') {
                $totalDoi += 2;
                log_message('debug', "[DOI] Step $stepId matched rule FP->IND (DOI+2)");
            } else {
                log_message('debug', "[DOI] Step $stepId transition $rule did not match any DOI rule.");
            }
        }

        log_message('debug', "[DOI] Final calculated DOI = $totalDoi");

        return $totalDoi > 0 ? $totalDoi : null;
    }
}
