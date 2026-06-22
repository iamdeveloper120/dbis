<?php

namespace App\Services;

use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientSessions\DailySessionDataProcessedModel;
use App\Services\ProbeSetProcessing\ProbeSetProcessingFactory;
use App\Models\ClientProgram\ClientProbeSetModel;

class SessionProcessingService
{
    protected $collectionModel;
    protected $processedModel;
    protected $clientProbeSetModel;

    public function __construct()
    {
        $this->collectionModel = new DailySessionDataCollectionModel();
        $this->processedModel = new DailySessionDataProcessedModel();
        $this->clientProbeSetModel = new ClientProbeSetModel();
    }

    public function processTargetData($data)
    {
        $db = \Config\Database::connect();
        
        $db->transException(true)->transStart();
        try {


            // ✅ Decode collected data once at the beginning
            $collectedData = json_decode($data['collected_data'], true);

            // ✅ 1. Get the last processed data (if any)
            $lastProcessedData = $this->processedModel->getTargetLastProcessedDataByDate(
                $data['client_id'],
                $data['target_id'],
                $data['client_probe_set_id'],
                $data['session_date']
            );

            // ✅ 2. Update collection data if previous processed data exists
            if ($lastProcessedData && $lastProcessedData['next_phase_id'] != 4) {
                $nextPhaseId = $lastProcessedData['next_phase_id'];

                // Fetch probe set details for the correct phase
                $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails(
                    $data['client_probe_set_id'],
                    $nextPhaseId
                );

                // Determine frame set number based on processed data or defaults
                $frameSetNo = $this->getFrameSetNo($lastProcessedData, $probeSetDetails);

                // Update collected data array directly (no need for extra decoding)
                $collectedData['phase']['id'] = $nextPhaseId;
                $collectedData['phase']['name'] = $probeSetDetails['phase_name'];
                $collectedData['rule']['frame_set_no'] = $frameSetNo;
                $collectedData['rule']['default_rule'] = $probeSetDetails['rule_data'];
                $collectedData['inputs'] = json_decode($probeSetDetails['inputs'], true);
                $collectedData['combination'] = [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ];

                // ✅ 3. Update collection table with correct phase and collected data
                $this->collectionModel->update($data['id'], [
                    'current_phase_id' => $nextPhaseId,
                    'collected_data' => json_encode($collectedData),
                ]);

                $data['current_phase_id'] = $nextPhaseId;
                $data['collected_data'] = json_encode($collectedData);

                // ✅ STIMULUS RECOMPILATION if needed
                if ($collectedData['inputs']['type'] === 'stimulus_program') {
                    $method = $collectedData['method'] ?? null;

                    if (in_array($method, ['forward', 'backward'])) {
                        $summary = $this->recompileStimulusStepSummary($method, $data);

                        $collectedData['statistics'] = $summary['statistics'];
                        $collectedData['result'] = $summary['result'];

                        $this->collectionModel->update($data['id'], [
                            'collected_data' => json_encode($collectedData),
                            'updated_at' => currentDate('Y-m-d H:i:s'),
                            'updated_by' => auth()->user()->id,
                        ]);

                        $data['collected_data'] = json_encode($collectedData);
                    }
                }
            }


            if (!$lastProcessedData) {
                // ✅ 4. Get the appropriate processing service
                $processingService = ProbeSetProcessingFactory::create($collectedData['inputs']['type']);

                // ✅ 5. Process the data using the selected service
                $processingResult = $processingService->process($data, $collectedData);
            } else if ($lastProcessedData['next_phase_id'] != 4) {
                // ✅ 4. Get the appropriate processing service
                $processingService = ProbeSetProcessingFactory::create($collectedData['inputs']['type']);

                // ✅ 5. Process the data using the selected service
                $processingResult = $processingService->process($data, $collectedData);
            }

            $db->transComplete();
            return $processingResult;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'processConflict failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            return ['success' => false, 'message' => 'An error occurred while processing your request.'];
        }
    }

    public function processConflictTargetData($data)
    {
        try {

            $processingResult = ['success' => false, 'message' => 'Not Processed'];;
            // ✅ Decode collected data once at the beginning
            $collectedData = json_decode($data['collected_data'], true);

            // ✅ 1. Get the last processed data (if any)
            $lastProcessedData = $this->processedModel->getTargetLastProcessedDataByDate(
                $data['client_id'],
                $data['target_id'],
                $data['client_probe_set_id'],
                $data['session_date']
            );

            // ✅ 2. Update collection data if previous processed data exists
            if ($lastProcessedData && $lastProcessedData['next_phase_id'] != 4) {
                $nextPhaseId = $lastProcessedData['next_phase_id'];

                // Fetch probe set details for the correct phase
                $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails(
                    $data['client_probe_set_id'],
                    $nextPhaseId
                );

                // Determine frame set number based on processed data or defaults
                $frameSetNo = $this->getFrameSetNo($lastProcessedData, $probeSetDetails);

                // Update collected data array directly (no need for extra decoding)
                $collectedData['phase']['id'] = $nextPhaseId;
                $collectedData['phase']['name'] = $probeSetDetails['phase_name'];
                $collectedData['rule']['frame_set_no'] = $frameSetNo;
                $collectedData['rule']['default_rule'] = $probeSetDetails['rule_data'];
                $collectedData['inputs'] = json_decode($probeSetDetails['inputs'], true);
                $collectedData['combination'] = [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ];

                // ✅ 3. Update collection table with correct phase and collected data
                $this->collectionModel->update($data['id'], [
                    'current_phase_id' => $nextPhaseId,
                    'collected_data' => json_encode($collectedData),
                    'is_reprocessed' => 1,
                ]);

                // Also update `$data` for further processing
                $data['current_phase_id'] = $nextPhaseId;
                $data['collected_data'] = json_encode($collectedData);

                // ✅ STIMULUS RECOMPILATION for conflict resolution (forward/backward)
                if ($collectedData['inputs']['type'] === 'stimulus_program') {
                    $method = $collectedData['method'] ?? null;

                    if (in_array($method, ['forward', 'backward'])) {
                        $summary = $this->recompileStimulusStepSummary($method, $data);

                        $collectedData['statistics'] = $summary['statistics'];
                        $collectedData['result'] = $summary['result'];

                        $this->collectionModel->update($data['id'], [
                            'collected_data' => json_encode($collectedData),
                            'updated_at' => currentDate('Y-m-d H:i:s'),
                            'updated_by' => auth()->user()->id,
                        ]);

                        $data['collected_data'] = json_encode($collectedData);
                    }
                }
            }

            if (!$lastProcessedData) {
                // ✅ 4. Get the appropriate processing service
                $processingService = ProbeSetProcessingFactory::create($collectedData['inputs']['type']);

                // ✅ 5. Process the data using the selected service
                $processingResult = $processingService->process($data, $collectedData);
            } else if ($lastProcessedData['next_phase_id'] != 4) {
                // ✅ 4. Get the appropriate processing service
                $processingService = ProbeSetProcessingFactory::create($collectedData['inputs']['type']);

                // ✅ 5. Process the data using the selected service
                $processingResult = $processingService->process($data, $collectedData);
            }
            $processingResult['data_after_resolving'] = $data;

            return $processingResult;
        } catch (\Exception $e) {
             log_message('error', 'processConflictTargetData: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            throw new \Exception('An error occurred while processing target data.');
        }
    }

    private function getFrameSetNo(?array $processedData, array $probeSetDetails)
    {
        // Determine the frame set number based on the processed data or defaults
        $rule_data = json_decode($probeSetDetails['rule_data'], true);

        if (isset($rule_data['frame_check']) && $rule_data['frame_check']) {
            if ($processedData) {
                $processed_detail = json_decode($processedData['processed_detail'], true);
                return $processed_detail['next_frame_set_no'];
            } else {
                return 1; // Default starting frame set
            }
        }
        return null;
    }
    protected function recompileStimulusStepSummary(string $method, array $data): array
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();
        $chainModel       = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $targetStepModel  = new \App\Models\ClientProgram\ClientStimulusStepModel();

        $client_id           = $data['client_id'];
        $target_id           = $data['target_id'];
        $client_probe_set_id = $data['client_probe_set_id'];
        $current_phase_id    = $data['current_phase_id'];
        $session_id          = $data['session_id'];
        $session_date        = $data['session_date'];
        $collection_id       = $data['id'];

        $collectionModel = $this->collectionModel;

        // Get all processed collection IDs up to this date + current
        $processedCollectionIds = $collectionModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('session_date <=', $session_date)
            ->where('is_processed', 1)
            ->select('id')
            ->findColumn('id');

        $validCollectionIds = array_merge($processedCollectionIds, [$collection_id]);

        // 🔁 Get all steps collected in this collection only
        $stepRows = $stepSessionModel
            ->where('collection_id', $collection_id)
            ->where('method', $method)
            ->where('phase_id', $current_phase_id)
            ->findAll();

        if (empty($stepRows)) {
            return $data; // Nothing to update
        }

        $step_id    = $stepRows[0]['step_id'] ?? null;
        $probeValue = $stepRows[0]['input_result'] ?? null;

        // ✅ Get chaining rule
        $chainRow = $chainModel->where('target_id', $target_id)->first();
        $requiredConsecutive = 3;
        if ($chainRow && !empty($chainRow->rule_override)) {
            $ruleOverride = json_decode($chainRow->rule_override, true);
            if (isset($ruleOverride[$method]['step_mastery']['value'])) {
                $requiredConsecutive = (int) $ruleOverride[$method]['step_mastery']['value'];
            }
        }

        // 🔄 Re-fetch all previous attempts for this step
        $allAttempts = $stepSessionModel
            ->where([
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id'   => $step_id,
                'method'    => $method,
                'phase_id'  => $current_phase_id,
            ])
            ->whereIn('collection_id', $validCollectionIds)
            ->orderBy('session_date', 'desc')
            ->findAll();

        $consecutiveInd = 0;
        foreach ($allAttempts as $entry) {
            if ($entry['input_result'] === 'IND') {
                $consecutiveInd++;
            } else {
                break;
            }
        }

        // ⛔ Delete old mastery if exists
        $existingMastery = $masteryModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'method'    => $method,
        ])->first();

        if ($existingMastery) {
            $masteryModel->delete($existingMastery->id);
        }

        $isMastered = false;
        if ($consecutiveInd >= $requiredConsecutive) {
            $isMastered = true;
            $masteryModel->insert([
                'client_id'     => $client_id,
                'target_id'     => $target_id,
                'step_id'       => $step_id,
                'method'        => $method,
                'collection_id' => $collection_id,
                'session_id'    => $session_id,
                'session_date'  => $session_date,
                'mastered_on'   => currentDate('Y-m-d'),
                'created_by'    => auth()->user()->id,
            ]);
        }

        // Recompile must mirror save-time percentage logic from live/review controllers:
        // denominator = all configured target steps, and for forward/backward the numerator
        // includes baseline-mastered steps plus current chain mastered steps (distinct by step_id).
        $targetStepIds = $targetStepModel
            ->where('target_id', $target_id)
            ->select('id')
            ->findColumn('id');
        $targetStepIds = array_map('intval', $targetStepIds ?? []);
        $totalSteps = count($targetStepIds);

        $masteredSteps = 0;
        if ($totalSteps > 0) {
            $masteryMethods = in_array($method, ['forward', 'backward'], true)
                ? ['baseline', $method]
                : [$method];

            $masteredStepIds = $masteryModel
                ->distinct()
                ->select('step_id')
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->whereIn('method', $masteryMethods)
                ->whereIn('step_id', $targetStepIds)
                ->findColumn('step_id');

            $masteredSteps = count($masteredStepIds ?? []);
        }

        $percentage = $totalSteps > 0 ? round(($masteredSteps / $totalSteps) * 100, 2) : 0;

        // 🔁 Update collected_data column
        $updatedCollected = [
            'statistics' => [
                'step_id'         => $step_id,
                'probe_value'     => $probeValue,
                'total_attempts'  => count($allAttempts),
                'required_ind'    => $requiredConsecutive,
                'consecutive_ind' => $consecutiveInd,
                'is_mastered'     => $isMastered,
                'total_steps'     => $totalSteps,
                'mastered_steps'  => $masteredSteps,
                'method'          => $method,
            ],
            'result' => [$percentage],
        ];

        $collectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at'     => currentDate('Y-m-d H:i:s'),
            'updated_by'     => auth()->user()->id,
        ]);


        return $updatedCollected;
    }
}
