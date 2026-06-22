<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class CPWizardModel extends Model
{
    protected $DBGroup          = 'default';

    public function getMasterProgramWithClientInfo($client_id)
    {
        /*$builder = $this->db->table('program_master_domains cpd');
        $builder->select('
            cpd.id as id, 
            cpd.name as name, 
            cpd.domain_code, 
            cpg.id as goal_id, 
            cpg.name as goal_name, 
            cpg.goal_code, 
            cpt.id as target_id, 
            cpt.name as target_name,
            IF(cpd_client.id IS NOT NULL, 1, 0) AS is_domain_linked,
            IF(cpg_client.id IS NOT NULL, 1, 0) AS is_goal_linked,
            IF(cpt_client.id IS NOT NULL, 1, 0) AS is_target_linked
        ');

        $builder->join('program_master_goals cpg', 'cpg.domain_id = cpd.id', 'left');
        $builder->join('program_master_targets cpt', 'cpt.goal_id = cpg.id', 'left');

        // Client-specific joins to check linkage
        $builder->join('client_program_domains cpd_client', 'cpd_client.mp_domain_id = cpd.id AND cpd_client.client_id = ' . $client_id, 'left');
        $builder->join('client_program_goals cpg_client', 'cpg_client.mp_goal_id = cpg.id AND cpg_client.client_id = ' . $client_id, 'left');
        $builder->join('client_program_targets cpt_client', 'cpt_client.mp_target_id = cpt.id AND cpt_client.client_id = ' . $client_id, 'left');

        $builder->orderBy('cpd.domain_code', 'ASC');
        $builder->orderBy('cpg.goal_code', 'ASC');
        $builder->orderBy('cpt.name', 'ASC');

        $results = $builder->get()->getResultArray();*/

        $sql = "SELECT 
        cpd.id AS id, 
        cpd.name AS name, 
        cpd.domain_code, 
        cpg.id AS goal_id, 
        cpg.name AS goal_name, 
        cpg.goal_code, 
        cpt.id AS target_id, 
        cpt.name AS target_name,
        IF(cpd_client.id IS NOT NULL, 1, 0) AS is_domain_linked,
        IF(cpg_client.id IS NOT NULL, 1, 0) AS is_goal_linked,
        IF(cpt_client.id IS NOT NULL, 1, 0) AS is_target_linked
    FROM program_master_domains cpd
    LEFT JOIN program_master_goals cpg 
        ON cpg.domain_id = cpd.id
    LEFT JOIN program_master_targets cpt 
        ON cpt.goal_id = cpg.id
    LEFT JOIN client_program_domains cpd_client 
        ON cpd_client.mp_domain_id = cpd.id 
        AND cpd_client.client_id = ?
    LEFT JOIN client_program_goals cpg_client 
        ON cpg_client.mp_goal_id = cpg.id 
        AND cpg_client.client_id = ?
    LEFT JOIN client_program_targets cpt_client 
        ON cpt_client.mp_target_id = cpt.id 
        AND cpt_client.client_id = ?
    ORDER BY 
        CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpd.domain_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
        cpd.domain_code ASC,
        CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpg.goal_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
        cpg.goal_code ASC,
        CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpt.name, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
        cpt.name ASC";

        
        $query = $this->db->query($sql, [$client_id, $client_id, $client_id]); // Use bindings to prevent SQL injection
        $results = $query->getResultArray(); // Convert to array
 

        $masterProgram = [];

        foreach ($results as $row) {
            // Populate domains
            $domainId = $row['id'];
            if (!isset($masterProgram[$domainId])) {
                $masterProgram[$domainId] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'domain_code' => $row['domain_code'],
                    'is_domain_linked' => (bool)$row['is_domain_linked'],
                    'goals' => []
                ];
            }

            // Populate goals within domains
            $goalId = $row['goal_id'];
            if ($goalId && !isset($masterProgram[$domainId]['goals'][$goalId])) {
                $masterProgram[$domainId]['goals'][$goalId] = [
                    'id' => $goalId,
                    'name' => $row['goal_name'],
                    'goal_code' => $row['goal_code'],
                    'is_goal_linked' => (bool)$row['is_goal_linked'],
                    'targets' => []
                ];
            }

            // Populate targets within goals
            if ($row['target_id']) {
                $masterProgram[$domainId]['goals'][$goalId]['targets'][] = [
                    'id' => $row['target_id'],
                    'name' => $row['target_name'],
                    'is_target_linked' => (bool)$row['is_target_linked']
                ];
            }
        }

        // Sort each level by 'name' to ensure ascending order within PHP
        foreach ($masterProgram as &$domain) {
            $domain['goals'] = array_values($domain['goals']);
            foreach ($domain['goals'] as &$goal) {
                $goal['targets'] = array_values($goal['targets']);
            }
        }

        return array_values($masterProgram); // Convert to indexed array for frontend
    }


    /************************************************************************ */
    // Domains
    /************************************************************************ */
    // Method to gives domains that do not exist in client program
    public function get_domains($client_id)
    {
        $builder = $this->db->table('program_master_domains pmd');
        $builder->select('pmd.*');
        $builder->join('client_program_domains cpd', 'pmd.id = cpd.mp_domain_id AND cpd.client_id = ' . $client_id, 'left');
        $builder->where('cpd.mp_domain_id IS NULL');
        $builder->orderBy('pmd.domain_code', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function get_client_domains($client_id)
    {
        $builder = $this->db->table('program_master_domains pmd');
        $builder->select('pmd.*');
        $builder->join('client_program_domains cpd', 'pmd.id = cpd.mp_domain_id AND cpd.client_id = ' . $client_id);
        $builder->orderBy('pmd.domain_code', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    // Method to check if domain name or domain code exists in client domains
    public function check_domain_code_and_name_exist_in_client_domain($client_id, $masterDomain)
    {
        $builder = $this->db->table('client_program_domains');
        $builder->select('1');
        $builder->where('client_id', $client_id);
        $builder->groupStart();
        $builder->where('name', $masterDomain->name);
        $builder->orWhere('domain_code', $masterDomain->domain_code);
        $builder->groupEnd();
        $builder->limit(1);

        $result = $builder->get()->getRow();

        return ($result !== null);
    }
    /************************************************************************ */
    // Method to get client domain_id based on master domain_id
    public function get_client_domain_id($client_id, $master_domain_id)
    {
        $builder = $this->db->table('client_program_domains');
        $builder->select('id');
        $builder->where('client_id', $client_id);
        $builder->where('mp_domain_id', $master_domain_id);
        $result = $builder->get()->getRow();

        return $result ? $result->id : null;
    }

    /************************************************************************ */
    // Goals
    /************************************************************************ */
    // Method gives goals that do not exist in client program
    public function get_goals($client_id, $domain_id = null)
    {
        $builder = $this->db->table('program_master_goals pmg');
        $builder->select('pmg.*, pmd.name as domain_name, pmd.domain_code');
        $builder->join('program_master_domains pmd', 'pmg.domain_id = pmd.id');
        $builder->join('client_program_domains cpd', 'pmd.id = cpd.mp_domain_id AND cpd.client_id = ' . $client_id);
        $builder->join('client_program_goals cpg', 'pmg.id = cpg.mp_goal_id AND cpg.client_id = ' . $client_id, 'left');
        $builder->where('cpg.mp_goal_id IS NULL');
        if ($domain_id != null) {
            $builder->where('pmg.domain_id', $domain_id);
        }
        $builder->orderBy('pmg.goal_code', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function get_client_goals($client_id, $domain_id = null)
    {
        $builder = $this->db->table('program_master_goals pmg');
        $builder->select('pmg.*, pmd.name as domain_name, pmd.domain_code');
        $builder->join('program_master_domains pmd', 'pmg.domain_id = pmd.id');
        $builder->join('client_program_domains cpd', 'pmd.id = cpd.mp_domain_id AND cpd.client_id = ' . $client_id);
        $builder->join('client_program_goals cpg', 'pmg.id = cpg.mp_goal_id AND cpg.client_id = ' . $client_id);

        // No need for a LEFT JOIN or WHERE clause to filter out non-existent goals

        if ($domain_id != null) {
            $builder->where('pmg.domain_id', $domain_id);
        }

        $builder->orderBy('pmg.goal_code', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    // Method to check if domain name or domain code exists in client domains
    public function check_goal_code_and_name_exist_in_client_goal($client_id, $domain_id, $masterGoal)
    {
        $builder = $this->db->table('client_program_goals');
        $builder->select('1');
        $builder->where('client_id', $client_id);
        $builder->where('domain_id', $domain_id);
        $builder->groupStart();
        $builder->where('name', $masterGoal->name);
        $builder->orWhere('goal_code', $masterGoal->goal_code);
        $builder->groupEnd();
        $builder->limit(1);

        $result = $builder->get()->getRow();

        return ($result !== null);
    }
    /************************************************************************ */
    public function get_client_goal_id($client_id, $master_goal_id)
    {
        $builder = $this->db->table('client_program_goals');
        $builder->select('id');
        $builder->where('client_id', $client_id);
        $builder->where('mp_goal_id', $master_goal_id);
        $result = $builder->get()->getRow();

        return $result ? $result->id : null;
    }

    /************************************************************************ */
    // Targets
    /************************************************************************ */
    // Method gives goals that do not exist in client program
    public function get_targets($client_id, $domain_id = null, $goal_id = null)
    {
        $builder = $this->db->table('program_master_targets pmt');
        $builder->select('pmt.*, pmg.name as goal_name, pmg.goal_code, pmd.name as domain_name, pmd.domain_code');
        $builder->join('program_master_goals pmg', 'pmt.goal_id = pmg.id');
        $builder->join('program_master_domains pmd', 'pmg.domain_id = pmd.id');
        $builder->join('client_program_goals cpg', 'pmg.id = cpg.mp_goal_id AND cpg.client_id = ' . $client_id);
        $builder->join('client_program_targets cpt', 'pmt.id = cpt.mp_target_id AND cpt.client_id = ' . $client_id, 'left');
        $builder->where('cpt.mp_target_id IS NULL');
        if ($domain_id != null) {
            $builder->where('pmd.id', $domain_id);
        }
        if ($goal_id != null) {
            $builder->where('pmt.goal_id', $goal_id);
        }
        $builder->orderBy('pmd.domain_code', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function check_target_exist_in_client_targets($client_id, $goal_id, $masterTarget)
    {
        $builder = $this->db->table('client_program_targets');
        $builder->select('1');
        $builder->where('client_id', $client_id);
        $builder->where('goal_id', $goal_id);
        $builder->where('name', $masterTarget->name);
        $builder->limit(1);

        $result = $builder->get()->getRow();

        return ($result !== null);
    }
    /************************************************************************ */
}
