<?php

namespace App\Models\ClientDataSheet;

use CodeIgniter\Model;
use App\Models\MasterProgram\TargetPhaseModel;

class ClientDataSheetModel extends Model
{

    public function getClientProbeSets($clientId, $probeType)
    {
        // Query builder for the client_probe_set table
        $builder = $this->db->table('client_probe_set cps');
        $builder->select('cps.id');

        // Filter by client_id and is_active
        $builder->where('cps.client_id', $clientId);
        $builder->where('cps.is_active', 1); // Ensuring active sets only

        // Use JSON_EXTRACT to filter by the 'type' field within the JSON 'inputs' column
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(cps.inputs, "$.type")) =', $probeType);

        // Execute the query and get results
        $results = $builder->get()->getResultArray();

        // Extract the 'id's from the result set
        $probeSetIds = array_column($results, 'id');

        // Return the array of matching probe set IDs
        return $probeSetIds;
    }

    /*  public function getClientProbeSets($clientId)
    {
        // Base query: selecting all probe sets from target_probe_sets
        $builder = $this->db->table('target_probe_sets tps');
        $builder->select('tps.id as probe_set_id, tps.name as probe_set_name, tps.inputs, cps.id as client_probe_set_id, cps.client_id');

        // Left join to include client-specific probe sets (if available)
        $builder->join('client_probe_set cps', 'cps.probe_set_id = tps.id AND cps.client_id = ' . $clientId, 'left');

        // Execute the query
        $results = $builder->get()->getResultArray();

        // Filter results by probe set type using the 'inputs' JSON column
        $probeSets = [];
        foreach ($results as $row) {
            // Decode the JSON structure in 'inputs'
            $inputs = json_decode($row['inputs'], true);

            // We care about the 'type' key in the JSON
            if (isset($inputs['type'])) {
                $type = $inputs['type'];

                // Based on the type, assign to the relevant tab
                $probeSets[$type] = [
                    'probe_set_id' => $row['probe_set_id'],
                    'probe_set_name' => $row['probe_set_name'],
                    'client_probe_set_id' => $row['client_probe_set_id'],
                    'client_id' => $row['client_id']
                ];
            }
        }

        return $probeSets;
    }*/

    public function getFilteredDomainsByProbeType($clientId, $probeType)
    {
        // Base query: selecting domains that have goals with the specified probe type
        $builder = $this->db->table('client_program_domains d');
        $builder->select('d.id, d.name, d.domain_code');

        // Join with client_program_goals
        $builder->join('client_program_goals g', 'g.domain_id = d.id', 'inner');

        // Join with client_probe_set and target_probe_sets to filter by specified type
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');

        // Ensure we only get domains where probe sets have the specified type
        $builder->where('d.client_id', $clientId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', $probeType);

        // Group by domain to avoid duplicates
        $builder->groupBy('d.id');

        return $builder->get()->getResult();
    }
    public function getGoalsByDomainAndProbeType($clientId, $domainId, $probeType)
    {
        $builder = $this->db->table('client_program_goals g');
        $builder->select('g.id, g.name, g.goal_code');

        // Join to client_probe_set and target_probe_sets to filter by probe type
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');

        // Filter by client, domain, and probe type
        $builder->where('g.client_id', $clientId);
        $builder->where('g.domain_id', $domainId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', $probeType);

        return $builder->get()->getResult();
    }

    public function getDomains($clientId)
    {
        // Base query: selecting domains that have goals with the specified probe type
        $builder = $this->db->table('client_program_domains d');
        $builder->select('d.id, d.name, d.domain_code');
        // Ensure we only get domains where probe sets have the specified type
        $builder->where('d.client_id', $clientId);
        $builder->orderBy('d.domain_code', 'Asc');

        return $builder->get()->getResult();
    }
    public function getGoalsByDomain($clientId, $domainId)
    {
        $builder = $this->db->table('client_program_goals g');
        $builder->select('g.id, g.name, g.goal_code');
        // Filter by client, domain, and probe type
        $builder->where('g.client_id', $clientId);
        $builder->where('g.domain_id', $domainId);
        $builder->orderBy('g.goal_code', 'Asc');

        return $builder->get()->getResult();
    }


    /*public function getClientDataSheet($clientId, $probeSetId)
    {
        // Build the query with joins as discussed
        $builder = $this->db->table('client_program_targets t');
        $builder->select([
            'd.id AS domain_id',
            'd.name AS domain_name',
            'd.domain_code',
            'g.id AS goal_id',
            'g.name AS goal_name',
            'g.goal_code',
            't.id AS target_id',
            't.name AS target_name',
            't.description AS target_desc',

            // Session Data
            'dc.id AS session_data_id',
            'dc.session_id',
            'dc.session_date',
            'dc.current_phase_id AS data_phase_id',
            'dc.collected_data', // Get the whole JSON data

            // Processing Data
            'dp.id AS processed_data_id',
            'dp.is_program_changed',
            'dp.next_phase_id AS current_phase_id',
            'dp.client_probe_set_id',

            // Program Change Alert Data
            'dpca.id AS prog_ch_alert',

            // Program Change Details
            'cpc.id AS prog_ch_made',

            // Target Retained Information
            'tr.id AS retained_id'
        ]);

        // Joining tables
        $builder->join('client_program_goals g', 'g.id = t.goal_id');
        $builder->join('client_program_domains d', 'd.id = g.domain_id');
        $builder->join('daily_session_data_collection dc', 'dc.target_id = t.id and is_processed=1', 'left');
        $builder->join('daily_session_data_processed dp', 'dp.target_id = t.id AND dp.collection_id = dc.id', 'left');
        $builder->join('client_program_change_alert dpca', 'dpca.processed_data_id = dp.id', 'left');
        $builder->join('client_program_change cpc', 'cpc.alert_id = dpca.id', 'left');
        $builder->join('client_program_targets_retained tr', 'tr.target_id = t.id', 'left');

        // Filtering by client ID and probe set ID
        $builder->where('d.client_id', $clientId);
        $builder->where('dc.client_probe_set_id', $probeSetId);

        // Ordering by domain, goal, and session date
        $builder->orderBy('d.id, g.id, t.id, dc.session_date', 'ASC');

        // Execute the query and get the result
        $query = $builder->get();

        return $query->getResultArray();
    }*/

    /*public function getClientDataSheet($clientId, $clientProbeSetIds, $domainId = null, $goalId = null)
    {
        // Build the query with joins as discussed
        $builder = $this->db->table('client_program_targets t');
        $builder->select([
            'd.id AS domain_id',
            'd.name AS domain_name',
            'd.domain_code',
            'g.id AS goal_id',
            'g.name AS goal_name',
            'g.goal_code',
            'tps.name as probe_set_name',
            'tpc.name as combination_name',

            't.id AS target_id',
            't.name AS target_name',
            't.description AS target_desc',

            'overrides.consecutive_criteria as override_consecutive_criteria',
            //counts for program alerts and program changes
            

            // Session Data
            'dc.id AS session_data_id',
            'dc.session_id',
            'dc.session_date',
            'dc.current_phase_id AS data_phase_id',
            'dc.collected_data', // Get the whole JSON data

            // Processing Data
            'dp.id AS processed_data_id',
            'dp.is_program_changed',
            'dp.next_phase_id AS processed_next_phase_id',
            'dp.client_probe_set_id',

            // Program Change Alert Data
            'dpca.id AS prog_ch_alert',

            // Program Change Details
            'cpc.id AS prog_ch_made',

            // Target Retained Information
            'tr.id AS retained_id',
            // Real current phase information from the second join
            'dpa.next_phase_id AS current_phase_id' // Real current phase of the targe
        ]);

        // Joining tables
        $builder->join('client_program_goals g', 'g.id = t.goal_id');
        $builder->join('client_probe_set cps', 'g.id = cps.goal_id AND cps.is_active = 1', 'left');
        $builder->join('target_probe_sets tps', 'cps.probe_set_id  = tps.id ', 'left');
        $builder->join('target_phase_combinations tpc', 'cps.combination_id  = tpc.id', 'left');

        $builder->join('client_program_domains d', 'd.id = g.domain_id');
        $builder->join('daily_session_data_collection dc', 'dc.target_id = t.id AND dc.is_processed=1 AND dc.client_probe_set_id=cps.id', 'left');
        $builder->join('daily_session_data_processed dp', 'dp.target_id = t.id AND dp.collection_id = dc.id AND dp.client_probe_set_id=cps.id ', 'left');


        // join for the real current phase with condition is_active = 1
        $builder->join('daily_session_data_processed dpa', 'dpa.target_id = t.id AND dpa.is_active = 1', 'left');

        $builder->join('client_program_change_alert dpca', 'dpca.processed_data_id = dp.id', 'left');
        $builder->join('client_program_change cpc', 'cpc.alert_id = dpca.id', 'left');
        $builder->join('client_program_targets_retained tr', 'tr.target_id = t.id', 'left');


        // Add joins for program alerts and changes with the same criteria as overrides
        $builder->join('client_program_targets_overrides overrides', 'overrides.target_id = t.id AND overrides.probe_set_id  = cps.id', 'left');
        


        // Filtering by client ID and probe set ID
        $builder->where('d.client_id', $clientId);
        if ($clientProbeSetIds != null) {
            $builder->whereIn('cps.id', $clientProbeSetIds);
        }


        // Apply additional filters if provided
        if ($domainId) {
            $builder->where('d.id', $domainId); // Filter by domain
        }
        if ($goalId) {
            $builder->where('g.id', $goalId); // Filter by goal
        }


        
        // Ordering by domain, goal, and session date
        $builder->orderBy('d.domain_code, g.goal_code, t.name, dc.session_date', 'ASC');

        // Execute the query and get the result
        $query = $builder->get();

        return $query->getResultArray(); // Return object as requested
    }*/
    public function getClientDataSheet($clientId, $clientProbeSetIds, $domainId = null, $goalId = null)
    {
        // Prepare the base SQL query with CTEs
        $sql = "
        WITH ProgramAlertCounts AS (
            SELECT client_id, target_id, client_probe_set_id, COUNT(*) AS program_alert_count
            FROM client_program_change_alert
            GROUP BY client_id, target_id, client_probe_set_id
        ),
        ProgramChangeCounts AS (
            SELECT client_id, target_id, client_probe_set_id, COUNT(*) AS program_change_count
            FROM client_program_change
            GROUP BY client_id, target_id, client_probe_set_id
        )
        SELECT 
            d.id AS domain_id,
            d.name AS domain_name,
            d.domain_code,
            g.id AS goal_id,
            g.name AS goal_name,
            g.goal_code,
            tps.name AS probe_set_name,
            tpc.name AS combination_name,

            cps.inputs AS probe_set_key,
            cpr1.rules AS teaching_phase_rule,
            cpr2.rules AS retention_phase_rule,

            t.id AS target_id,
            t.name AS target_name,
            t.description AS target_desc,

            overrides.consecutive_criteria AS override_consecutive_criteria,

            -- Counts from CTEs
            COALESCE(ProgramAlertCounts.program_alert_count, 0) AS program_alert_count,
            COALESCE(ProgramChangeCounts.program_change_count, 0) AS program_change_count,

            -- Session Data
            dc.id AS session_data_id,
            dc.session_id,
            dc.session_date,
            dc.current_phase_id AS data_phase_id,
            dc.collected_data,

            -- Processing Data
            dp.id AS processed_data_id,
            dp.is_program_changed,
            dp.next_phase_id AS processed_next_phase_id,
            dp.client_probe_set_id,

            -- Program Change Alert Data
            dpca.id AS prog_ch_alert,

            -- Program Change Details
            cpc.id AS prog_ch_made,

            -- Target Retained Information
            tr.id AS retained_id,

            -- Real current phase information
            dpa.next_phase_id AS current_phase_id,

            ctsc.method AS chain_method

        FROM client_program_targets t
        JOIN client_program_goals g ON g.id = t.goal_id
        JOIN client_program_domains d ON d.id = g.domain_id
        
        LEFT JOIN client_probe_set cps ON g.id = cps.goal_id AND cps.is_active = 1
        LEFT JOIN client_probe_rules cpr1 ON cps.id = cpr1.client_probe_set_id AND cpr1.phase_id = 2
        LEFT JOIN client_probe_rules cpr2 ON cps.id = cpr2.client_probe_set_id AND cpr2.phase_id = 3

        LEFT JOIN target_probe_sets tps ON cps.probe_set_id = tps.id
        LEFT JOIN target_phase_combinations tpc ON cps.combination_id = tpc.id
       
        LEFT JOIN daily_session_data_collection dc ON dc.target_id = t.id AND dc.is_processed = 1 AND dc.client_probe_set_id = cps.id
        LEFT JOIN daily_session_data_processed dp ON dp.target_id = t.id AND dp.collection_id = dc.id AND dp.client_probe_set_id = cps.id
        LEFT JOIN daily_session_data_processed dpa ON dpa.target_id = t.id AND dpa.is_active = 1
        LEFT JOIN client_program_change_alert dpca ON dpca.processed_data_id = dp.id
        LEFT JOIN client_program_change cpc ON cpc.alert_id = dpca.id
        LEFT JOIN client_program_targets_retained tr ON tr.target_id = t.id
        LEFT JOIN client_program_targets_overrides overrides ON overrides.target_id = t.id AND overrides.probe_set_id = cps.id
        LEFT JOIN client_target_stimulus_chains ctsc ON ctsc.target_id = t.id
        -- Join CTEs
        LEFT JOIN ProgramAlertCounts ON ProgramAlertCounts.target_id = t.id AND ProgramAlertCounts.client_probe_set_id = cps.id AND ProgramAlertCounts.client_id = d.client_id
        LEFT JOIN ProgramChangeCounts ON ProgramChangeCounts.target_id = t.id AND ProgramChangeCounts.client_probe_set_id = cps.id AND ProgramChangeCounts.client_id = d.client_id

        

        WHERE d.client_id = ?
    ";

        // Prepare the parameters array
        $params = [$clientId];

        // If clientProbeSetIds is not empty, add it to the WHERE IN clause
        if ($clientProbeSetIds != null) {
            $sql .= " AND cps.id IN (" . implode(',', $clientProbeSetIds) . ")";
        }
        if ($domainId) {
            $sql .= " AND d.id = ?";
            $params[] = $domainId;
        }
        if ($goalId) {
            $sql .= " AND g.id = ?";
            $params[] = $goalId;
        }

        // Add ordering
        $sql .= " ORDER BY d.domain_code, g.goal_code, t.name, dc.session_date ASC";

        // Execute the query with all parameters
        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }



    public function getTargetPhasesArray()
    {
        $targetPhaseModel = new TargetPhaseModel();
        $phases = $targetPhaseModel->findAll();
        $phaseArray = [];
        foreach ($phases as $phase) {
            // Assign the ID as the index and the name as the value in the array
            $phaseArray[$phase['id']] = $phase['name'];
        }
        return $phaseArray;
    }
    public function getDataSheetInformation($clientId, $clientProbeSetIds, $filterDomain = null, $filterGoal = null)
    {
        $queryResult = $this->getClientDataSheet($clientId, $clientProbeSetIds, $filterDomain, $filterGoal);
        $phases = $this->getTargetPhasesArray();

        // Organize the data into a hierarchical structure
        $data = [];

        foreach ($queryResult as $row) {
            $domainId = $row['domain_id'];
            $goalId = $row['goal_id'];
            $targetId = $row['target_id'];

            // Check if domain is already set
            if (!isset($data[$domainId])) {
                $data[$domainId] = [
                    'domain_name' => $row['domain_name'],
                    'domain_code' => $row['domain_code'],
                    'goals' => []
                ];
            }

            // Check if goal is already set
            if (!isset($data[$domainId]['goals'][$goalId])) {
                $data[$domainId]['goals'][$goalId] = [
                    'goal_name' => $row['goal_name'],
                    'goal_code' => $row['goal_code'],
                    'probe_set_name' => $row['probe_set_name'],
                    'combination_name' => $row['combination_name'],
                    'probe_set_key' => isset($row['probe_set_key']) && !is_null($row['probe_set_key'])
                        ? json_decode($row['probe_set_key'])
                        : null,
                    'teaching_phase_rule' => isset($row['teaching_phase_rule']) && !is_null($row['teaching_phase_rule'])
                        ? json_decode($row['teaching_phase_rule'])
                        : null,

                    'retention_phase_rule' => isset($row['retention_phase_rule']) && !is_null($row['retention_phase_rule'])
                        ? json_decode($row['retention_phase_rule'])
                        : null,
                    'targets' => []
                ];
            }

            // Check if target is already set
            if (!isset($data[$domainId]['goals'][$goalId]['targets'][$targetId])) {
                $sessionData = [];

                // If session data exists
                if ($row['session_date'] !== null) {
                    // Decode the JSON from collected_data
                    $collectedData = json_decode($row['collected_data'], true);

                    // Extract necessary fields from the JSON
                    $result = isset($collectedData['result']) ? $collectedData['result'] : null;
                    $success_key = isset($collectedData['inputs']['key']) ? $collectedData['inputs']['key'] : null;
                    $probe_type = $collectedData['inputs']['type'];
                    $frameSetNo = isset($collectedData['rule']['frame_set_no']) ? $collectedData['rule']['frame_set_no'] : null;
                    $statistics = isset($collectedData['statistics']) ? $collectedData['statistics'] : null;
                    $chain_method = isset($collectedData['method']) ? $collectedData['method'] : null;
                    // Add session data
                    $sessionData[] = [
                        'session_data_id' => $row['session_data_id'],
                        'session_id' => $row['session_id'],
                        'session_date' => $row['session_date'],
                        'probe_type' => $probe_type, // Probe type
                        'result' => $result, // Result from JSON
                        'statistics' => $statistics, // Result from JSON
                        'chain_method' => $chain_method,
                        'success_key' => $success_key, // Key from JSON
                        'data_phase_id' => $row['data_phase_id'], // Current phase from collection
                        'phase_name' => $row['data_phase_id'] ? $phases[$row['data_phase_id']] : '',
                        'data_frame_set' => $frameSetNo, // Frame set number from JSON
                        'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                        'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                        'prog_ch' => $row['is_program_changed'], // Program change alert
                        'prog_ch_made' => $row['prog_ch_made'], // Program change applied
                    ];
                }

                // Add target data
                $data[$domainId]['goals'][$goalId]['targets'][$targetId] = [
                    'client_id' => $clientId,
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name'],
                    'target_desc' => $row['target_desc'],
                    'current_phase_id' => $row['current_phase_id'], // Current phase from processing data
                    'phase_name' => $row['current_phase_id'] ? $phases[$row['current_phase_id']] : '',
                    'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                    'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                    'retained' => $row['retained_id'] ? true : false, // Whether the target is retained
                    'override_consecutive_criteria' => $row['override_consecutive_criteria'], // Target override
                    'program_alert_count' => $row['program_alert_count'], // program alert count
                    'program_change_count' => $row['program_change_count'], // program change count
                    'target_chain_method' => $row['chain_method'], // target_chain_method
                    'session_data' => $sessionData,
                ];
            } else {
                // If target is already set, add the session data if available
                if ($row['session_date'] !== null) {
                    // Decode the JSON from collected_data
                    $collectedData = json_decode($row['collected_data'], true);

                    // Extract necessary fields from the JSON
                    $result = isset($collectedData['result']) ? $collectedData['result'] : null;
                    $success_key = isset($collectedData['inputs']['key']) ? $collectedData['inputs']['key'] : null;
                    $probe_type = $collectedData['inputs']['type'];
                    $frameSetNo = isset($collectedData['rule']['frame_set_no']) ? $collectedData['rule']['frame_set_no'] : null;
                    $statistics = isset($collectedData['statistics']) ? $collectedData['statistics'] : null;
                    $chain_method = isset($collectedData['method']) ? $collectedData['method'] : null;

                    // Add session data
                    $data[$domainId]['goals'][$goalId]['targets'][$targetId]['session_data'][] = [
                        'session_data_id' => $row['session_data_id'],
                        'session_id' => $row['session_id'],
                        'session_date' => $row['session_date'],
                        'probe_type' => $probe_type, // Probe type
                        'result' => $result, // Result from JSON
                        'statistics' => $statistics,
                        'chain_method' => $chain_method,
                        'success_key' => $success_key, // Key from JSON
                        'data_phase_id' => $row['data_phase_id'], // Current phase from collection
                        'phase_name' => $row['data_phase_id'] ? $phases[$row['data_phase_id']] : '',
                        'data_frame_set' => $frameSetNo, // Frame set number from JSON
                        'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                        'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                        'prog_ch' => $row['is_program_changed'], // Program change alert
                        'prog_ch_made' => $row['prog_ch_made'], // Program change applied
                    ];
                }
            }
        }
        // Attach latest session date to each target for frontend sorting
        foreach ($data as &$domain) {
            foreach ($domain['goals'] as &$goal) {
                foreach ($goal['targets'] as &$target) {
                    $latestDate = null;
                    if (!empty($target['session_data'])) {
                        $dates = array_column($target['session_data'], 'session_date');
                        $latestDate = max($dates);
                    }
                    $target['latest_session_date'] = $latestDate;
                }
            }
        }
        unset($domain, $goal, $target); // Best practice: break references

        return $data;
    }

    public function getSkillsRetained($client_id, $domain_id = null, $goal_id = null, $probe_set_id = null)
    {
        $builder = $this->db->table('client_program_targets_retained r')
            ->select('r.session_date, d.name as domain_name, d.domain_code, 
                      g.name as goal_name, g.goal_code, 
                      t.name as target_name, 
                      ps.name as probe_set_name')
            ->join('client_program_domains d', 'r.domain_id = d.id', 'left')
            ->join('client_program_goals g', 'r.goal_id = g.id', 'left')
            ->join('client_program_targets t', 'r.target_id = t.id', 'left')
            ->join('client_probe_set cps', 'r.client_probe_set_id = cps.id', 'left')
            ->join('target_probe_sets ps', 'cps.probe_set_id = ps.id', 'left')
            ->where('r.client_id', $client_id);

        // Apply domain filter if provided
        if ($domain_id !== null && $domain_id !== '') {
            $builder->where('r.domain_id', $domain_id);
        }

        // Apply goal filter if provided
        if ($goal_id !== null && $goal_id !== '') {
            $builder->where('r.goal_id', $goal_id);
        }

        // Apply goal filter if provided
        if ($probe_set_id !== null && $probe_set_id !== '') {
            $builder->where('ps.id', $probe_set_id);
        }

        // Order by session_date in descending order (latest first)
        $builder->orderBy('r.session_date', 'DESC')->orderBy('d.domain_code', 'ASC')->orderBy('g.goal_code', 'ASC')->orderBy('t.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getDOITargets($client_id, $domain_id = null, $goal_id = null, $probe_set_id = null)
    {
        $builder = $this->db->table('client_program_targets_doi doi')
            ->select('doi.target_id, doi.session_date, d.name as domain_name, d.domain_code, 
                  g.name as goal_name, g.goal_code, 
                  t.name as target_name, 
                  ps.name as probe_set_name, doi.doi_value')
            ->join('client_program_domains d', 'doi.domain_id = d.id', 'left')
            ->join('client_program_goals g', 'doi.goal_id = g.id', 'left')
            ->join('client_program_targets t', 'doi.target_id = t.id', 'left')
            ->join('client_probe_set cps', 'doi.client_probe_set_id = cps.id', 'left')
            ->join('target_probe_sets ps', 'cps.probe_set_id = ps.id', 'left')
            ->where('doi.client_id', $client_id);

        // Apply domain filter if provided
        if ($domain_id !== null && $domain_id !== '') {
            $builder->where('doi.domain_id', $domain_id);
        }

        // Apply goal filter if provided
        if ($goal_id !== null && $goal_id !== '') {
            $builder->where('doi.goal_id', $goal_id);
        }

        // Apply goal filter if provided
        if ($probe_set_id !== null && $probe_set_id !== '') {
            $builder->where('ps.id', $probe_set_id);
        }

        // Order by session_date within each group (latest first)
        $builder->orderBy('doi.session_date', 'DESC')->orderBy('d.domain_code', 'ASC')->orderBy('g.goal_code', 'ASC')->orderBy('t.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getProgramChangeData($client_id, $domain_id = null, $goal_id = null, $probe_set_id = null)
    {
        $builder = $this->db->table('client_program_change_alert cpa')
            ->select('cpa.id as alert_id, cpc.id as change_id, cpa.session_date,cpa.is_change_made, d.name as domain_name, d.domain_code, 
                      g.name as goal_name, g.goal_code,cpa.target_id, t.name as target_name, ps.name as probe_set_name, 
                      cpc.consecutive_criteria, cpc.incorrect_response, cpc.behavioral_variables, cpc.description')
            ->join('client_program_change cpc', 'cpc.alert_id = cpa.id', 'left')
            ->join('client_program_domains d', 'cpa.domain_id = d.id', 'left')
            ->join('client_program_goals g', 'cpa.goal_id = g.id', 'left')
            ->join('client_program_targets t', 'cpa.target_id = t.id', 'left')
            ->join('client_probe_set cps', 'cpa.client_probe_set_id = cps.id', 'left')
            ->join('target_probe_sets ps', 'cps.probe_set_id = ps.id', 'left')
            ->where('cpa.client_id', $client_id);

        // Apply filters
        if ($domain_id !== null && $domain_id !== '') {
            $builder->where('cpa.domain_id', $domain_id);
        }
        if ($goal_id !== null && $goal_id !== '') {
            $builder->where('cpa.goal_id', $goal_id);
        }
        if ($probe_set_id !== null && $probe_set_id !== '') {
            $builder->where('ps.id', $probe_set_id);
        }

        // Order by session_date and target_id
        //$builder->orderBy('cpa.target_id', 'DESC');
        $builder->orderBy('cpa.session_date', 'DESC')->orderBy('d.domain_code', 'ASC')->orderBy('g.goal_code', 'ASC')->orderBy('t.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getSingleTargetData($clientId, $probeSetId, $domainId, $goalId, $targetId)
    {
        // Prepare the base SQL query with CTEs
        $sql = "
       WITH ProgramAlertCounts AS (
           SELECT client_id, target_id, client_probe_set_id, COUNT(*) AS program_alert_count
           FROM client_program_change_alert
           GROUP BY client_id, target_id, client_probe_set_id
       ),
       ProgramChangeCounts AS (
           SELECT client_id, target_id, client_probe_set_id, COUNT(*) AS program_change_count
           FROM client_program_change
           GROUP BY client_id, target_id, client_probe_set_id
       )
       SELECT 
           d.id AS domain_id,
           d.name AS domain_name,
           d.domain_code,
           g.id AS goal_id,
           g.name AS goal_name,
           g.goal_code,
           tps.name AS probe_set_name,
           tpc.name AS combination_name,

           cps.inputs AS probe_set_key,
           cpr1.rules AS teaching_phase_rule,
           cpr2.rules AS retention_phase_rule,

           t.id AS target_id,
           t.name AS target_name,
           t.description AS target_desc,

           overrides.consecutive_criteria AS override_consecutive_criteria,

           -- Counts from CTEs
           COALESCE(ProgramAlertCounts.program_alert_count, 0) AS program_alert_count,
           COALESCE(ProgramChangeCounts.program_change_count, 0) AS program_change_count,

           -- Session Data
           dc.id AS session_data_id,
           dc.session_id,
           dc.session_date,
           dc.current_phase_id AS data_phase_id,
           dc.collected_data,

           -- Processing Data
           dp.id AS processed_data_id,
           dp.is_program_changed,
           dp.next_phase_id AS processed_next_phase_id,
           dp.client_probe_set_id,

           -- Program Change Alert Data
           dpca.id AS prog_ch_alert,

           -- Program Change Details
           cpc.id AS prog_ch_made,

           -- Target Retained Information
           tr.id AS retained_id,

           -- Real current phase information
           dpa.next_phase_id AS current_phase_id,
           ctsc.method AS chain_method

       FROM client_program_targets t
       JOIN client_program_goals g ON g.id = t.goal_id
       JOIN client_program_domains d ON d.id = g.domain_id
       
       LEFT JOIN client_probe_set cps ON g.id = cps.goal_id AND cps.is_active = 1
       LEFT JOIN client_probe_rules cpr1 ON cps.id = cpr1.client_probe_set_id AND cpr1.phase_id = 2
       LEFT JOIN client_probe_rules cpr2 ON cps.id = cpr2.client_probe_set_id AND cpr2.phase_id = 3

       LEFT JOIN target_probe_sets tps ON cps.probe_set_id = tps.id
       LEFT JOIN target_phase_combinations tpc ON cps.combination_id = tpc.id
      
       LEFT JOIN daily_session_data_collection dc ON dc.target_id = t.id AND dc.is_processed = 1 AND dc.client_probe_set_id = cps.id
       LEFT JOIN daily_session_data_processed dp ON dp.target_id = t.id AND dp.collection_id = dc.id AND dp.client_probe_set_id = cps.id
       LEFT JOIN daily_session_data_processed dpa ON dpa.target_id = t.id AND dpa.is_active = 1
       LEFT JOIN client_program_change_alert dpca ON dpca.processed_data_id = dp.id
       LEFT JOIN client_program_change cpc ON cpc.alert_id = dpca.id
       LEFT JOIN client_program_targets_retained tr ON tr.target_id = t.id
       LEFT JOIN client_program_targets_overrides overrides ON overrides.target_id = t.id AND overrides.probe_set_id = cps.id
       LEFT JOIN client_target_stimulus_chains ctsc ON ctsc.target_id = t.id
       -- Join CTEs
       LEFT JOIN ProgramAlertCounts ON ProgramAlertCounts.target_id = t.id AND ProgramAlertCounts.client_probe_set_id = cps.id AND ProgramAlertCounts.client_id = d.client_id
       LEFT JOIN ProgramChangeCounts ON ProgramChangeCounts.target_id = t.id AND ProgramChangeCounts.client_probe_set_id = cps.id AND ProgramChangeCounts.client_id = d.client_id

       WHERE d.client_id = ?
   ";

        // Prepare the parameters array
        $params = [$clientId];

        // If clientProbeSetIds is not empty, add it to the WHERE IN clause
        if ($probeSetId) {
            $sql .= " AND cps.id = ?";
            $params[] = $probeSetId;
        }
        if ($domainId) {
            $sql .= " AND d.id = ?";
            $params[] = $domainId;
        }
        if ($goalId) {
            $sql .= " AND g.id = ?";
            $params[] = $goalId;
        }
        if ($targetId) {
            $sql .= " AND t.id = ?";
            $params[] = $targetId;
        }

        // Add ordering
        $sql .= " ORDER BY d.domain_code, g.goal_code, t.name, dc.session_date ASC";

        // Execute the query with all parameters
        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }

    public function getSingleTargetDataSheetInformation($clientId, $probeSetId, $domainId, $goalId, $targetId)
    {
        $queryResult = $this->getSingleTargetData($clientId, $probeSetId, $domainId, $goalId, $targetId);

        $phases = $this->getTargetPhasesArray();

        // Organize the data into a hierarchical structure
        $data = [];

        foreach ($queryResult as $row) {
            $domainId = $row['domain_id'];
            $goalId = $row['goal_id'];
            $targetId = $row['target_id'];

            // Check if domain is already set
            if (!isset($data[$domainId])) {
                $data[$domainId] = [
                    'domain_name' => $row['domain_name'],
                    'domain_code' => $row['domain_code'],
                    'goals' => []
                ];
            }

            // Check if goal is already set
            if (!isset($data[$domainId]['goals'][$goalId])) {
                $data[$domainId]['goals'][$goalId] = [
                    'goal_name' => $row['goal_name'],
                    'goal_code' => $row['goal_code'],
                    'probe_set_name' => $row['probe_set_name'],
                    'combination_name' => $row['combination_name'],
                    'probe_set_key' => isset($row['probe_set_key']) && !is_null($row['probe_set_key'])
                        ? json_decode($row['probe_set_key'])
                        : null,
                    'teaching_phase_rule' => isset($row['teaching_phase_rule']) && !is_null($row['teaching_phase_rule'])
                        ? json_decode($row['teaching_phase_rule'])
                        : null,

                    'retention_phase_rule' => isset($row['retention_phase_rule']) && !is_null($row['retention_phase_rule'])
                        ? json_decode($row['retention_phase_rule'])
                        : null,
                    'targets' => []
                ];
            }

            // Check if target is already set
            if (!isset($data[$domainId]['goals'][$goalId]['targets'][$targetId])) {
                $sessionData = [];

                // If session data exists
                if ($row['session_date'] !== null) {
                    // Decode the JSON from collected_data
                    $collectedData = json_decode($row['collected_data'], true);

                    // Extract necessary fields from the JSON
                    $result = isset($collectedData['result']) ? $collectedData['result'] : null;
                    $success_key = isset($collectedData['inputs']['key']) ? $collectedData['inputs']['key'] : null;
                    $probe_type = $collectedData['inputs']['type'];
                    $frameSetNo = isset($collectedData['rule']['frame_set_no']) ? $collectedData['rule']['frame_set_no'] : null;
                    $statistics = isset($collectedData['statistics']) ? $collectedData['statistics'] : null;
                    $chain_method = isset($collectedData['method']) ? $collectedData['method'] : null;
                    // Add session data
                    $sessionData[] = [
                        'session_data_id' => $row['session_data_id'],
                        'session_id' => $row['session_id'],
                        'session_date' => $row['session_date'],
                        'probe_type' => $probe_type, // Probe type
                        'result' => $result, // Result from JSON
                        'statistics' => $statistics, // Result from JSON
                        'chain_method' => $chain_method,
                        'success_key' => $success_key, // Key from JSON
                        'data_phase_id' => $row['data_phase_id'], // Current phase from collection
                        'phase_name' => $row['data_phase_id'] ? $phases[$row['data_phase_id']] : '',
                        'data_frame_set' => $frameSetNo, // Frame set number from JSON
                        'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                        'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                        'prog_ch' => $row['is_program_changed'], // Program change alert
                        'prog_ch_made' => $row['prog_ch_made'], // Program change applied
                    ];
                }

                // Add target data
                $data[$domainId]['goals'][$goalId]['targets'][$targetId] = [
                    'client_id' => $clientId,
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name'],
                    'target_desc' => $row['target_desc'],
                    'current_phase_id' => $row['current_phase_id'], // Current phase from processing data
                    'phase_name' => $row['current_phase_id'] ? $phases[$row['current_phase_id']] : '',
                    'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                    'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                    'retained' => $row['retained_id'] ? true : false, // Whether the target is retained
                    'override_consecutive_criteria' => $row['override_consecutive_criteria'], // Target override
                    'program_alert_count' => $row['program_alert_count'], // program alert count
                    'program_change_count' => $row['program_change_count'], // program change count
                    'target_chain_method' => $row['chain_method'], // target_chain_method
                    'session_data' => $sessionData,
                ];
            } else {
                // If target is already set, add the session data if available
                if ($row['session_date'] !== null) {
                    // Decode the JSON from collected_data
                    $collectedData = json_decode($row['collected_data'], true);

                    // Extract necessary fields from the JSON
                    $result = isset($collectedData['result']) ? $collectedData['result'] : null;
                    $success_key = isset($collectedData['inputs']['key']) ? $collectedData['inputs']['key'] : null;
                    $probe_type = $collectedData['inputs']['type'];
                    $frameSetNo = isset($collectedData['rule']['frame_set_no']) ? $collectedData['rule']['frame_set_no'] : null;
                    $statistics = isset($collectedData['statistics']) ? $collectedData['statistics'] : null;
                    $chain_method = isset($collectedData['method']) ? $collectedData['method'] : null;

                    // Add session data
                    $data[$domainId]['goals'][$goalId]['targets'][$targetId]['session_data'][] = [
                        'session_data_id' => $row['session_data_id'],
                        'session_id' => $row['session_id'],
                        'session_date' => $row['session_date'],
                        'probe_type' => $probe_type, // Probe type
                        'result' => $result, // Result from JSON
                        'statistics' => $statistics,
                        'chain_method' => $chain_method,
                        'success_key' => $success_key, // Key from JSON
                        'data_phase_id' => $row['data_phase_id'], // Current phase from collection
                        'phase_name' => $row['data_phase_id'] ? $phases[$row['data_phase_id']] : '',
                        'data_frame_set' => $frameSetNo, // Frame set number from JSON
                        'prog_ch_alert_date' => $row['session_date'], // Program change alert date
                        'prog_ch_alert' => $row['prog_ch_alert'], // Program change alert
                        'prog_ch' => $row['is_program_changed'], // Program change alert
                        'prog_ch_made' => $row['prog_ch_made'], // Program change applied
                    ];
                }
            }
        }
        // Attach latest session date to each target for frontend sorting
        foreach ($data as &$domain) {
            foreach ($domain['goals'] as &$goal) {
                foreach ($goal['targets'] as &$target) {
                    $latestDate = null;
                    if (!empty($target['session_data'])) {
                        $dates = array_column($target['session_data'], 'session_date');
                        $latestDate = max($dates);
                    }
                    $target['latest_session_date'] = $latestDate;
                }
            }
        }
        unset($domain, $goal, $target); // Best practice: break references
        return $data;
    }
}
