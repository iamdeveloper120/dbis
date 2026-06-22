<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;

class TargetProbeSetModel extends Model
{
    protected $table = 'target_probe_sets';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'inputs', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'];

    public function getAllProbeSets()
    {
        return $this->findAll();
    }

    public function getProbeSetById($probeSetId)
    {
        return $this->find($probeSetId);
    }

    // New method to get combinations for a given probe set
    public function getCombinationsForProbeSet($probeSetId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('target_probe_set_rules');
        $builder->select('target_phase_combinations.*');
        $builder->join('target_phase_combinations', 'target_phase_combinations.id = target_probe_set_rules.combination_id');
        $builder->where('target_probe_set_rules.probe_set_id', $probeSetId);
        $builder->groupBy('target_phase_combinations.id'); // Group by combination id to avoid duplicates
        $query = $builder->get();
        
        return $query->getResult();
    }
}
