<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProbeSetModel extends Model
{
    protected $DBGroup  = 'default';
    protected $table = 'client_probe_set';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'goal_id',
        'probe_set_id',
        'combination_id',
        'inputs',
        'is_active',
        'start_date',
        'end_date',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    public function getProbeSetForGoal($goalId, $clientId)
    {
        return $this->where('goal_id', $goalId)
            ->where('client_id', $clientId)
            ->orderBy('start_date', 'desc')
            ->first();
    }

    public function saveProbeSet($data)
    {
        if (isset($data['id'])) {
            $this->update($data['id'], $data);
            return $data['id'];
        } else {
            $this->insert($data);
            return $this->getInsertID();
        }
    }

    /**
     * Get probe sets with related details such as probe set name, combination name, domain name, goal name, and client name.
     */
    public function getProbeSetsWithDetails($clientId, $goalId)
    {
        return $this->select('client_probe_set.*, 
                              clients.first_name as client_name, 
                              client_program_goals.name as goal_name, 
                              client_program_domains.name as domain_name, 
                              target_probe_sets.name as probe_set_name, 
                              target_phase_combinations.name as combination_name')
            ->join('clients', 'clients.id = client_probe_set.client_id')
            ->join('client_program_goals', 'client_program_goals.id = client_probe_set.goal_id')
            ->join('client_program_domains', 'client_program_domains.id = client_program_goals.domain_id')
            ->join('target_probe_sets', 'target_probe_sets.id = client_probe_set.probe_set_id')
            ->join('target_phase_combinations', 'target_phase_combinations.id = client_probe_set.combination_id')
            ->where('client_probe_set.client_id', $clientId)
            ->where('client_probe_set.goal_id', $goalId)
            ->orderBy('client_probe_set.is_active', 'DESC')
            ->findAll();
    }

    public function getProbeSetDetails($probeSetId, $currentPhaseId)
    {
        // Query to fetch probe set details including inputs, combination, phase, and rules
        return $this->db->table('client_probe_set cps')
            ->select('
                cps.inputs,
                tpc.id as combination_id,
                tpc.name as combination_name,
                tp.name as phase_name,
                cpr.rules as rule_data
            ')
            ->join('target_phase_combinations tpc', 'tpc.id = cps.combination_id', 'left')
            ->join('target_phases tp', 'tp.id = ' . $currentPhaseId, 'left')
            ->join('client_probe_rules cpr', 'cpr.client_probe_set_id = cps.id AND cpr.phase_id = ' . $currentPhaseId, 'left')
            ->where('cps.id', $probeSetId)
            ->get()
            ->getRowArray();
    }

    public function isUsed($clientProbeSetId, $goalId, $clientId)
    {
        $builder = $this->db->table('daily_session_data_collection');
        $builder->select('*');
        $builder->where('client_id', $clientId);
        $builder->where('client_probe_set_id', $clientProbeSetId);
        $builder->where('goal_id', $goalId);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }
    }
}
