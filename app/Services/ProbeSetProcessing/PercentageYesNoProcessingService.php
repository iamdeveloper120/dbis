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

class PercentageYesNoProcessingService implements ProbeSetProcessingServiceInterface
{
    protected $collectionModel;
    protected $processedModel;
    protected $clientProgramModel;
    protected $targetOverridesModel;
    protected $clientProbeSetModel;

    protected $clientTargetsRetainedModel;
    protected $clientTargetsDOIModel;
    protected $clientProgramChangeAlertModel;

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
    }

    public function process(array $data, array $collectedData): array
    {

        // Decode rules and inputs
        $rule = json_decode($collectedData['rule']['default_rule'], true);
        $key = $collectedData['inputs']['key'];
        $result = $collectedData['result'];

        // Check if rule_end is true
        if ($rule['rule_end']) {
            return [
                'success' => false,
                'message' => 'Target has already been retained; no further processing needed.',
            ];
        }

        // Apply override criteria
        $overrideCriteria = $this->targetOverridesModel->getConsecutiveCriteriaOverride(
            $data['client_id'],
            $data['domain_id'],
            $data['goal_id'],
            $data['target_id'],
            $data['client_probe_set_id'],
            $data['current_phase_id']
        );
        $consecutiveCriteria = $overrideCriteria ?: $rule['consecutive_criteria'];

        // Perform frame checks if required

        return $this->performWithoutFrame($data, $rule, $key, $result, $consecutiveCriteria);
    }

    protected function performWithoutFrame($data, $rule, $key, $result, $consecutiveCriteria)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isProgramChangeRequired = $rule['program_change'];
        $session_limit = $rule['session_limit'];

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
            $isCriteriaMet = $this->collectionModel->countDifferentDayCriteriaMetPercentageYesNoProbe(
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
            if ($isProgramChangeRequired) {
                if (!$isCriteriaMet) {
                    $session_limit = $session_limit ?? $consecutiveCriteria;
                    if ($consecutiveDaysMet >= $session_limit) {
                        $isProgramChanged = true;
                    }
                }
            }
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
            'message' => 'Count probe processed successfully.',
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

        // If a program change alert needs to be triggered
        if ($isProgramChanged) {
            $alertData = [
                'processed_data_id' => $processedDataId,
                'collection_id' => $data['id'],
                'session_id' => $data['session_id'],
                'session_date' => $data['session_date'],
                'client_id' => $data['client_id'],
                'domain_id' => $data['domain_id'],
                'goal_id' => $data['goal_id'],
                'target_id' => $data['target_id'],
                'client_probe_set_id' => $data['client_probe_set_id'],
                'is_alert_handled' => 0,
                'is_change_made' => 0,
                'created_by' => auth()->user()->id
            ];
            $this->clientProgramChangeAlertModel->saveChangeAlert($alertData);
        }
 
    }
}
