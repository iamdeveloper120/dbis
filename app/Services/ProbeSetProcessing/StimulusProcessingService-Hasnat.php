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
        } else {
            $chainRow = $this->clientStimulusChainModel
                ->where('target_id', $data['target_id'])
                ->first();
            $method = $chainRow->method; // e.g., "forward", "total_task"
            if ($method == 'forward' || $method == 'backward') {
                return $this->performForwardBackwardProcessing($data, $rule, $result);
            } else if ($method == 'total_task') {
                // Decode rule_override JSON
                $ruleOverrides = json_decode($chainRow->rule_override ?? '{}', true);

                // Safely access the relevant method's rule
                $methodRule = $ruleOverrides[$method] ?? [];

                // Now get values depending on method
                $consecutiveCriteria = $methodRule['overall_mastery']['value'] ?? null;
                $check = $methodRule['overall_mastery']['check'] ?? null;
                return $this->performTotalTaskProcessing($data, $rule, $check, $result, $consecutiveCriteria);
            }
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
    protected function performForwardBackwardProcessing($data, $rule, $result)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isDOIRequired = false;

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
            // Update phase and possibly trigger a program change
            $nextPhaseId = $isCriteriaMet ? $rule['p_phase_id'] : $rule['f_phase_id'];
        }

        // Save the processed data
        $processedData = [
            'next_frame_set_no' => null,
        ];

        $isRetained = ($nextPhaseId == 4) ? true : false;
        $isDOIRequired = false;
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
    }
}
