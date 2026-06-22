<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;

class MasterProgramViewModel  extends Model
{
    protected $DBGroup          = 'default';

    public function getMasterProgramTree()
    {
        $builder = $this->db->table('program_master_domains cpd');
        $builder->select('cpd.id as domain_id, cpd.name as domain_name, cpd.domain_code, 
                      cpg.id as goal_id, cpg.name as goal_name, cpg.goal_code, 
                      cpt.id as target_id, cpt.name as target_name');
        $builder->join('program_master_goals cpg', 'cpg.domain_id = cpd.id', 'left');
        $builder->join('program_master_targets cpt', 'cpt.goal_id = cpg.id', 'left');
        $builder->orderBy('cpd.domain_code', 'ASC');
        $builder->orderBy('cpg.goal_code', 'ASC');
        $builder->orderBy('cpt.name', 'ASC');

        $results = $builder->get()->getResultArray();
        $masterProgram = [];

        foreach ($results as $row) {
            // Populate domains
            $domainId = $row['domain_id'];
            if (!isset($masterProgram[$domainId])) {
                $masterProgram[$domainId] = [
                    'domain_id' => $row['domain_id'],
                    'domain_name' => $row['domain_name'],
                    'domain_code' => $row['domain_code'],
                    'goals' => []
                ];
            }

            // Populate goals within domains
            $goalId = $row['goal_id'];
            if ($goalId && !isset($masterProgram[$domainId]['goals'][$goalId])) {
                $masterProgram[$domainId]['goals'][$goalId] = [
                    'goal_id' => $goalId,
                    'goal_name' => $row['goal_name'],
                    'goal_code' => $row['goal_code'],
                    'targets' => []
                ];
            }

            // Populate targets within goals
            if ($row['target_id']) {
                $masterProgram[$domainId]['goals'][$goalId]['targets'][] = [
                    'target_id' => $row['target_id'],
                    'target_name' => $row['target_name']
                ];
            }
        }

        return $masterProgram;
    }

    /************************************************************************ */
    public function getMasterProgramTreeForReact()
    {
        /* $builder = $this->db->table('program_master_domains cpd');
        $builder->select('cpd.id as id, cpd.name as name, cpd.domain_code, 
                      cpg.id as goal_id, cpg.name as goal_name, cpg.goal_code, 
                      cpt.id as target_id, cpt.name as target_name');
        $builder->join('program_master_goals cpg', 'cpg.domain_id = cpd.id', 'left');
        $builder->join('program_master_targets cpt', 'cpt.goal_id = cpg.id', 'left');
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
            cpt.name AS target_name 
        FROM program_master_domains cpd
        LEFT JOIN program_master_goals cpg ON cpg.domain_id = cpd.id
        LEFT JOIN program_master_targets cpt ON cpt.goal_id = cpg.id
        ORDER BY 
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpd.domain_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpd.domain_code ASC,
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpg.goal_code, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpg.goal_code ASC,
            CAST(COALESCE(NULLIF(REGEXP_SUBSTR(cpt.name, '[0-9]+'), ''), '0') AS UNSIGNED) ASC,
            cpt.name ASC";

        $query = $this->db->query($sql);
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
                    'targets' => []
                ];
            }

            // Populate targets within goals
            if ($row['target_id']) {
                $masterProgram[$domainId]['goals'][$goalId]['targets'][] = [
                    'id' => $row['target_id'],
                    'name' => $row['target_name']
                ];
            }
        }


        foreach ($masterProgram as &$domain) {
            $domain['goals'] = array_values($domain['goals']);
            foreach ($domain['goals'] as &$goal) {
                $goal['targets'] = array_values($goal['targets']);
            }
        }

        return array_values($masterProgram); // Convert to indexed array for frontend
    }
}
