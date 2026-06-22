<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProgramModel  extends Model
{
    protected $DBGroup          = 'default';

    public function getClientProgramInfo($client_id)
    {
        $sql = "SELECT 
            cpd.id AS id, 
            cpd.name AS name, 
            cpd.domain_code, 
            cpg.id AS goal_id, 
            cpg.name AS goal_name, 
            cpg.goal_code, 
            cpt.id AS target_id, 
            cpt.name AS target_name, 
            cpt.on_hold AS target_on_hold,
            cps.id AS probe_set_id,             
            tps.name AS probe_set_name,
            tps.inputs AS probe_set_inputs,
            tpc.name AS combination_name,
            vtss.step_count AS total_steps,
            vtss.chaining_method,
            vtss.rule_override
        FROM client_program_domains cpd
        LEFT JOIN client_program_goals cpg 
            ON cpg.domain_id = cpd.id  
            AND cpg.client_id = ?
        LEFT JOIN client_program_targets cpt 
            ON cpt.goal_id = cpg.id 
            AND cpt.client_id = ?
        LEFT JOIN client_probe_set cps 
            ON cps.goal_id = cpg.id 
            AND cps.client_id = ? 
            AND cps.is_active = 1
        LEFT JOIN target_probe_sets tps 
            ON tps.id = cps.probe_set_id
        LEFT JOIN target_phase_combinations tpc 
            ON tpc.id = cps.combination_id
        LEFT JOIN view_target_stimulus_step_summary vtss
            ON vtss.target_id = cpt.id
        WHERE cpd.client_id = ?
        ORDER BY 
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpd.domain_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpd.domain_code ASC,
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpg.goal_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpg.goal_code ASC,
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpt.name, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpt.name ASC";


        $query = $this->db->query($sql, [$client_id, $client_id, $client_id, $client_id]); // Use bindings to prevent SQL injection
        $results = $query->getResultArray(); // Convert to array


        $clientProgram = [];

        foreach ($results as $row) {

            // Populate domains
            $domainId = $row['id'];
            if (!isset($clientProgram[$domainId])) {
                $clientProgram[$domainId] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'domain_code' => $row['domain_code'],
                    'goals' => []
                ];
            }

            // Populate goals within domains
            // Decode probe_set_inputs and get type
            $inputs = json_decode($row['probe_set_inputs'] ?? '', true);
            $probeType = $inputs['type'] ?? null;
            $goalId = $row['goal_id'];
            if ($goalId && !isset($clientProgram[$domainId]['goals'][$goalId])) {
                $clientProgram[$domainId]['goals'][$goalId] = [
                    'id' => $goalId,
                    'name' => $row['goal_name'],
                    'goal_code' => $row['goal_code'],
                    'targets' => [],
                    'probe_set' => [
                        'id' => $row['probe_set_id'],
                        'name' => $row['probe_set_name'],
                        'combination_name' => $row['combination_name'],
                        'type' => $probeType, // e.g., "stimulus_program"
                    ]
                ];
            }

            // Populate targets within goals
            if ($row['target_id']) {
                $clientProgram[$domainId]['goals'][$goalId]['targets'][] = [
                    'id' => $row['target_id'],
                    'name' => $row['target_name'],
                    'target_on_hold' => (int) ($row['target_on_hold'] ?? 0),
                    'chaining' => [
                        'method' => $row['chaining_method'] ?? null,
                        'total_steps' => isset($row['total_steps']) ? (int) $row['total_steps'] : null,
                        'rule_override' => $row['rule_override'] ? json_decode($row['rule_override'], true) : null,
                    ]
                ];
            }
        }

        // Sort each level by 'name' to ensure ascending order within PHP
        foreach ($clientProgram as &$domain) {
            $domain['goals'] = array_values($domain['goals']);
            foreach ($domain['goals'] as &$goal) {
                $goal['targets'] = array_values($goal['targets']);
            }
        }

        return array_values($clientProgram); // Convert to indexed array for frontend
    }

    public function getClientProgramTree($client_id)
    {
        // Step 1: Fetch Domains
        $builder = $this->db->table('client_program_domains cpd');
        $builder->select('cpd.id as domain_id, cpd.name as domain_name, cpd.domain_code');
        $builder->where('cpd.client_id', $client_id);
        $builder->orderBy('cpd.domain_code', 'ASC');
        $domains = $builder->get()->getResultArray();

        // Step 2: Fetch Goals and link them to their Domains
        $builder = $this->db->table('client_program_goals cpg');
        $builder->select('cpg.id as goal_id, cpg.name as goal_name, cpg.goal_code, cpg.domain_id');
        $builder->where('cpg.client_id', $client_id);
        $builder->orderBy('cpg.goal_code', 'ASC');
        $goals = $builder->get()->getResultArray();

        // Step 3: Fetch Targets and link them to their Goals
        $builder = $this->db->table('client_program_targets cpt');
        $builder->select('cpt.id as target_id, cpt.name as target_name, cpt.goal_id');
        $builder->where('cpt.client_id', $client_id);
        $builder->orderBy('cpt.name', 'ASC');
        $targets = $builder->get()->getResultArray();

        // Step 4: Organize data into a hierarchical structure
        $clientProgram = [];

        foreach ($domains as $domain) {
            $domainId = $domain['domain_id'];
            $clientProgram[$domainId] = $domain;
            $clientProgram[$domainId]['goals'] = [];

            foreach ($goals as $goal) {
                if ($goal['domain_id'] == $domainId) {
                    $goalId = $goal['goal_id'];
                    $clientProgram[$domainId]['goals'][$goalId] = $goal;
                    $clientProgram[$domainId]['goals'][$goalId]['targets'] = [];

                    foreach ($targets as $target) {
                        if ($target['goal_id'] == $goalId) {
                            $clientProgram[$domainId]['goals'][$goalId]['targets'][] = $target;
                        }
                    }
                }
            }
        }


        return $clientProgram;
    }

    public function get_probe_sets($client_id, $goal_id)
    {
        // Fetch probe sets linked to the client and goal
        $probeSets = $this->db->table('client_probe_set cps')
            ->select('cps.id as probe_set_id, tps.name as probe_set_name')
            ->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'left')
            ->where('cps.client_id', $client_id)
            ->where('cps.goal_id', $goal_id)
            ->get()
            ->getResultArray();

        return $probeSets;
    }
    public function get_active_probe_set($client_id, $goal_id)
    {
        // Fetch a single active probe set linked to the client and goal
        $probeSet = $this->db->table('client_probe_set cps')
            ->select('cps.id as probe_set_id, tps.name as probe_set_name,tps.id as master_probe_set_id')
            ->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'left')
            ->where('cps.client_id', $client_id)
            ->where('cps.goal_id', $goal_id)
            ->where('cps.is_active', 1)
            ->limit(1)
            ->get()
            ->getRow(); // Fetches the first row as an object

        // Return null if no active probe set is found
        return $probeSet ?: null;
    }

    public function getClientProgramForLiveSession($client_id)
    {
        $sql = "
            SELECT 
                cpd.id AS domain_id,
                cpd.name AS domain_name,
                cpd.domain_code,
                cpg.id AS goal_id,
                cpg.name AS goal_name,
                cpg.goal_code
            FROM client_program_domains cpd
            JOIN client_program_goals cpg 
                ON cpg.domain_id = cpd.id 
                AND cpg.client_id = ? 
            WHERE cpd.client_id = ?
            ORDER BY cpd.domain_code ASC, cpg.goal_code ASC
        ";

        $query = $this->db->query($sql, [$client_id, $client_id]);
        $results = $query->getResultArray();

        // Organize results into the specific hierarchical structure
        $clientProgram = [];
        foreach ($results as $row) {
            $domainId = $row['domain_id'];
            $goalId = $row['goal_id'];

            // Initialize the domain if not already set
            if (!isset($clientProgram[$domainId])) {
                $clientProgram[$domainId] = [
                    'domain_id' => $domainId,
                    'domain_name' => $row['domain_name'],
                    'domain_code' => $row['domain_code'],
                    'goals' => []
                ];
            }

            // Add goal under the respective domain with goalId as key
            $clientProgram[$domainId]['goals'][$goalId] = [
                'goal_id' => $goalId,
                'goal_name' => $row['goal_name'],
                'goal_code' => $row['goal_code']
            ];
        }

        return $clientProgram;
    }

    public function get_target_list($client_id, $domain_id, $goal_id, $probe_set_id, $session_date)
    {
        // Get today's date for the query
        //$todayDate = currentDate('Y-m-d');

        // Combined query to fetch all required data in one go
        $result = $this->db->table('client_program_targets as cpt')
            ->select('
            cpt.id as target_id,
            cpt.name as target_name,
            cps.id as probe_set_id,
            cps.inputs as probe_set_inputs,
            tps.name as probe_set_name,
            tpc.id as combination_id,
            tpc.name as combination_name,
            dsp.session_date as last_session_date,
            IFNULL(dsp.next_phase_id, tpc.initial_phase_id) as current_phase_id,
            IFNULL(tp.name, ip.name) as current_phase_name,
            cpr.id as rule_id,
            cpr.rules as rule_data,
            tco.consecutive_criteria as override_consecutive_criteria,
            COALESCE(vpcs.program_alert_count, 0)  as program_alert_count,
            vpcs.last_alert_date as last_alert_date,
            COALESCE(vpcs.program_change_count, 0) as program_change_count,
            vpcs.last_change_date as last_change_date
        ')
            // Join with the client_probe_set table to get the probe set details
            ->join('client_probe_set as cps', 'cps.goal_id = cpt.goal_id AND cps.client_id = cpt.client_id AND cps.id = ' . $probe_set_id, 'left')
            // Join with target_probe_sets to get the probe set name
            ->join('target_probe_sets as tps', 'tps.id = cps.probe_set_id', 'left')
            // Join with target_phase_combinations to get the phase combination details
            ->join('target_phase_combinations as tpc', 'tpc.id = cps.combination_id', 'left')
            // Join with target_phases to get the initial phase information
            ->join('target_phases as ip', 'ip.id = tpc.initial_phase_id', 'left')
            // Left join with daily_session_data_collection to exclude targets that have data collected today
            ->join('daily_session_data_collection as dsc', "dsc.target_id = cpt.id AND dsc.client_id = cpt.client_id AND dsc.session_date = '{$session_date}'", 'left')
            ->join(
                'view_client_target_program_change_summary as vpcs',
                'vpcs.client_id = cpt.client_id AND vpcs.target_id = cpt.id AND vpcs.client_probe_set_id = cps.id',
                'left'
            )
            // Subquery to find the latest phase entry in daily_session_data_processed for each target
            ->join('(SELECT target_id, MAX(session_date) as last_date
        FROM daily_session_data_processed
        WHERE session_date <= "' . $session_date . '"
        GROUP BY target_id) as dsp_sub', 'dsp_sub.target_id = cpt.id', 'left')
            // Join back to daily_session_data_processed to get the full record for the latest phase
            ->join('daily_session_data_processed as dsp', 'dsp.target_id = dsp_sub.target_id AND dsp.session_date = dsp_sub.last_date', 'left')


            // Join with target_phases to get the name of the current phase based on dsp.next_phase_id
            ->join('target_phases as tp', 'tp.id = dsp.next_phase_id', 'left')

            // Join with client_probe_rules to retrieve the applicable rules for the current phase
            ->join('client_probe_rules as cpr', 'cpr.client_probe_set_id = cps.id AND cpr.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left')
            // Join with client_program_targets_overrides to check for any consecutive criteria overrides
            ->join('client_program_targets_overrides as tco', 'tco.client_id = cpt.client_id AND tco.target_id = cpt.id AND tco.probe_set_id = cps.id AND tco.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left')
            // Filtering by client ID and goal ID
            ->where('cpt.client_id', $client_id)
            ->where('cpt.goal_id', $goal_id)
            ->where('cpt.on_hold', 0)
            // Exclude targets that already have a data collection entry for today
            ->where('dsc.id IS NULL')
            // Exclude targets where next_phase_id is 4
            ->where('IFNULL(dsp.next_phase_id, tpc.initial_phase_id) !=', 4)
            ->orderBy('cpt.name', 'ASC')
            // Execute the query and fetch the results
            ->get()
            ->getResultArray();

        $targets = [];

        foreach ($result as $row) {
            $target_id = $row['target_id'];

            if (!isset($targets[$target_id])) {
                // Initialize target if not set
                $targets[$target_id] = [
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name'],
                    'last_session_date' => $row['last_session_date'],
                    'program_alert_count'  => (int) $row['program_alert_count'],
                    'last_alert_date'      => $row['last_alert_date'],
                    'program_change_count' => (int) $row['program_change_count'],
                    'last_change_date'     => $row['last_change_date'],
                    'probe_set' => [
                        'probe_set_id' => $row['probe_set_id'], // Ensure this is set
                        'probe_set_name' => $row['probe_set_name'],
                        'inputs' => json_decode($row['probe_set_inputs'], true),
                        'combination' => [
                            'combination_id' => $row['combination_id'],
                            'combination_name' => $row['combination_name'],
                            'current_phase_id' => $row['current_phase_id'],
                            'current_phase_name' => $row['current_phase_name'],
                        ],
                        'rules' => []
                    ]
                ];
            }

            // Append rules to the target
            if ($row['rule_id']) {
                $consecutiveCriteria = $row['override_consecutive_criteria'] ?? json_decode($row['rule_data'], true)['consecutive_criteria'];
                $targets[$target_id]['probe_set']['rules'][] = [
                    'rule_id' => $row['rule_id'],
                    'rule_data' => json_decode($row['rule_data'], true),
                    'phase_name' => $row['current_phase_name'],
                    'consecutive_criteria' => $consecutiveCriteria
                ];
            }
        }

        // Reset the array indexes
        $targets = array_values($targets);

        return $targets;
    }
    public function get_target_list_for_percentage_yes_no_probes($client_id, $domain_id, $goal_id, $probe_set_id, $session_date, $session_id)
    {
        // Get today's date for the query
        //$todayDate = currentDate('Y-m-d');

        // Combined query to fetch all required data in one go
        $result = $this->db->table('client_program_targets as cpt')
            ->select('
            cpt.id as target_id,
            cpt.name as target_name,
            cps.id as probe_set_id,
            cps.inputs as probe_set_inputs,
            tps.name as probe_set_name,
            tpc.id as combination_id,
            tpc.name as combination_name,
            dsp.session_date as last_session_date,
            IFNULL(dsp.next_phase_id, tpc.initial_phase_id) as current_phase_id,
            IFNULL(tp.name, ip.name) as current_phase_name,
            cpr.id as rule_id,
            cpr.rules as rule_data,
            tco.consecutive_criteria as override_consecutive_criteria,
            COALESCE(vpcs.program_alert_count, 0)  as program_alert_count,
            vpcs.last_alert_date as last_alert_date,
            COALESCE(vpcs.program_change_count, 0) as program_change_count,
            vpcs.last_change_date as last_change_date
        ')
            // Join with the client_probe_set table to get the probe set details
            ->join('client_probe_set as cps', 'cps.goal_id = cpt.goal_id AND cps.client_id = cpt.client_id AND cps.id = ' . $probe_set_id, 'left')
            // Join with target_probe_sets to get the probe set name
            ->join('target_probe_sets as tps', 'tps.id = cps.probe_set_id', 'left')
            // Join with target_phase_combinations to get the phase combination details
            ->join('target_phase_combinations as tpc', 'tpc.id = cps.combination_id', 'left')
            // Join with target_phases to get the initial phase information
            ->join('target_phases as ip', 'ip.id = tpc.initial_phase_id', 'left')
            // Left join with daily_session_data_collection to exclude targets that have data collected today
            //->join('daily_session_data_collection as dsc', "dsc.target_id = cpt.id AND dsc.client_id = cpt.client_id AND dsc.session_date = '{$session_date}'", 'left')
            // Join with daily_session_data_collection to exclude targets collected in other sessions today
            ->join('daily_session_data_collection as dsc', "dsc.target_id = cpt.id AND dsc.client_id = cpt.client_id AND dsc.session_date = '{$session_date}' AND dsc.session_id != {$session_id}", 'left')
            // Subquery to find the latest phase entry in daily_session_data_processed for each target
            ->join('(SELECT target_id, MAX(session_date) as last_date
                FROM daily_session_data_processed
                WHERE session_date <= "' . $session_date . '"
                GROUP BY target_id) as dsp_sub', 'dsp_sub.target_id = cpt.id', 'left')
            // Join back to daily_session_data_processed to get the full record for the latest phase
            ->join('daily_session_data_processed as dsp', 'dsp.target_id = dsp_sub.target_id AND dsp.session_date = dsp_sub.last_date', 'left')


            // Join with target_phases to get the name of the current phase based on dsp.next_phase_id
            ->join('target_phases as tp', 'tp.id = dsp.next_phase_id', 'left')

            // Join with client_probe_rules to retrieve the applicable rules for the current phase
            ->join('client_probe_rules as cpr', 'cpr.client_probe_set_id = cps.id AND cpr.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left')
            // Join with client_program_targets_overrides to check for any consecutive criteria overrides
            ->join('client_program_targets_overrides as tco', 'tco.client_id = cpt.client_id AND tco.target_id = cpt.id AND tco.probe_set_id = cps.id AND tco.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left')
            ->join(
                'view_client_target_program_change_summary as vpcs',
                'vpcs.client_id = cpt.client_id AND vpcs.target_id = cpt.id AND vpcs.client_probe_set_id = cps.id',
                'left'
            )
            // Filtering by client ID and goal ID
            ->where('cpt.client_id', $client_id)
            ->where('cpt.goal_id', $goal_id)
            ->where('cpt.on_hold', 0)
            // Exclude targets that already have a data collection entry for today
            //->where('(dsc.id IS NULL and dsc.is_processed = 0)')
            ->where('dsc.id IS NULL')
            ->where("
                NOT EXISTS (
                    SELECT 1 FROM daily_session_data_collection dsc2
                    WHERE dsc2.target_id = cpt.id
                    AND dsc2.client_id = cpt.client_id
                    AND dsc2.session_date = '{$session_date}'
                    AND dsc2.session_id = {$session_id}
                    AND dsc2.is_processed = 1
                )
            ")
            // Exclude targets where next_phase_id is 4
            ->where('IFNULL(dsp.next_phase_id, tpc.initial_phase_id) !=', 4)
            ->orderBy('cpt.name', 'ASC')
            // Execute the query and fetch the results
            ->get()
            ->getResultArray();

        $targets = [];

        foreach ($result as $row) {
            $target_id = $row['target_id'];

            if (!isset($targets[$target_id])) {
                // Initialize target if not set
                $targets[$target_id] = [
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name'],
                    'last_session_date' => $row['last_session_date'],
                    'program_alert_count'  => (int) $row['program_alert_count'],
                    'last_alert_date'      => $row['last_alert_date'],
                    'program_change_count' => (int) $row['program_change_count'],
                    'last_change_date'     => $row['last_change_date'],
                    'probe_set' => [
                        'probe_set_id' => $row['probe_set_id'], // Ensure this is set
                        'probe_set_name' => $row['probe_set_name'],
                        'inputs' => json_decode($row['probe_set_inputs'], true),
                        'combination' => [
                            'combination_id' => $row['combination_id'],
                            'combination_name' => $row['combination_name'],
                            'current_phase_id' => $row['current_phase_id'],
                            'current_phase_name' => $row['current_phase_name'],
                        ],
                        'rules' => []
                    ]
                ];
            }

            // Append rules to the target
            if ($row['rule_id']) {
                $consecutiveCriteria = $row['override_consecutive_criteria'] ?? json_decode($row['rule_data'], true)['consecutive_criteria'];
                $targets[$target_id]['probe_set']['rules'][] = [
                    'rule_id' => $row['rule_id'],
                    'rule_data' => json_decode($row['rule_data'], true),
                    'phase_name' => $row['current_phase_name'],
                    'consecutive_criteria' => $consecutiveCriteria
                ];
            }
        }

        // Reset the array indexes
        $targets = array_values($targets);

        return $targets;
    }

    public function get_target_list_for_stimulus_probes($client_id, $domain_id, $goal_id, $probe_set_id, $session_date, $session_id)
    {
        $result = $this->db->table('client_program_targets as cpt')
            ->select('
            cpt.id as target_id,
            cpt.name as target_name,
            cps.id as probe_set_id,
            cps.inputs as probe_set_inputs,
            tps.name as probe_set_name,
            tpc.id as combination_id,
            tpc.name as combination_name,
            dsp.session_date as last_session_date,
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
            CASE WHEN mastery.id IS NOT NULL THEN 1 ELSE 0 END as is_mastered,
            COALESCE(vpcs.program_alert_count, 0)  as program_alert_count,
            vpcs.last_alert_date as last_alert_date,
            COALESCE(vpcs.program_change_count, 0) as program_change_count,
            vpcs.last_change_date as last_change_date
        ', false)
            ->join('client_probe_set as cps', 'cps.goal_id = cpt.goal_id AND cps.client_id = cpt.client_id AND cps.id = ' . $probe_set_id, 'left')
            ->join('target_probe_sets as tps', 'tps.id = cps.probe_set_id', 'left')
            ->join('target_phase_combinations as tpc', 'tpc.id = cps.combination_id', 'left')
            ->join('target_phases as ip', 'ip.id = tpc.initial_phase_id', 'left')
            ->join('daily_session_data_collection as dsc', "dsc.target_id = cpt.id AND dsc.client_id = cpt.client_id AND dsc.session_date = '{$session_date}' AND dsc.session_id != {$session_id}", 'left')
            ->join(
                'view_client_target_program_change_summary as vpcs',
                'vpcs.client_id = cpt.client_id AND vpcs.target_id = cpt.id AND vpcs.client_probe_set_id = cps.id',
                'left'
            )
            ->join('(SELECT target_id, MAX(session_date) as last_date
            FROM daily_session_data_processed
            WHERE session_date <= "' . $session_date . '"
            GROUP BY target_id) as dsp_sub', 'dsp_sub.target_id = cpt.id', 'left')
            ->join('daily_session_data_processed as dsp', 'dsp.target_id = dsp_sub.target_id AND dsp.session_date = dsp_sub.last_date', 'left')
            ->join('target_phases as tp', 'tp.id = dsp.next_phase_id', 'left')
            ->join('client_probe_rules as cpr', 'cpr.client_probe_set_id = cps.id AND cpr.phase_id = IFNULL(dsp.next_phase_id, tpc.initial_phase_id)', 'left')
            ->join('client_target_stimulus_chains as ctsc', 'ctsc.target_id = cpt.id', 'left')
            ->join('client_target_stimulus_steps as steps', 'steps.target_id = cpt.id', 'left')
            ->join(
                'client_target_stimulus_step_mastery as mastery',
                "mastery.target_id = cpt.id 
     AND mastery.step_id = steps.id 
     AND mastery.session_date <= '{$session_date}'",
                'left'
            )
            ->where('cpt.client_id', $client_id)
            ->where('cpt.goal_id', $goal_id)
            ->where('cpt.on_hold', 0)
            ->where('dsc.id IS NULL')
            ->where("
            NOT EXISTS (
                SELECT 1 FROM daily_session_data_collection dsc2
                WHERE dsc2.target_id = cpt.id
                AND dsc2.client_id = cpt.client_id
                AND dsc2.session_date = '{$session_date}'
                AND dsc2.session_id = {$session_id}
                AND dsc2.is_processed = 1
            )
        ")
            ->where('IFNULL(dsp.next_phase_id, tpc.initial_phase_id) !=', 4)
            ->orderBy('cpt.name', 'ASC')
            ->orderBy('steps.step_number', 'ASC')
            ->get()
            ->getResultArray();

        $targets = [];

        foreach ($result as $row) {
            $target_id = $row['target_id'];

            if (!isset($targets[$target_id])) {
                $targets[$target_id] = [
                    'target_id' => $target_id,
                    'target_name' => $row['target_name'],
                    'last_session_date' => $row['last_session_date'],
                    'current_phase_id' => $row['current_phase_id'],
                    'current_phase_name' => $row['current_phase_name'],
                    'program_alert_count'  => (int) $row['program_alert_count'],
                    'last_alert_date'      => $row['last_alert_date'],
                    'program_change_count' => (int) $row['program_change_count'],
                    'last_change_date'     => $row['last_change_date'],
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
                        'rules' => []
                    ]
                ];
            }

            // Append rules to the target
            if (!empty($row['rule_id'])) {
                $targets[$target_id]['probe_set']['rules'][] = [
                    'rule_id' => $row['rule_id'],
                    'rule_data' => json_decode($row['rule_data'], true),
                    'phase_name' => $row['current_phase_name'],
                ];
            }

            // Append stimulus step if present
            if (!empty($row['step_id'])) {
                $targets[$target_id]['steps'][] = [
                    'step_id' => $row['step_id'],
                    'step_number' => $row['step_number'],
                    'sd_text' => $row['sd_text'],
                    'c_text' => $row['c_text'],
                    'response_text' => $row['response_text'],
                    'is_mastered' => (bool) $row['is_mastered'],
                    'mastered_with_chain' => $row['mastered_with_chain'] ?? null, // ✅ ADD THIS LINE
                ];
            }
        }

        return array_values($targets);
    }


    public function getClientSelectedGoalProbeSet($client_id, $goal_id)
    {
        $builder = $this->db->table('client_probe_set cps');
        $builder->select('
        cps.probe_set_id as id,
        tps.name as name,
         tps.inputs as inputs,
        tpc.name as combination_name
    ');

        // Join target_probe_sets and target_phase_combinations to get names
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'left');
        $builder->join('target_phase_combinations tpc', 'tpc.id = cps.combination_id', 'left');

        // Filter by client_id, goal_id, and is_active = 1
        $builder->where('cps.client_id', $client_id);
        $builder->where('cps.goal_id', $goal_id);
        $builder->where('cps.is_active', 1);

        // Fetch the result
        $result = $builder->get()->getRowArray();

        if (!$result) {
            return [];
        }

        // Decode JSON safely
        $inputs = json_decode($result['inputs'], true);
        $type = $inputs['type'] ?? null;

        return [
            'id' => $result['id'],
            'name' => $result['name'],
            'combination_name' => $result['combination_name'],
            'type' => $type,
        ];
    }

    public function targetHasSessionData(int $targetId): bool
    {

        $builder = $this->db->table('client_target_stimulus_step_sessions_data');
        $builder->where('target_id', $targetId);
        $builder->limit(1);
        return $builder->countAllResults() > 0;
    }

    public function getStimulusChainingDetailsForTarget(int $targetId): ?array
    {
        return $this->db->table('view_target_stimulus_step_summary')
            ->select('chaining_method, step_count, rule_override')
            ->where('target_id', $targetId)
            ->get()
            ->getRowArray();
    }


    /************************************************************************ */
    public function getSelectedClientCurrentProgramSummary($client_id)
    {
        // =========================
        // STEP 1. Query target data
        // =========================
        $sql = "
        WITH
        intro AS (
            SELECT client_id, target_id, MIN(session_date) AS introduced_on
            FROM daily_session_data_collection
            WHERE is_processed = 1
            GROUP BY client_id, target_id
        ),
        last_phase4 AS (
            SELECT client_id, target_id, MAX(session_date) AS session_date
            FROM daily_session_data_processed
            WHERE next_phase_id = 4
            GROUP BY client_id, target_id
        )
        SELECT
            t.client_id,
            d.id            AS domain_id,
            d.name          AS domain_name,
            d.domain_code   AS domain_code,
            g.id            AS goal_id,
            g.name          AS goal_name,
            g.goal_code     AS goal_code,
            t.id            AS target_id,
            t.name          AS target_name,

            i.introduced_on,
            COALESCE(r.session_date, lp.session_date) AS mastered_on,

            CASE
                WHEN r.id IS NOT NULL THEN 'Mastered'
                WHEN lp.session_date IS NOT NULL THEN 'Mastered'
                ELSE 'In Progress'
            END AS status,

            CASE
                WHEN i.introduced_on IS NULL THEN NULL
                WHEN COALESCE(r.session_date, lp.session_date) IS NOT NULL
                     THEN DATEDIFF(COALESCE(r.session_date, lp.session_date), i.introduced_on)
                ELSE DATEDIFF(CURDATE(), i.introduced_on)
            END AS duration_days,

            (
                SELECT COUNT(DISTINCT dc3.session_id)
                FROM daily_session_data_collection dc3
                WHERE dc3.client_id = t.client_id
                  AND dc3.target_id = t.id
            ) AS sessions_count

        FROM client_program_targets t
        JOIN client_program_goals g
          ON g.id = t.goal_id AND g.client_id = t.client_id
        JOIN client_program_domains d
          ON d.id = g.domain_id AND d.client_id = t.client_id
        LEFT JOIN client_program_targets_retained r
          ON r.client_id = t.client_id AND r.target_id = t.id
        LEFT JOIN last_phase4 lp
          ON lp.client_id = t.client_id AND lp.target_id = t.id
        LEFT JOIN intro i
          ON i.client_id = t.client_id AND i.target_id = t.id
        WHERE t.client_id = ?
          AND i.introduced_on IS NOT NULL
        ORDER BY
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(d.domain_code, '[0-9]+'), ''), '0') AS UNSIGNED),
            d.domain_code,
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(g.goal_code,   '[0-9]+'), ''), '0') AS UNSIGNED),
            g.goal_code,
            t.name;
    ";

        $rows = $this->db->query($sql, [$client_id])->getResultArray();

        // Fetch client name early for all cases
        $clientName = $this->db->table('clients')
            ->select("CONCAT(first_name, ' ', COALESCE(last_name,'')) AS full_name")
            ->where('id', $client_id)
            ->get()->getRow('full_name');

        // =========================
        // STEP 2. If no data found — return safe default structure
        // =========================
        if (empty($rows)) {
            return [
                "client_id" => $client_id,
                "client_name" => $clientName ?? "N/A",
                "program_summary" => [
                    "program_start" => null,
                    "program_age" => "N/A",
                    "total_domains" => 0,
                    "total_domains_mastered" => 0,
                    "total_goals" => 0,
                    "total_goals_mastered" => 0,
                    "total_targets" => 0,
                    "total_targets_mastered" => 0,
                    "program_changes_alerts" => 0,
                    "program_changes" => 0,
                    "days" => "N/A"
                ],
                "domains" => []
            ];
        }

        // =========================
        // STEP 3. Build hierarchy
        // =========================
        $domains = [];
        $programStart = null;
        $totalGoals = $totalGoalsMastered = $totalTargets = $totalTargetsMastered = 0;

        foreach ($rows as $r) {
            $programStart = $programStart ? min($programStart, $r['introduced_on']) : $r['introduced_on'];

            // Domain
            $domainId = $r['domain_id'];
            if (!isset($domains[$domainId])) {
                $domains[$domainId] = [
                    "domain_id" => $domainId,
                    "domain_name" => trim("{$r['domain_code']} - {$r['domain_name']}"),
                    "introduced_on" => $r['introduced_on'],
                    "is_mastered" => false,
                    "total_goals" => 0,
                    "mastered_goals" => 0,
                    "total_targets" => 0,
                    "mastered_targets" => 0,
                    "goals" => []
                ];
            }

            if ($domains[$domainId]['introduced_on'] === null || $r['introduced_on'] < $domains[$domainId]['introduced_on']) {
                $domains[$domainId]['introduced_on'] = $r['introduced_on'];
            }

            // Goal
            $goalId = $r['goal_id'];
            if (!isset($domains[$domainId]['goals'][$goalId])) {
                $domains[$domainId]['goals'][$goalId] = [
                    "goal_id" => $goalId,
                    "goal_name" => trim("{$r['goal_code']} - {$r['goal_name']}"),
                    "introduced_on" => $r['introduced_on'],
                    "average_mastery_days" => 0,
                    "is_mastered" => false,
                    "total_targets" => 0,
                    "mastered_targets" => 0,
                    "targets" => []
                ];
            }

            if ($domains[$domainId]['goals'][$goalId]['introduced_on'] === null || $r['introduced_on'] < $domains[$domainId]['goals'][$goalId]['introduced_on']) {
                $domains[$domainId]['goals'][$goalId]['introduced_on'] = $r['introduced_on'];
            }

            // Target
            $domains[$domainId]['goals'][$goalId]['targets'][] = [
                "target_name" => $r['target_name'],
                "status" => $r['status'],
                "introduced_on" => $r['introduced_on'],
                "mastered_on" => $r['mastered_on'],
                "duration_days" => (int) $r['duration_days'],
                "sessions_count" => (int) $r['sessions_count']
            ];

            // Count totals
            $domains[$domainId]['total_targets']++;
            $domains[$domainId]['goals'][$goalId]['total_targets']++;
            $totalTargets++;

            if ($r['status'] === 'Mastered') {
                $domains[$domainId]['mastered_targets']++;
                $domains[$domainId]['goals'][$goalId]['mastered_targets']++;
                $totalTargetsMastered++;
            }
        }

        // =========================
        // STEP 4. Sort targets + compute mastery stats
        // =========================
        foreach ($domains as &$d) {
            foreach ($d['goals'] as &$g) {
                // Sort targets: In Progress first, then Mastered
                usort($g['targets'], function ($a, $b) {
                    if ($a['status'] === $b['status']) {
                        return strcasecmp($a['target_name'], $b['target_name']);
                    }
                    return $a['status'] === 'In Progress' ? -1 : 1;
                });

                $durations = array_column(
                    array_filter($g['targets'], fn($t) => $t['status'] === 'Mastered'),
                    'duration_days'
                );
                $g['average_mastery_days'] = $durations ? round(array_sum($durations) / count($durations)) : 0;
                $g['is_mastered'] = ($g['total_targets'] > 0 && $g['mastered_targets'] == $g['total_targets']);
                $d['total_goals']++;
                if ($g['is_mastered']) $d['mastered_goals']++;
                $totalGoals++;
                if ($g['is_mastered']) $totalGoalsMastered++;
            }
            $d['is_mastered'] = ($d['total_goals'] > 0 && $d['mastered_goals'] == $d['total_goals']);
            $d['goals'] = array_values($d['goals']);
        }
        unset($d, $g);

        // =========================
        // STEP 5. Alerts / Changes
        // =========================
        $alertRow = $this->db->table('client_program_change_alert')
            ->select('COUNT(*) AS program_changes_alerts, SUM(is_change_made) AS program_changes')
            ->where('client_id', $client_id)
            ->get()->getRowArray();

        // =========================
        // STEP 6. Final full output (always complete)
        // =========================
        return [
            "client_id" => $client_id,
            "client_name" => $clientName ?? "N/A",
            "program_summary" => [
                "program_start" => $programStart ?? null,
                "program_age" => $programStart ? (date_diff(date_create($programStart), date_create())->days . " days") : "N/A",
                "total_domains" => count($domains),
                "total_domains_mastered" => count(array_filter($domains, fn($d) => $d['is_mastered'])),
                "total_goals" => $totalGoals,
                "total_goals_mastered" => $totalGoalsMastered,
                "total_targets" => $totalTargets,
                "total_targets_mastered" => $totalTargetsMastered,
                "program_changes_alerts" => (int) ($alertRow['program_changes_alerts'] ?? 0),
                "program_changes" => (int) ($alertRow['program_changes'] ?? 0),
                "days" => $programStart ? (date_diff(date_create($programStart), date_create())->days . " Days") : "N/A"
            ],
            "domains" => array_values($domains)
        ];
    }

    public function getSelectedClientActiveProgram($client_id)
    {
        $programData = $this->getSelectedClientCurrentProgramSummary($client_id);
        $activeDomains = [];
        $activeGoalsCount = 0;
        $activeTargetsCount = 0;

        foreach ($programData['domains'] as $domain) {
            $domainActiveGoals = [];

            foreach ($domain['goals'] as $goal) {
                $activeTargets = array_values(array_filter(
                    $goal['targets'],
                    static fn($target) => $target['status'] !== 'Mastered'
                ));

                // Goal is active only when at least one introduced target is not mastered.
                if (empty($activeTargets)) {
                    continue;
                }

                $goal['targets'] = $activeTargets;
                $goal['active_targets_count'] = count($activeTargets);
                $domainActiveGoals[] = $goal;
                $activeGoalsCount++;
                $activeTargetsCount += count($activeTargets);
            }

            // Domain is active only when it contains at least one active goal.
            if (empty($domainActiveGoals)) {
                continue;
            }

            $domain['goals'] = $domainActiveGoals;
            $domain['active_goals_count'] = count($domainActiveGoals);
            $activeDomains[] = $domain;
        }

        return [
            "client_id" => $programData['client_id'],
            "client_name" => $programData['client_name'],
            "program_summary" => [
                "program_start" => $programData['program_summary']['program_start'],
                "total_domains_active" => count($activeDomains),
                "total_goals_active" => $activeGoalsCount,
                "total_targets_active" => $activeTargetsCount
            ],
            "domains" => $activeDomains
        ];
    }


    /************************************************************************* */
}
