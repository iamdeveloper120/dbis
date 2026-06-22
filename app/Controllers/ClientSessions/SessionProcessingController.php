<?php

namespace App\Controllers\ClientSessions;

use App\Controllers\AdminController;

use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientSessions\DailySessionDataProcessedModel;

use App\Models\ClientSessions\DailySessionModel;
use App\Models\ClientSessions\SessionDurationModel;
use App\Models\ClientSessions\SessionPBDurationModel;
use App\Models\ClientSessions\SessionMandDurationModel;
use App\Models\Mands\MandsSessionDataModel;

use App\Models\ClientProgram\ClientProgramChangeAlertModel;
use App\Models\ClientProgram\ClientProgramChangeModel;
use App\Models\ClientProgram\ClientProgramChangeAntModel;
use App\Models\ClientProgram\ClientProgramChangeConModel;

use App\Models\ClientProgram\ClientProgramTargetOverridesModel;

use App\Models\ClientProgram\ClientTargetsDOIModel;
use App\Models\ClientProgram\ClientTargetsRetainedModel;

use App\Models\ClientProgram\ClientProbeSetRuleModel;

use App\Models\ClientSessions\DailySessionProcessingLog;

use App\Models\ClientSessions\DailySessionTargetConflictResolutionLog;


class SessionProcessingController extends AdminController
{
    protected $dailySessionModel;
    protected $dailySessionDataCollectionModel;
    protected $sessionDurationModel;
    protected $pbDurationModel;
    protected $mandsDurationModel;
    protected $mandsSessionDataModel;
    protected $processingService;

    protected $dailySessionDataProcessedModel;
    protected $clientProgramChangeAlertModel;
    protected $clientProgramChangeModel;
    protected $clientProgramChangeAntModel;
    protected $clientProgramChangeConModel;
    protected $clientProgramTargetOverridesModel;
    protected $clientTargetsDOIModel;
    protected $clientTargetsRetainedModel;

    protected $clientProbeSetRuleModel;

    protected $processedModel;
    protected $dailySessionProcessingLog;
    protected $conflictLog;

    public function __construct()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        $this->dailySessionDataCollectionModel = new DailySessionDataCollectionModel();
        $this->dailySessionDataProcessedModel = new DailySessionDataProcessedModel();

        $this->dailySessionModel = new DailySessionModel();
        $this->sessionDurationModel = new SessionDurationModel();
        $this->pbDurationModel = new SessionPBDurationModel();
        $this->mandsDurationModel = new SessionMandDurationModel();
        $this->mandsSessionDataModel = new MandsSessionDataModel();

        $this->clientProgramChangeAlertModel = new ClientProgramChangeAlertModel();
        $this->clientProgramChangeModel = new ClientProgramChangeModel();
        $this->clientProgramChangeAntModel = new ClientProgramChangeAntModel();
        $this->clientProgramChangeConModel = new ClientProgramChangeConModel();
        $this->clientProgramTargetOverridesModel = new ClientProgramTargetOverridesModel();
        $this->clientTargetsDOIModel = new ClientTargetsDOIModel();
        $this->clientTargetsRetainedModel = new ClientTargetsRetainedModel();

        $this->clientProbeSetRuleModel = new ClientProbeSetRuleModel();

        $this->processedModel = new DailySessionDataProcessedModel();

        $this->dailySessionProcessingLog = new DailySessionProcessingLog();

        $this->conflictLog = new DailySessionTargetConflictResolutionLog();


        $this->processingService = service('sessionProcessingService');
    }

    public function processAll()
    {
        $processingResponses = [];
        $actionResponse = [];

        $sessionId = $this->request->getPost('id');
        $instructor_comments = $this->request->getPost('instructor_comments');
        $comments = $this->request->getPost('comments');
        $session_rating = $this->request->getPost('session_rating');

        $sessionData =  $this->dailySessionModel->find($sessionId);

        if ($sessionData->status == 1) {
            $actionResponse = ['success' => false, 'message' => 'The session is still running. Complete or end the session to continue with further processing.'];
        } else if ($sessionData->status == 3) {
            $actionResponse = ['success' => false, 'message' => 'All data in the session has already been processed.'];
        } else if (in_array($sessionData->status, [2, 4])) {

            // ✅ Teaching Duration Checks
            if ($this->sessionDurationModel->hasEmptyEndTime($sessionId)) {
                $actionResponse = ['success' => false, 'message' => 'The session remains active. Adjust the end time or close the session, then try processing again.'];
            } else if (!$this->sessionDurationModel->hasAtLeastOneRecord($sessionId)) {
                $actionResponse = ['success' => false, 'message' => 'At least one Teaching Duration record is required before processing.'];
            }
            // ✅ Mands Duration Checks
            else if ($this->mandsDurationModel->hasEmptyEndTime($sessionId)) {
                $actionResponse = ['success' => false, 'message' => 'Adjust the mands duration end time or close the session, then try processing again.'];
            } else {
                // Check if Mands Data Exists
                $mandsDataExists = $this->mandsSessionDataModel->where('session_id', $sessionId)->countAllResults();

                if ($mandsDataExists > 0) {
                    // If Mands data exists, ensure at least one Mands duration record
                    if (!$this->mandsDurationModel->hasAtLeastOneRecord($sessionId)) {
                        $actionResponse = ['success' => false, 'message' => 'At least one Mands Duration record is required because Mands session data exists.'];
                    }
                } else {
                    // If no Mands data, ensure no Mands duration records exist
                    if ($this->mandsDurationModel->hasAtLeastOneRecord($sessionId)) {
                        $actionResponse = ['success' => false, 'message' => 'Mands Duration records should not exist when no Mands session data is available.'];
                    }
                }
            }

            // ✅ Problem Behavior (PB) Duration Check
            if ($this->pbDurationModel->hasMissingPbRecords($sessionId)) {
                $actionResponse = ['success' => false, 'message' => 'Complete all problem behavior data before processing.'];
            }

            // ✅ Proceed with Processing if No Errors
            if (empty($actionResponse)) {
                // Fetch all unprocessed targets for the session and do not have any conflict
                $targets = $this->dailySessionDataCollectionModel
                    ->asArray()
                    ->where('session_id', $sessionId)
                    ->where('is_processed', 0)
                    ->where('is_conflicted', 0)
                    ->findAll();

                $totalTargets = count($targets);
                $processedSuccess = 0;
                $conflictedTargets = 0;
                $deletedTargets = 0;

                foreach ($targets as $data) {

                    // ✅ Check for conflicts if for given target any upward processed data exist. if yes then will add conflict to true and will not proceed that target
                    if ($this->dailySessionDataCollectionModel->checkForConflict($data['client_id'], $data['target_id'], $data['client_probe_set_id'], $data['session_date'])) {
                        $this->dailySessionDataCollectionModel->update($data['id'], ['is_conflicted' => true, 'conflict_reason' => 'Upward processed data detected.']);
                        $processingResponses[] = [
                            'success' => false,
                            'message' => 'Upward processed data detected.',
                            'status' => 'conflict',
                            'data' => $data
                        ];
                        $conflictedTargets++;
                        continue;
                    }

                    // if no conflict exist and target is already mastered then what to do...
                    $isTargetMastered = $this->clientTargetsRetainedModel
                        ->where([
                            'client_id' => $data['client_id'],
                            'target_id' => $data['target_id'],
                            'client_probe_set_id' => $data['client_probe_set_id']
                        ])
                        ->countAllResults() > 0; // Returns true if the target is mastered, false otherwise

                    if ($isTargetMastered) {
                        //$this->dailySessionDataCollectionModel->update($data['id'], ['is_conflicted' => true, 'conflict_reason' => 'Target is mastered in past processing.']);
                        $this->dailySessionDataCollectionModel->where('id', $data['id'])->delete();
                        $processingResponses[] = [
                            'success' => false,
                            'message' => 'Target is mastered in past processing. Collected data has been removed',
                            'status' => 'conflict',
                            'is_deleted' => true,
                            'data' => $data
                        ];
                        $deletedTargets++;
                        continue;
                    }

                    $targetCurrentActivePhase = $this->processedModel->where([
                        'client_id' => $data['client_id'],
                        'target_id' => $data['target_id'],
                        'client_probe_set_id' => $data['client_probe_set_id'],
                        'is_active' => 1
                    ])->first();

                    if ($targetCurrentActivePhase &&  $targetCurrentActivePhase['next_phase_id'] == 3) {

                        $retentionPhaseRuleData = $this->clientProbeSetRuleModel->where([
                            'phase_id' => $targetCurrentActivePhase['next_phase_id'],
                            'client_probe_set_id' => $targetCurrentActivePhase['client_probe_set_id']
                        ])->first();

                        $retentionPhaseRule = json_decode($retentionPhaseRuleData['rules'], true);
                        $activation_days = $retentionPhaseRule['activation_days'];
                        $days_from_retention = getDaysDifference($targetCurrentActivePhase['session_date'], $data['session_date']);

                        // Skip if the number of days since the last session is less than the activation days
                        if ($days_from_retention < $activation_days) {
                            //$this->dailySessionDataCollectionModel->update($data['id'], ['is_conflicted' => true, 'conflict_reason' => 'Target is in retention phase and still collected data do not met retention days criteria.']);
                            $this->dailySessionDataCollectionModel->where('id', $data['id'])->delete();
                            $processingResponses[] = [
                                'success' => false,
                                'message' => 'Target is in retention phase and still collected data do not met retention days criteria. Collected data has been removed',
                                'status' => 'conflict',
                                'is_deleted' => true,
                                'data' => $data
                            ];
                            $deletedTargets++;
                            continue;
                        }
                    }


                    // ✅ Process only clean data
                    $singleTargetProcessResponse = $this->processingService->processTargetData($data);
                    $singleTargetProcessResponse['data'] = $data;
                    $processingResponses[] = $singleTargetProcessResponse;
                    if ($singleTargetProcessResponse['success']) {
                        $processedSuccess++;
                    }
                }

                // Check if session is fully processed
                $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($sessionId);

                // Update session status
                $updatedSessionData = [
                    'id' => $sessionId,
                    'session_rating' => $session_rating,
                    'instructor_comments' => $instructor_comments,
                    'comments' => $comments,
                    'status' => $sessionProcessingStatus['status_code'],
                    'note' => $sessionProcessingStatus['status_name']
                ];

                // Save session update
                $this->dailySessionModel->update($sessionId, $updatedSessionData);
                $updatedSessionData =  $this->dailySessionModel->get_client_executed_session($sessionId);

                // ✅ Store session processing log
                $this->dailySessionProcessingLog->insert([
                    'session_id' => $sessionId,
                    'processed_by' => auth()->user()->id,
                    'process_count' => $this->dailySessionProcessingLog->where('session_id', $sessionId)->countAll() + 1, // Increment count
                    'session_status' => $sessionProcessingStatus['status_name'],
                    'total_targets' => $totalTargets,
                    'processed_success' => $processedSuccess,
                    'conflicted_targets' => $conflictedTargets,
                    'deleted_targets' => $deletedTargets,
                    'details' => json_encode($processingResponses), // Store full details in JSON
                    'session_details' => json_encode($updatedSessionData)
                ]);

                $actionResponse = [
                    'success' => true,
                    'message' => 'Processed successfully',
                    'processResponses' => $processingResponses,
                    'session_row_data' => $updatedSessionData
                ];
            }
        } else {
            $actionResponse = ['success' => false, 'message' => 'Session Status not exist'];
        }

        return $this->response->setJSON($actionResponse);
    }

    public function processConflict()
    {
        // Step 1: Retrieve current target context (permissions, validations)
        $contextResponse = $this->getConflictedTargetContext();

        if (!$contextResponse['success']) {
            return $this->response->setJSON($contextResponse);
        }
        $currentTargetData = $contextResponse['data'];

        $db = \Config\Database::connect();
        $db->transException(true)->transStart();

        try {
            // Step 2: Fetch upward targets and collection IDs
            [$targets, $collectionIds] = $this->getUpwardCollectionData($currentTargetData);

            // Step 3: Fetch processed data and session IDs
            [$processedData, $processedIds, $sessionIds] = $this->getRelatedProcessedData($collectionIds);

            // Step 4: Backup related data before deletion
            $existingData = $this->captureExistingData($processedIds, $collectionIds, $currentTargetData);

            // Step 5: Perform deletions
            $this->deleteRelatedData($existingData, $processedIds, $collectionIds, $currentTargetData);

            // Step 6: Handle override insertion (if latest program change exists)
            $this->restoreOverrideFromLatestProgramChange($currentTargetData);

            // Step 7: Reprocess all targets including current
            $processingResponses = $this->processConflictTargetList($currentTargetData, $targets);

            // Step 8: Insert conflict resolution log
            $this->logConflictResolution($currentTargetData, $existingData, $processingResponses);

            // Step 9: Mark current entry as not conflicted
            $this->clearConflictFlag($currentTargetData['id']);

            // Step 10: Update session statuses
            $this->updateSessionStatuses($sessionIds, $currentTargetData['session_id']);

            // ✅ Commit Transaction
            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Conflict resolved successfully.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'processConflict failed: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while resolving the conflict. Please contact the administrator.',
            ]);
        }
    }

    private function getConflictedTargetContext(): array
    {
        $dataCollectionId = $this->request->getPost('dataCollectionId');

        $currentTargetData = $this->dailySessionDataCollectionModel
            ->asArray()
            ->where('id', $dataCollectionId)
            ->first();

        if (!$currentTargetData) {
            return ['success' => false, 'message' => 'Invalid target data.'];
        }

        if (!auth()->user()->can('sessions.review.modification')) {
            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $currentTargetData['session_date'])
                ->where('client_id', $currentTargetData['client_id'])
                ->where('target_id', $currentTargetData['target_id'])
                ->where('client_probe_set_id', $currentTargetData['client_probe_set_id'])
                ->where('is_processed', 1)
                ->countAllResults();

            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return [
                    'success' => false,
                    'message' => 'You are not authorized to resolve conflict while there are more than '
                        . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ];
            }
        }

        return ['success' => true, 'data' => $currentTargetData];
    }
    private function getUpwardCollectionData(array $currentTargetData): array
    {
        $targets = $this->dailySessionDataCollectionModel
            ->asArray()
            ->where('session_date > ', $currentTargetData['session_date'])
            ->where('client_id', $currentTargetData['client_id'])
            ->where('target_id', $currentTargetData['target_id'])
            ->where('client_probe_set_id', $currentTargetData['client_probe_set_id'])
            ->where('is_processed', 1)
            ->orderBy('session_date', 'ASC')
            ->findAll();

        $collectionIds = array_column($targets, 'id');

        return [$targets, $collectionIds];
    }
    private function getRelatedProcessedData(array $collectionIds): array
    {
        if (empty($collectionIds)) {
            return [[], [], []];
        }

        $processedData = $this->dailySessionDataProcessedModel
            ->asArray()
            ->whereIn('collection_id', $collectionIds)
            ->findAll();

        $processedIds = array_column($processedData, 'id');
        $sessionIds = array_column($processedData, 'session_id');

        return [$processedData, $processedIds, array_unique($sessionIds)];
    }
    private function captureExistingData(array $processedIds, array $collectionIds, array $currentTargetData): array
    {
        $programChangeIds = $this->clientProgramChangeModel
            ->whereIn('processed_data_id', $processedIds)
            ->findColumn('id');

        return [
            'collection_data' => $this->dailySessionDataCollectionModel
                ->asArray()
                ->whereIn('id', $collectionIds)
                ->findAll(),

            'processed_data' => $this->dailySessionDataProcessedModel
                ->asArray()
                ->whereIn('collection_id', $collectionIds)
                ->findAll(),

            'retained_data' => $this->clientTargetsRetainedModel
                ->asArray()
                ->whereIn('processed_data_id', $processedIds)
                ->findAll(),

            'doi_data' => $this->clientTargetsDOIModel
                ->asArray()
                ->whereIn('processed_data_id', $processedIds)
                ->findAll(),

            'alert_data' => $this->clientProgramChangeAlertModel
                ->asArray()
                ->whereIn('processed_data_id', $processedIds)
                ->findAll(),

            'program_change_data' => $this->clientProgramChangeModel
                ->asArray()
                ->whereIn('processed_data_id', $processedIds)
                ->findAll(),

            'pg_ant_data' => !empty($programChangeIds)
                ? $this->clientProgramChangeAntModel->asArray()->whereIn('prog_ch_id', $programChangeIds)->findAll()
                : [],

            'pg_con_data' => !empty($programChangeIds)
                ? $this->clientProgramChangeConModel->asArray()->whereIn('prog_ch_id', $programChangeIds)->findAll()
                : [],

            'overrides_data' => $this->clientProgramTargetOverridesModel
                ->asArray()
                ->where([
                    'client_id' => $currentTargetData['client_id'],
                    'target_id' => $currentTargetData['target_id'],
                    'probe_set_id' => $currentTargetData['client_probe_set_id']
                ])
                ->findAll(),
        ];
    }
    private function deleteRelatedData(array $existingData, array $processedIds, array $collectionIds, array $currentTargetData): void
    {
        // Delete dependencies in safe order
        if (!empty($existingData['pg_ant_data'])) {
            $this->clientProgramChangeAntModel
                ->whereIn('prog_ch_id', array_column($existingData['pg_ant_data'], 'prog_ch_id'))
                ->delete();
        }

        if (!empty($existingData['pg_con_data'])) {
            $this->clientProgramChangeConModel
                ->whereIn('prog_ch_id', array_column($existingData['pg_con_data'], 'prog_ch_id'))
                ->delete();
        }

        $this->clientProgramChangeModel->whereIn('processed_data_id', $processedIds)->delete();
        $this->clientProgramChangeAlertModel->whereIn('processed_data_id', $processedIds)->delete();
        $this->clientTargetsDOIModel->whereIn('processed_data_id', $processedIds)->delete();
        $this->clientTargetsRetainedModel->whereIn('processed_data_id', $processedIds)->delete();
        $this->dailySessionDataProcessedModel->whereIn('collection_id', $collectionIds)->delete();

        $this->clientProgramTargetOverridesModel->where([
            'client_id' => $currentTargetData['client_id'],
            'target_id' => $currentTargetData['target_id'],
            'probe_set_id' => $currentTargetData['client_probe_set_id']
        ])->delete();

        // Reassign is_active if needed
        $latestProcessed = $this->dailySessionDataProcessedModel
            ->where([
                'client_id' => $currentTargetData['client_id'],
                'target_id' => $currentTargetData['target_id'],
                'client_probe_set_id' => $currentTargetData['client_probe_set_id']
            ])
            ->orderBy('session_date', 'DESC')
            ->first();

        if ($latestProcessed) {
            $this->dailySessionDataProcessedModel
                ->where([
                    'client_id' => $latestProcessed['client_id'],
                    'target_id' => $latestProcessed['target_id'],
                    'client_probe_set_id' => $latestProcessed['client_probe_set_id']
                ])
                ->set(['is_active' => 0])
                ->update();

            $this->dailySessionDataProcessedModel
                ->update($latestProcessed['id'], ['is_active' => 1]);
        }

        $this->dailySessionDataCollectionModel
            ->whereIn('id', $collectionIds)
            ->set(['is_processed' => 0, 'is_conflicted' => 0])
            ->update();
    }
    private function restoreOverrideFromLatestProgramChange(array $currentTargetData): void
    {
        $latestProgramChange = $this->clientProgramChangeModel
            ->asArray()
            ->where('client_id', $currentTargetData['client_id'])
            ->where('target_id', $currentTargetData['target_id'])
            ->where('client_probe_set_id', $currentTargetData['client_probe_set_id'])
            ->where('consecutive_criteria >', 0)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($latestProgramChange) {
            $this->clientProgramTargetOverridesModel->insert([
                'client_id'             => $latestProgramChange['client_id'],
                'domain_id'             => $latestProgramChange['domain_id'],
                'goal_id'               => $latestProgramChange['goal_id'],
                'target_id'             => $latestProgramChange['target_id'],
                'probe_set_id'          => $latestProgramChange['client_probe_set_id'],
                'phase_id'              => null,
                'consecutive_criteria'  => $latestProgramChange['consecutive_criteria'],
                'created_by'            => auth()->user()->id,
                'created_at'            => date('Y-m-d H:i:s'),
            ]);

            log_message('debug', 'Processing Conflict: Inserted new override from latest program change - ' . json_encode($latestProgramChange));
        }
    }

    private function processConflictTargetList(array $currentTargetData, array $targets): array
    {
        $responses = [];

        // 🔍 No upward processed targets
        if (empty($targets)) {
            log_message('debug', 'Processing Conflict: No upward processed data found.');

            if ($this->isTargetMastered($currentTargetData)) {
                log_message('debug', 'Processing Conflict: Current target is already mastered. Deleting collection.');
                $this->dailySessionDataCollectionModel
                    ->where('id', $currentTargetData['id'])
                    ->delete();
                // Conflict log will be inserted by the caller
                return [['success' => true, 'message' => 'Target already mastered. Collection deleted.']];
            }

            if ($this->isWithinRetentionWindow($currentTargetData)) {
                log_message('debug', 'Processing Conflict: Retention check failed. Deleting collection.');
                $this->dailySessionDataCollectionModel
                    ->where('id', $currentTargetData['id'])
                    ->delete();
                // Conflict log will be inserted by the caller
                return [['success' => true, 'message' => 'Target in retention phase. Collection deleted.']];
            }

            // Otherwise, proceed to process the single current target

        }
        log_message('debug', 'Processing Conflict: Processing single current target.');
        // 👇 Upward targets exist — process full list (current + upward)
        $allTargets = array_merge([$currentTargetData], $targets);

        foreach ($allTargets as $i => $targetData) {
            log_message('debug', 'Processing Conflict: Processing target - ' . json_encode($targetData));

            $response = $this->processingService->processConflictTargetData($targetData);
            $response['data_before_resolving'] = $targetData;
            $responses[] = $response;

            // 🧠 Check for mastery
            if ($this->isTargetMastered($targetData)) {
                $this->deleteFutureTargetData($targetData);
                break;
            }

            // ⏳ Retention check (only if next exists)
            if ($this->isWithinRetentionWindow($targetData, $allTargets[$i + 1] ?? null)) {
                $this->deleteFutureTargetData($targetData);
                break;
            }
        }

        return $responses;
    }

    private function isTargetMastered(array $targetData): bool
    {
        return $this->clientTargetsRetainedModel
            ->where([
                'client_id' => $targetData['client_id'],
                'target_id' => $targetData['target_id'],
                'client_probe_set_id' => $targetData['client_probe_set_id']
            ])
            ->countAllResults() > 0;
    }

    private function isWithinRetentionWindow(array $current, ?array $next = null): bool
    {
        $activePhase = $this->processedModel->where([
            'client_id' => $current['client_id'],
            'target_id' => $current['target_id'],
            'client_probe_set_id' => $current['client_probe_set_id'],
            'is_active' => 1
        ])->first();

        if (!$activePhase || $activePhase['next_phase_id'] != 3) {
            return false;
        }

        // If `next` is not passed (single target only), compare with current session
        $nextSessionDate = $next['session_date'] ?? $current['session_date'];

        $rule = $this->clientProbeSetRuleModel->where([
            'phase_id' => $activePhase['next_phase_id'],
            'client_probe_set_id' => $activePhase['client_probe_set_id']
        ])->first();

        if (!$rule) return false;

        $activationDays = json_decode($rule['rules'], true)['activation_days'] ?? 0;
        $daysPassed = getDaysDifference($activePhase['session_date'], $nextSessionDate);

        return $daysPassed < $activationDays;
    }

    private function deleteFutureTargetData(array $targetData): void
    {
        $futureIds = $this->dailySessionDataCollectionModel
            ->where('session_date >', $targetData['session_date'])
            ->where('client_id', $targetData['client_id'])
            ->where('target_id', $targetData['target_id'])
            ->where('client_probe_set_id', $targetData['client_probe_set_id'])
            ->findColumn('id');

        if (!empty($futureIds)) {
            (new \App\Models\ClientProgram\ClientStimulusStepMasteryModel())
                ->whereIn('collection_id', $futureIds)
                ->delete();
            // Step session data deletion
            (new \App\Models\ClientSessions\StimulusStepSessionsDataModel())
                ->deleteByCollectionIds($futureIds);

            // Step mastery data deletion
            /*(new \App\Models\ClientProgram\ClientStimulusStepMasteryModel())
                ->whereIn('collection_id', $futureIds)
                ->delete();*/

            $this->dailySessionDataCollectionModel
                ->whereIn('id', $futureIds)
                ->delete();

            log_message('debug', 'Processing Conflict: Deleted future stimulus session and mastery data for retained/mastered target.');
        }
    }

    private function logConflictResolution(array $currentTargetData, array $existingData, array $responses): void
    {
        $this->conflictLog->insert([
            'session_id'       => $currentTargetData['session_id'],
            'target_id'        => $currentTargetData['target_id'],
            'client_id'        => $currentTargetData['client_id'],
            'client_probe_set_id' => $currentTargetData['client_probe_set_id'],
            'conflicted_data'  => json_encode($currentTargetData),
            'existing_data'    => json_encode($existingData),
            'modifications'    => json_encode($responses),
            'resolved_by'      => auth()->user()->id,
            'resolved_at'      => date('Y-m-d H:i:s'),
        ]);
    }
    private function clearConflictFlag(int $collectionId): void
    {
        $this->dailySessionDataCollectionModel
            ->where('id', $collectionId)
            ->where('is_processed', 1)
            ->set(['is_conflicted' => 0])
            ->update();
    }
    private function updateSessionStatuses(array $sessionIds, int $currentSessionId): void
    {
        $sessionIds[] = $currentSessionId;
        $sessionIds = array_unique($sessionIds);

        foreach ($sessionIds as $sessionId) {
            $status = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($sessionId);
            log_message('debug', 'Updating session ' . $sessionId . ' status to ' . json_encode($status));
            $this->dailySessionModel->update($sessionId, [
                'status' => $status['status_code'],
                'note'   => $status['status_name']
            ]);
        }
    }
}
