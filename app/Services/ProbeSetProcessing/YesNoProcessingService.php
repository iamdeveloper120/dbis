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


class YesNoProcessingService implements ProbeSetProcessingServiceInterface
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
        $currentFrame = $collectedData['rule']['frame_set_no'];

        log_message('debug', '---------------------------------------------');
        log_message('debug', 'Start of process method variables');
        log_message('debug', 'Data: ' . json_encode($data));
        log_message('debug', 'Rule: ' . json_encode($rule));
        log_message('debug', 'Key: ' . json_encode($key));
        log_message('debug', 'result: ' . json_encode($result));
        log_message('debug', 'Current Frame: ' . json_encode($currentFrame));

        log_message('debug', 'End of process method variables');
        log_message('debug', '---------------------------------------------');

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

        log_message('debug', 'Override Criteria: ' .  $overrideCriteria . ' Consecutive Criteria' . $consecutiveCriteria);
        log_message('debug', '---------------------------------------------');

        // Perform frame checks if required
        if ($rule['frame_check']) {
            log_message('debug', 'Perform FrameCheck');
            log_message('debug', '---------------------------------------------');
            return $this->performFrameCheck($data, $rule, $key, $result, $consecutiveCriteria, $currentFrame);
        } else {
            log_message('debug', 'Perform Without Frame');
            log_message('debug', '---------------------------------------------');
            return $this->performWithoutFrame($data, $rule, $key, $result, $consecutiveCriteria);
        }
    }

    protected function performWithoutFrame($data, $rule, $key, $result, $consecutiveCriteria)
    {
        // Process according to the same_day_check rule
        $nextPhaseId = null;
        $isProgramChanged = false;
        $isProgramChangeRequired = $rule['program_change'];
        $session_limit = $rule['session_limit'];

        if ($rule['same_day_check']) {
            $isCriteriaMet =  $this->collectionModel->countSameDayCriteriaMet($key, $result, $consecutiveCriteria);

            $nextPhaseId = $isCriteriaMet ? $rule['p_phase_id'] : $rule['f_phase_id'];

            if ($isProgramChangeRequired) {
                if (!$isCriteriaMet) {
                    $isProgramChanged = true;
                }
            }

            log_message('debug', 'same_day_check');
        } else {
            log_message('debug', 'different day check');
            // Check if the number of consecutive days matches the criteria
            $consecutiveDaysMet = $this->collectionModel->countConsecutiveDays(
                $data['client_id'],
                $data['target_id'],
                $data['client_probe_set_id'],
                $data['current_phase_id']
            );
            log_message('debug', 'consecutiveDaysMet ' . $consecutiveDaysMet);
            log_message('debug', 'consecutiveCriteria ' . $consecutiveCriteria);

            if ($consecutiveDaysMet < $consecutiveCriteria) {
                // Not enough consecutive days, continue in the current phase
                $nextPhaseId = $data['current_phase_id'];
                $isProgramChanged = false;
                log_message('debug', 'Still data is not enough to perform match criteria');
            } else {
                log_message('debug', 'Enough data to check for next phase and program change.');
                // Process according to the day_check rule
                $isCriteriaMet = $this->collectionModel->countDifferentDayCriteriaMet(
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
                log_message('debug', 'isCriteriaMet ' . $isCriteriaMet ? 'true' : 'false');
                log_message('debug', 'nextPhaseId ' . $nextPhaseId);
                if ($isProgramChangeRequired) {
                    if (!$isCriteriaMet) {
                        $session_limit = $session_limit ?? $consecutiveCriteria;
                        if ($consecutiveDaysMet >= $session_limit) {
                            $isProgramChanged = true;
                        }
                    }
                }
            }
        }

        // check if next phase has frame check then we need to set next frame no to 1 in order to maintain compatibility
        $nextPhaseProbeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($data['client_probe_set_id'], $nextPhaseId);
        $nextPhaseRule = json_decode($nextPhaseProbeSetDetails['rule_data'], true);

        // Save the processed data
        $processedData = [
            'next_frame_set_no' => $nextPhaseRule['frame_check'] ? 1 : null,
        ];


        // Save processed data
        $isRetained = ($nextPhaseId == 4) ? true : false;
        $isDOICalculated = false;
        $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, $isDOICalculated);
        return [
            'success' => true,
            'message' => 'Yes/No probe processed successfully.',
        ];
    }

    protected function performFrameCheck($data, $rule, $key, $result, $consecutiveCriteria, $currentFrame)
    {
        // Implement frame-specific checks based on the current frame number
        if ($currentFrame == 1) {
            return $this->frame1Check($data, $rule, $key, $result, $consecutiveCriteria);
        } elseif ($currentFrame == 2) {
            return $this->frame2Check($data, $rule, $key, $result, $consecutiveCriteria);
        }

        return [
            'success' => false,
            'message' => 'Invalid frame set number.',
        ];
    }

    protected function frame1Check($data, $rule, $key, $result, $consecutiveCriteria)
    {

        $clientId = $data['client_id'];
        $targetId = $data['target_id'];
        $probeSetId = $data['client_probe_set_id'];
        $currentPhaseId = $data['current_phase_id'];

        // Count consecutive days in Frame 1
        $consecutiveDays = $this->collectionModel->countFrame1Days($clientId, $targetId, $probeSetId, $currentPhaseId);

        if ($consecutiveDays < $consecutiveCriteria) {
            // Criteria not met; continue with the same phase and frame
            $processedData = [
                'next_frame_set_no' => 1,  // frame 1
            ];

            $this->performProcessing($data, $processedData, $currentPhaseId, false, false, false);
            return [
                'success' => true,
                'message' => 'Frame 1: Continue in the same phase.',
            ];
        }

        // Check if all results are 'N' for Frame 1
        $allResultsNo = $this->collectionModel->areAllResultsNoForFrame1($clientId, $targetId, $probeSetId, $currentPhaseId, $result, $consecutiveCriteria);

        if ($allResultsNo) {
            // If all results are 'N', remain in Frame 1 with the same phase
            $processedData = [
                'next_frame_set_no' => 1,  // Stay in Frame 1 
            ];

            $this->performProcessing($data, $processedData, $currentPhaseId, true, false, false);
            return [
                'success' => true,
                'message' => 'Frame 1: Program change needed due to all results being "N".',
            ];
        }

        // Check if all results are 'Y' for Frame 1
        $allResultsYes = $this->collectionModel->areAllResultsYesForFrame1($clientId, $targetId, $probeSetId, $currentPhaseId, $result, $consecutiveCriteria);
        if ($allResultsYes) {
            // If all results are 'N', remain in Frame 1 with the same phase
            $processedData = [
                'next_frame_set_no' => null,
            ];

            $isRetained = ($rule['p_phase_id'] == 4) ? true : false;
            $this->performProcessing($data, $processedData, $rule['p_phase_id'], false, $isRetained, false);

            return [
                'success' => true,
                'message' => 'Target Retained.',
            ];
        }

        // If results are mixed, move to Frame 2
        $processedData = [
            'next_frame_set_no' => 2,  // Move to Frame 2 
        ];


        $this->performProcessing($data, $processedData, $currentPhaseId, false, false, false);

        return [
            'success' => true,
            'message' => 'Frame 1: Moving to Frame 2.',
        ];
    }


    protected function frame2Check($data, $rule, $key, $result, $consecutiveCriteria)
    {
        $nextPhaseId = null;
        $isProgramChanged = false;
        $currentPhaseId = $data['current_phase_id'];
        $clientId = $data['client_id'];
        $targetId = $data['target_id'];
        $probeSetId = $data['client_probe_set_id'];

        // 1. Handle 'N' Result
        if (in_array('N', $result)) {
            // Trigger program change alert
            $nextPhaseId = $currentPhaseId;
            $isProgramChanged = true;
            $nextFrameSetNo = 1;

            // Save the processed data and return
            $processedData = [
                'next_frame_set_no' => $nextFrameSetNo
            ];

            $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, false, false);
            return [
                'success' => true,
                'message' => 'Frame 2 - Program change triggered due to "No" result.',
            ];
        }

        // 2. Check if Criteria Are Met Across Frame 1 and Frame 2
        $isCriteriaMet = $this->collectionModel->checkIfCriteriaMetAcrossFramesWithPhaseCheck(
            $clientId,
            $targetId,
            $probeSetId,
            $currentPhaseId,
            $key,
            $result,
            $consecutiveCriteria
        );

        if ($isCriteriaMet) {
            // Criteria met, proceed to the next phase
            $nextPhaseId = $rule['p_phase_id'];
            $nextFrameSetNo = $rule['frame_check'] ? 1 : null; // Reset frame set if next phase has frame check
            $isProgramChanged = false;
            $processedData = [
                'next_frame_set_no' => $nextFrameSetNo,
            ];

            $isRetained = ($nextPhaseId == 4) ? true : false;
            $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, $isRetained, false);
            return [
                'success' => true,
                'message' => 'Frame 2 - Criteria met, proceeding to the next phase.',
            ];
        } else {
            // 3. Criteria Not Met, Check Consecutive Days in Frame 2
            $consecutiveFrame2Days = $this->collectionModel->countFrame2Days($clientId, $targetId, $probeSetId, $currentPhaseId);

            if ($consecutiveFrame2Days >= $consecutiveCriteria) {
                // Revert to Frame 1 and trigger a program change alert
                $nextPhaseId = $currentPhaseId;
                $nextFrameSetNo = 1;
                $isProgramChanged = true;
                $processedData = [
                    'next_frame_set_no' => $nextFrameSetNo
                ];

                $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, false, false);
                return [
                    'success' => true,
                    'message' => 'Frame 2 - Criteria not met, reverting to Frame 1 and triggering program change.',
                ];
            } else {
                // Stay in Frame 2
                $nextPhaseId = $currentPhaseId;
                $nextFrameSetNo = 2;
                $isProgramChanged = false;
                $processedData = [
                    'next_frame_set_no' => $nextFrameSetNo,
                ];

                $this->performProcessing($data, $processedData, $nextPhaseId, $isProgramChanged, false, false);
                return [
                    'success' => true,
                    'message' => 'Frame 2 - Criteria not met, staying in Frame 2.',
                    'processedData' => $processedData,
                ];
            }
        }
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

        // If DOI is required to be calculated and saved in Yes/No probe set we do not calculate degrees of independence
        $isDOIRequired = $isDOIRequired;
        /*$calculatedDOI = null;
        if ($isDOICalculated) {
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
                'degree_of_instruction' => $calculatedDOI,
                'created_by' => auth()->user()->id
            ];
            $this->clientTargetsDOIModel->saveDOITarget($doiData);
        }*/
    }
}
