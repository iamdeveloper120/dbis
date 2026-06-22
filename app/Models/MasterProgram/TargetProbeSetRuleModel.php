<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;

class TargetProbeSetRuleModel extends Model
{
    protected $table = 'target_probe_set_rules';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'probe_set_id',
        'combination_id',
        'phase_id',
        'phase_order',
        'rules',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at'
    ];

    public function getRulesWithDetails($probeSetId)
    {
        return $this->select('
                target_probe_set_rules.*,
                target_probe_sets.name as probe_set_name,
                target_phase_combinations.name as combination_name,
                target_phases.name as phase_name,
                initial_phases.name as initial_phase_name,
                final_phases.name as final_phase_name
            ')
            ->join('target_probe_sets', 'target_probe_sets.id = target_probe_set_rules.probe_set_id')
            ->join('target_phase_combinations', 'target_phase_combinations.id = target_probe_set_rules.combination_id')
            ->join('target_phases', 'target_phases.id = target_probe_set_rules.phase_id')
            ->join('target_phases as initial_phases', 'initial_phases.id = target_phase_combinations.initial_phase_id', 'left')
            ->join('target_phases as final_phases', 'final_phases.id = target_phase_combinations.final_phase_id', 'left')
            ->where('target_probe_set_rules.probe_set_id', $probeSetId)
            ->findAll();
    }

    public function getRulesForSelectedProbeSetAndCombination($probeSetId, $combinationId)
    {
        return $this->select('
                target_probe_set_rules.*,
                target_probe_sets.name as probe_set_name,
                target_phase_combinations.name as combination_name,
                target_phases.name as phase_name,
                initial_phases.name as initial_phase_name,
                final_phases.name as final_phase_name
            ')
            ->join('target_probe_sets', 'target_probe_sets.id = target_probe_set_rules.probe_set_id')
            ->join('target_phase_combinations', 'target_phase_combinations.id = target_probe_set_rules.combination_id')
            ->join('target_phases', 'target_phases.id = target_probe_set_rules.phase_id')
            ->join('target_phases as initial_phases', 'initial_phases.id = target_phase_combinations.initial_phase_id', 'left')
            ->join('target_phases as final_phases', 'final_phases.id = target_phase_combinations.final_phase_id', 'left')
            ->where('target_probe_set_rules.probe_set_id', $probeSetId)
            ->where('target_probe_set_rules.combination_id', $combinationId)
            ->findAll();
    }
}
