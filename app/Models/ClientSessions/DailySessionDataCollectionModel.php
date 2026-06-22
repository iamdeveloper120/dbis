<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DataCollection;


class DailySessionDataCollectionModel  extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_session_data_collection';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = DataCollection::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'session_id',
        'session_date',
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'client_probe_set_id',
        'current_phase_id',
        'collected_data',
        'is_processed',
        'is_conflicted',
        'conflict_reason',
        'is_default',
        'is_reprocessed',
        'processed_at',
        'processed_by',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getSessionData($session_id)
    {
        return $this->select('
                daily_session_data_collection.*,
                client_program_domains.name as domain_name,
                client_program_domains.domain_code,
                client_program_goals.name as goal_name,
                client_program_goals.goal_code,
                client_program_targets.name as target_name,
                target_probe_sets.name as probe_set_name,
                target_probe_sets.id as master_probe_set_id
            ')
            ->join('client_program_domains', 'client_program_domains.id = daily_session_data_collection.domain_id')
            ->join('client_program_goals', 'client_program_goals.id = daily_session_data_collection.goal_id')
            ->join('client_program_targets', 'client_program_targets.id = daily_session_data_collection.target_id')
            ->join('client_probe_set', 'client_probe_set.id = daily_session_data_collection.client_probe_set_id')
            ->join('target_probe_sets', 'target_probe_sets.id = client_probe_set.probe_set_id') // Corrected join with target_probe_sets
            ->where('daily_session_data_collection.session_id', $session_id)
            ->orderBy('daily_session_data_collection.created_at', 'asc')
            ->findAll();
    }

    // Custom method to fetch a single row as an associative array
    public function getSingleArray($id)
    {
        $result = $this->asArray()
            ->where(['id' => $id])
            ->first();
        return $result;
    }

    public function getSingle($id)
    {
        return $this->select('
                daily_session_data_collection.*,
                client_program_domains.name as domain_name,
                client_program_domains.domain_code,
                client_program_goals.name as goal_name,
                client_program_goals.goal_code,
                client_program_targets.name as target_name,
                target_probe_sets.name as probe_set_name,
                 target_probe_sets.id as master_probe_set_id
            ')
            ->join('client_program_domains', 'client_program_domains.id = daily_session_data_collection.domain_id')
            ->join('client_program_goals', 'client_program_goals.id = daily_session_data_collection.goal_id')
            ->join('client_program_targets', 'client_program_targets.id = daily_session_data_collection.target_id')
            ->join('client_probe_set', 'client_probe_set.id = daily_session_data_collection.client_probe_set_id')
            ->join('target_probe_sets', 'target_probe_sets.id = client_probe_set.probe_set_id') // Corrected join with target_probe_sets
            ->where('daily_session_data_collection.id', $id)
            ->first();
    }
    public function getFullStimulusTargetByCollection($client_id, $target_id, $probe_set_id, $session_id, $session_date)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();

        $builder = $this->db->table('client_program_targets as cpt');
        $builder->select('
        cpt.id as target_id,
        cpt.name as target_name,
        cps.id as probe_set_id,
        cps.inputs as probe_set_inputs,
        tps.name as probe_set_name,
        tpc.id as combination_id,
        tpc.name as combination_name,
        IFNULL(dsp.next_phase_id, tpc.initial_phase_id) as current_phase_id,
        IFNULL(tp.name, ip.name) as current_phase_name,
        cpr.id as rule_id,
        cpr.rules as rule_data,
        ctsc.method as chain_method,
        ctsc.rule_override as chain_rule_json,
        steps.id as step_id,
        steps.step_number,
        steps.sd_text,
        steps.c_text,
        steps.response_text,
        mastery.method as mastered_with_chain,
        CASE WHEN mastery.id IS NOT NULL THEN 1 ELSE 0 END as is_mastered
    ', false);

        $builder->join('client_probe_set as cps', 'cps.id = ' . $probe_set_id, 'left');
        $builder->join('target_probe_sets as tps', 'tps.id = cps.probe_set_id', 'left');
        $builder->join('target_phase_combinations as tpc', 'tpc.id = cps.combination_id', 'left');
        $builder->join('target_phases as ip', 'ip.id = tpc.initial_phase_id', 'left');

        $builder->join('(SELECT target_id, MAX(session_date) as last_date FROM daily_session_data_processed WHERE session_date <= "' . $session_date . '" GROUP BY target_id) as dsp_sub', 'dsp_sub.target_id = cpt.id', 'left');
        $builder->join('daily_session_data_processed as dsp', 'dsp.target_id = dsp_sub.target_id AND dsp.session_date = dsp_sub.last_date', 'left');
        $builder->join('target_phases as tp', 'tp.id = dsp.next_phase_id', 'left');

        $builder->join('client_probe_rules as cpr', 'cpr.client_probe_set_id = cps.id AND cpr.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left');
        $builder->join('client_target_stimulus_chains as ctsc', 'ctsc.target_id = cpt.id', 'left');
        $builder->join('client_target_stimulus_steps as steps', 'steps.target_id = cpt.id', 'left');
        $builder->join('client_target_stimulus_step_mastery as mastery', "mastery.target_id = cpt.id AND mastery.step_id = steps.id AND mastery.session_date <= '{$session_date}'", 'left');

        $builder->where('cpt.client_id', $client_id);
        $builder->where('cpt.id', $target_id);

        $result = $builder->get()->getResultArray();

        if (empty($result)) return [];

        $target = [];
        $rules = [];
        $currentPhaseRule = null;

        foreach ($result as $row) {
            if (empty($target)) {
                $target = [
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name'],
                    'client_probe_set_id' => $probe_set_id,
                    'current_phase_id' => $row['current_phase_id'],
                    'current_phase_name' => $row['current_phase_name'],
                    'chain' => $row['chain_method'] ? [
                        'method' => $row['chain_method'],
                        'rule_override' => !empty($row['chain_rule_json']) ? json_decode($row['chain_rule_json'], true) : null
                    ] : null,
                    'steps' => [],
                    'probe_set' => [
                        'probe_set_id' => $row['probe_set_id'],
                        'probe_set_name' => $row['probe_set_name'],
                        'inputs' => json_decode($row['probe_set_inputs'], true),
                        'combination' => [
                            'combination_id' => $row['combination_id'],
                            'combination_name' => $row['combination_name'],
                            'current_phase_id' => $row['current_phase_id'],
                            'current_phase_name' => $row['current_phase_name'],
                        ],
                        'rules' => [], // will fill below
                    ]
                ];
            }

            // Populate step
            if (!empty($row['step_id'])) {
                $target['steps'][] = [
                    'step_id' => $row['step_id'],
                    'step_number' => $row['step_number'],
                    'sd_text' => $row['sd_text'],
                    'c_text' => $row['c_text'],
                    'response_text' => $row['response_text'],
                    'is_mastered' => (bool) $row['is_mastered'],
                    'mastered_with_chain' => $row['mastered_with_chain'] ?? null,
                ];
            }

            // Populate rules
            if (!empty($row['rule_id'])) {
                $decodedRule = json_decode($row['rule_data'], true);
                $rules[] = [
                    'rule_id' => $row['rule_id'],
                    'rule_data' => $decodedRule,
                    'phase_name' => $row['current_phase_name'],
                ];

                if ($row['current_phase_name'] === $row['current_phase_name']) {
                    $currentPhaseRule = $decodedRule;
                }
            }
        }

        // Attach rules and current rule
        $target['probe_set']['rules'] = $rules;
        $target['phase_name'] = $target['current_phase_name'];
        $target['additional_data'] = [
            'rules' => $rules,
            'current_rule' => $currentPhaseRule
        ];

        // Attach prefilled inputs
        $stepInputs = $stepSessionModel->where([
            'client_id' => $client_id,
            'session_id' => $session_id,
            'target_id' => $target_id,
        ])->findAll();

        $prefillInputs = [];
        foreach ($stepInputs as $row) {
            $phase = $row['phase_id'];
            $method = $row['method'];
            $step = $row['step_id'];
            $attempt = (int) $row['attempt_no'];

            $prefillInputs[$phase][$method][$step][$attempt] = $row['input_result'];
        }

        $target['prefill_step_inputs'] = $prefillInputs;

        return $target;
    }


    public function checkCollectedDataProcessingStatus($sessionId)
    {
        // Get the total number of targets in the session
        $totalTargets = $this->where('session_id', $sessionId)->countAllResults();

        // Return early if there are no targets
        if ($totalTargets == 0) {
            return ['status_code' => 3, 'status_name' => 'no-targets']; // No targets in the session
        }

        // Count processed targets, conflicted targets, and unprocessed targets
        $processedTargets = $this->where('session_id', $sessionId)
            ->where('is_processed', 1)
            ->countAllResults();

        $conflictedTargets = $this->where('session_id', $sessionId)
            ->where('is_conflicted', 1)
            ->countAllResults();

        $unprocessedTargets = $totalTargets - $processedTargets - $conflictedTargets;


        // Scenario 1: All targets are processed, and there are no conflicts
        if ($processedTargets == $totalTargets) {
            return ['status_code' => 3, 'status_name' => 'processed']; // All targets processed with no conflict
        }

        // Scenario 2: Some targets are processed, and others are unprocessed without conflict
        if ($processedTargets > 0 && $unprocessedTargets > 0 && $conflictedTargets == 0) {
            return ['status_code' => 4, 'status_name' => 'partially-processed-with-unprocessed']; // Partial processing with no conflict
        }

        // Scenario 3: All targets are processed with some conflicts
        if ($processedTargets > 0 && $unprocessedTargets == 0 && $conflictedTargets > 0) {
            return ['status_code' => 4, 'status_name' => 'partially-processed-with-conflict']; // All processed but with some conflicts
        }

        // Scenario 4: All targets are processed with some conflicts and some unprocessed
        if ($processedTargets > 0 && $unprocessedTargets > 0 && $conflictedTargets > 0) {
            return ['status_code' => 4, 'status_name' => 'mixed-processed-unprocessed-conflicted']; // All processed but with some conflicts
        }

        // Scenario 5: All targets are in conflict,  processed but deducted all conflicting targets
        if ($totalTargets == $conflictedTargets) {
            return ['status_code' => 4, 'status_name' => 'all-conflicted']; // All targets are in conflict
        }

        // Scenario 6: No targets are processed, and there are no processing as on any target
        if ($totalTargets ==  $unprocessedTargets) {
            return ['status_code' => 2, 'status_name' => 'unprocessed']; // All targets unprocessed with no conflict
        }

        log_message('debug', 'I am here nothing happened ' . $sessionId);
    }



    /**
     * Check if there's a data exist for a given target, client, probe set, and session date.
     *
     * @param int $clientId
     * @param int $targetId
     * @param int $probeSetId
     * @param string $sessionDate
     * @return bool
     */
    public function checkForDuplicateEntry(int $clientId, int $targetId, int $probeSetId, string $sessionDate): bool
    {
        return $this->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('client_probe_set_id', $probeSetId)
            ->where('session_date', $sessionDate)
            ->countAllResults() > 0;
    }

    public function getExistingEntry(int $clientId, int $targetId, int $probeSetId, string $session_id)
    {
        return $this->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('client_probe_set_id', $probeSetId)
            ->where('session_id', $session_id)
            ->first(); // This gets the first matching row as an object
    }

    /**
     * Check if there's a conflict for a given target, client, probe set, and session date.
     *
     * @param int $clientId
     * @param int $targetId
     * @param int $probeSetId
     * @param string $sessionDate
     * @return bool
     */
    public function checkForConflict(int $clientId, int $targetId, int $probeSetId, string $sessionDate): bool
    {
        return $this->where('client_id', $clientId)
            ->where('target_id', $targetId)
            ->where('client_probe_set_id', $probeSetId)
            ->where('session_date >=', $sessionDate)
            ->where('is_processed', 1)
            ->countAllResults() > 0;
    }


    /**
     * Count the number of matching results for the same day
     */
    /**
     * Check if the same day criteria are met.
     */
    public function countSameDayCriteriaMet($key, $results, $consecutiveCriteria)
    {

        // Check if consecutive_criteria is 1
        if ($consecutiveCriteria == 1) {
            // If only one result and it matches the key, criteria are met
            return (count($results) == 1 && $results[0] == $key);
        } else {
            // For more than 1 value, check if all results match the key
            foreach ($results as $singleResult) {
                if ($singleResult != $key) {
                    return false; // If any result doesn't match, criteria are not met
                }
            }

            // If all results match and there are enough of them, criteria are met
            return count($results) >= $consecutiveCriteria;
        }
    }


    /**
     * Check if the different day criteria are met.
     */
    public function countDifferentDayCriteriaMet($clientId, $targetId, $probeSetId, $currentPhaseId, $key, $result, $consecutiveCriteria)
    {
        log_message('debug', '---------------------countDifferentDayCriteriaMet--------------------------');
        log_message('debug', 'Result ' . $result[0] . ' Key ' . $key);
        // 1. Check if the current unprocessed entry matches the key
        if ($result[0] != $key) {
            // If the current result doesn't match the key, no need to check further
            log_message('debug', 'Result and key not match');
            log_message('debug', '---------------------countDifferentDayCriteriaMet--------------------------');
            return false;
        }

        // 2. Start with the current result being a match
        $consecutiveCount = 1;  // Include the current result in the consecutive count

        // 3. Query to get the most recent processed records for the same phase
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        // 4. Iterate over the results, limit the loop to consecutiveCriteria
        $index = 1;  // Since we already counted the current result
        foreach ($query as $entry) {
            // Stop if we've reached the maximum number of days to check
            if ($index >= $consecutiveCriteria) {
                break;
            }

            // Stop if the phase is different
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Decode the collected data and get the result
            $collectedData = json_decode($entry->collected_data, true);
            $entryResult = isset($collectedData['result'][0]) ? $collectedData['result'][0] : null;
            log_message('debug', 'Previous Result Index: ' . $index . ' Value: ' . $entryResult);
            // If the result is not empty and matches the key, increment the count
            if ($entryResult && $entryResult == $key) {
                $consecutiveCount++;
            } else {
                // If a mismatch is found, break the loop
                break;
            }

            // Increment the index after processing the entry
            $index++;
        }

        log_message('debug', 'consecutiveCount: ' . $consecutiveCount . ' consecutiveCriteria: ' . $consecutiveCriteria);
        log_message('debug', '---------------------countDifferentDayCriteriaMet--------------------------');

        // 5. Compare the consecutive count with the criteria
        return $consecutiveCount >= $consecutiveCriteria;
    }
    /**
     * Check if the different day criteria are met.
     */
    public function countDifferentDayCriteriaMetPercentageYesNoProbe($clientId, $targetId, $probeSetId, $currentPhaseId, $key, $result, $consecutiveCriteria)
    {
        log_message('debug', '---------------------countDifferentDayCriteriaMetPercentageYesNoProbe--------------------------');
        log_message('debug', 'Result ' . $result[0] . ' Key ' . $key);
        // 1. Check if the current unprocessed entry matches the key
        if ($result[0] < $key) {
            // If the current result doesn't match the key, no need to check further
            log_message('debug', 'Result and key not match');
            log_message('debug', '---------------------countDifferentDayCriteriaMetPercentageYesNoProbe--------------------------');
            return false;
        }

        // 2. Start with the current result being a match
        $consecutiveCount = 1;  // Include the current result in the consecutive count

        // 3. Query to get the most recent processed records for the same phase
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        // 4. Iterate over the results, limit the loop to consecutiveCriteria
        $index = 1;  // Since we already counted the current result
        foreach ($query as $entry) {
            // Stop if we've reached the maximum number of days to check
            if ($index >= $consecutiveCriteria) {
                break;
            }

            // Stop if the phase is different
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Decode the collected data and get the result
            $collectedData = json_decode($entry->collected_data, true);
            $entryResult = isset($collectedData['result'][0]) ? $collectedData['result'][0] : null;
            log_message('debug', 'Previous Result Index: ' . $index . ' Value: ' . $entryResult);
            // If the result is not empty and matches the key, increment the count
            if ($entryResult && $entryResult >= $key) {
                $consecutiveCount++;
            } else {
                // If a mismatch is found, break the loop
                break;
            }

            // Increment the index after processing the entry
            $index++;
        }

        log_message('debug', 'consecutiveCount: ' . $consecutiveCount . ' consecutiveCriteria: ' . $consecutiveCriteria);
        log_message('debug', '---------------------countDifferentDayCriteriaMetPercentageYesNoProbe--------------------------');

        // 5. Compare the consecutive count with the criteria
        return $consecutiveCount >= $consecutiveCriteria;
    }
    public function countDifferentDayCriteriaMetStimulusProbeTotalTask($clientId, $targetId, $probeSetId, $currentPhaseId, $key, $result, $consecutiveCriteria)
    {
        log_message('debug', '---------------------countDifferentDayCriteriaMetStimulusProbeTotalTask--------------------------');
        log_message('debug', 'Result ' . $result[0] . ' Key ' . $key);
        // 1. Check if the current unprocessed entry matches the key
        if ($result[0] < $key) {
            // If the current result doesn't match the key, no need to check further
            log_message('debug', 'Result and key not match');
            log_message('debug', '---------------------countDifferentDayCriteriaMetStimulusProbeTotalTask--------------------------');
            return false;
        }

        // 2. Start with the current result being a match
        $consecutiveCount = 1;  // Include the current result in the consecutive count

        // 3. Query to get the most recent processed records for the same phase
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        // 4. Iterate over the results, limit the loop to consecutiveCriteria
        $index = 1;  // Since we already counted the current result
        foreach ($query as $entry) {
            // Stop if we've reached the maximum number of days to check
            if ($index >= $consecutiveCriteria) {
                break;
            }

            // Stop if the phase is different
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Decode the collected data and get the result
            $collectedData = json_decode($entry->collected_data, true);
            $entryResult = isset($collectedData['result'][0]) ? $collectedData['result'][0] : null;
            log_message('debug', 'Previous Result Index: ' . $index . ' Value: ' . $entryResult);
            // If the result is not empty and matches the key, increment the count
            if ($entryResult && $entryResult >= $key) {
                $consecutiveCount++;
            } else {
                // If a mismatch is found, break the loop
                break;
            }

            // Increment the index after processing the entry
            $index++;
        }

        log_message('debug', 'consecutiveCount: ' . $consecutiveCount . ' consecutiveCriteria: ' . $consecutiveCriteria);
        log_message('debug', '---------------------countDifferentDayCriteriaMetStimulusProbeTotalTask--------------------------');

        // 5. Compare the consecutive count with the criteria
        return $consecutiveCount >= $consecutiveCriteria;
    }

    public function countConsecutiveDays($clientId, $targetId, $probeSetId, $currentPhaseId)
    {
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        $consecutiveDays = 1;
        foreach ($query as $entry) {
            if ($entry->current_phase_id != $currentPhaseId) {
                break; // Stop counting if a different phase is found
            }
            $consecutiveDays++;
        }

        return $consecutiveDays;
    }

    // Yes - NO Probes with Frame Set Logic
    public function countFrame1Days($clientId, $targetId, $probeSetId, $currentPhaseId)
    {
        $consecutiveDays = 1; // Start with 1 to include the current day's data

        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            //'current_phase_id' => $currentPhaseId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only count processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);
            //$rule = json_decode($collectedData['rule']['default_rule'], true);

            $frameSetNo = $collectedData['rule']['frame_set_no'] ?? null;

            if ($frameSetNo != 1 || $entry->current_phase_id != $currentPhaseId) {
                break;
            }

            $consecutiveDays++;
        }

        return $consecutiveDays;
    }
    public function countFrame2Days($clientId, $targetId, $probeSetId, $currentPhaseId)
    {
        $consecutiveDays = 1; // Start with 1 to include the current day's data

        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            //'current_phase_id' => $currentPhaseId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only count processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);
            //$rule = json_decode($collectedData['rule']['default_rule'], true);

            $frameSetNo = $collectedData['rule']['frame_set_no'] ?? null;

            if ($frameSetNo != 2 || $entry->current_phase_id != $currentPhaseId) {
                break;
            }

            $consecutiveDays++;
        }

        return $consecutiveDays;
    }

    public function areAllResultsNoForFrame1($clientId, $targetId, $probeSetId, $currentPhaseId, $currentResults, $consecutiveCriteria)
    {
        // 1. Initialize an array to hold all results
        //$allResults = [];

        // 2. Add the current day's unprocessed results first
        //$allResults = array_merge($allResults, $currentResults);
        $allResults = isset($currentResults[0]) ? [$currentResults[0]] : [];

        // 3. Fetch the previously processed records for the current phase and frame 1
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            //'current_phase_id' => $currentPhaseId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only consider processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);
            //$rule = json_decode($collectedData['rule']['default_rule'], true);
            $frameSetNo = $collectedData['rule']['frame_set_no'] ?? null;

            // Break if the frame set number is not 1 or is null
            if ($frameSetNo != 1 || $entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Add these results to the combined results array
            //$allResults = array_merge($allResults, $collectedData['result']);
            if (!empty($collectedData['result'][0])) {
                $allResults[] = $collectedData['result'][0];
            }

            // Stop if we have enough days to check (limit to consecutiveCriteria)
            if (count($allResults) >= $consecutiveCriteria) {
                break;
            }
        }

        // 4. Check if all collected results are 'N'
        $checkResults = array_slice($allResults, 0, $consecutiveCriteria); // Limit to consecutiveCriteria
        foreach ($checkResults as $result) {
            if ($result != 'N') {
                return false; // If any result is not 'N', return false
            }
        }

        return true; // All results within the limit are 'N'
    }

    public function areAllResultsYesForFrame1($clientId, $targetId, $probeSetId, $currentPhaseId, $currentResults, $consecutiveCriteria)
    {
        // 1. Initialize an array to hold all results
        //$allResults = [];

        // 2. Add the current day's unprocessed results first
        //$allResults = array_merge($allResults, $currentResults);
        $allResults = isset($currentResults[0]) ? [$currentResults[0]] : [];

        // 3. Fetch the previously processed records for the current phase and frame 1
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            // 'current_phase_id' => $currentPhaseId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only consider processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);
            //$rule = json_decode($collectedData['rule']['default_rule'], true);
            $frameSetNo = $collectedData['rule']['frame_set_no'] ?? null;

            // Break if the frame set number is not 1 or is null
            if ($frameSetNo != 1 || $entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Add these results to the combined results array
            //$allResults = array_merge($allResults, $collectedData['result']);
            if (!empty($collectedData['result'][0])) {
                $allResults[] = $collectedData['result'][0];
            }

            // Stop if we have enough days to check (limit to consecutiveCriteria)
            if (count($allResults) >= $consecutiveCriteria) {
                break;
            }
        }

        // 4. Check if all collected results are 'Y'
        $checkResults = array_slice($allResults, 0, $consecutiveCriteria); // Limit to consecutiveCriteria
        foreach ($checkResults as $result) {
            if ($result != 'Y') {
                return false; // If any result is not 'Y', return false
            }
        }

        return true; // All results within the limit are 'Y'
    }

    public function checkIfCriteriaMetAcrossFramesWithPhaseCheck($clientId, $targetId, $probeSetId, $currentPhaseId, $key, $currentResults, $consecutiveCriteria)
    {
        // Initialize an array to hold all results
        //$allResults = array_merge([], $currentResults);
        $allResults = isset($currentResults[0]) ? [$currentResults[0]] : [];

        // Fetch the previously processed records for the current phase
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            //'current_phase_id' => $currentPhaseId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1
        ])
            ->orderBy('session_date', 'DESC')
            ->findAll();

        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);
            //$rule = json_decode($collectedData['rule']['default_rule'], true);

            // Break if the phase sequence changes
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            // Add these results to the combined results array
            // $allResults = array_merge($allResults, $collectedData['result']);
            if (!empty($collectedData['result'][0])) {
                $allResults[] = $collectedData['result'][0];
            }

            // Stop if we have enough days to check (limit to consecutiveCriteria)
            if (count($allResults) >= $consecutiveCriteria) {
                break;
            }
        }

        // Check if all collected results match the key (criteria met)
        $checkResults = array_slice($allResults, 0, $consecutiveCriteria);
        foreach ($checkResults as $result) {
            if ($result != $key) {
                return false;
            }
        }

        return true;
    }

    public function calculateDOIForCountProbes($clientId, $targetId, $probeSetId, $currentPhaseId, $minValue, $maxValue)
    {
        // Generate the range array based on min and max values
        $rangeValues = range($minValue, $maxValue);

        // Fetch all relevant processed records for the given client, target, and probe set, ordered by date DESC
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only consider processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        $results = [];

        // Collect results while ensuring they belong to the current phase
        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);

            // Stop if we encounter a phase that doesn't match the current phase
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            $results = array_merge($results, $collectedData['result']);
        }

        // If there's only one result, return false as it's the first entry and we don't have doi information for first result
        if (count($results) < 2) {
            return false;
        }
        // Compare the first two results in the array (latest two results)
        $lastResult = $results[0];        // Most recent result
        $secondLastResult = $results[1];  // Second most recent result

        // Get the indices of the last two results using the dynamic range values
        $lastIndex = array_search($lastResult, $rangeValues);
        $secondLastIndex = array_search($secondLastResult, $rangeValues);

        // Check if the last result represents a move to a higher level of independence
        if ($lastIndex > $secondLastIndex) {
            // Exclude the first two results (most recent two) and check the remaining results
            $previousResults = array_slice($results, 2); // Get all but the first two

            // Check if the lastResult has not been achieved previously
            if (!in_array($lastResult, $previousResults)) {
                return true; // New DOI detected
            }
        }

        return false; // No new DOI detected
    }


    public function calculateDOIForPromptLevels($clientId, $targetId, $probeSetId, $currentPhaseId, $promptLevels)
    {
        // Fetch all relevant processed records for the given client, target, and probe set, ordered by date DESC
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only consider processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        $results = [];

        // Collect results while ensuring they belong to the current phase
        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);

            // Stop if we encounter a phase that doesn't match the current phase
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            $results = array_merge($results, $collectedData['result']);
        }

        // If there's only one result, return false as it's the first entry and we don't have doi information for first result
        if (count($results) < 2) {
            return false;
        }

        // Compare the first two results in the array (latest two results)
        $lastResult = $results[0];        // Most recent result
        $secondLastResult = $results[1];  // Second most recent result

        // Get the indices of the last two results using the dynamic prompt levels
        $lastIndex = array_search($lastResult, $promptLevels);
        $secondLastIndex = array_search($secondLastResult, $promptLevels);

        // Check if the last result represents a move to a higher level of independence
        if ($lastIndex > $secondLastIndex) {
            // Exclude the first two results (most recent two) and check the remaining results
            $previousResults = array_slice($results, 2); // Get all but the first two

            // Check if the lastResult has not been achieved previously
            if (!in_array($lastResult, $previousResults)) {
                return true; // New DOI detected
            }
        }

        return false; // No new DOI detected
    }


    public function calculateDOIForDuration($clientId, $targetId, $probeSetId, $currentPhaseId, $durationSet)
    {
        // Fetch all relevant processed records for the given client, target, and probe set, ordered by date DESC
        $query = $this->where([
            'client_id' => $clientId,
            'target_id' => $targetId,
            'client_probe_set_id' => $probeSetId,
            'is_conflicted' => 0,
            'deleted_at' => null,
            'is_processed' => 1  // Only consider processed entries
        ])
            ->orderBy('session_date', 'DESC')  // Most recent first
            ->findAll();

        $results = [];

        // Collect results while ensuring they belong to the current phase
        foreach ($query as $entry) {
            $collectedData = json_decode($entry->collected_data, true);

            // Stop if we encounter a phase that doesn't match the current phase
            if ($entry->current_phase_id != $currentPhaseId) {
                break;
            }

            $results = array_merge($results, $collectedData['result']);
        }

        // If there's only one result, return false as it's the first entry and we don't have doi information for first result
        if (count($results) < 2) {
            return false;
        }

        // Compare the first two results in the array (latest two results)
        $lastResult = $results[0];        // Most recent result
        $secondLastResult = $results[1];  // Second most recent result

        // Get the indices of the last two results using the dynamic prompt levels
        $lastIndex = array_search($lastResult, $durationSet);
        $secondLastIndex = array_search($secondLastResult, $durationSet);

        // Check if the last result represents a move to a higher level of independence
        if ($lastIndex > $secondLastIndex) {
            // Exclude the first two results (most recent two) and check the remaining results
            $previousResults = array_slice($results, 2); // Get all but the first two

            // Check if the lastResult has not been achieved previously
            if (!in_array($lastResult, $previousResults)) {
                return true; // New DOI detected
            }
        }

        return false; // No new DOI detected
    }
}
