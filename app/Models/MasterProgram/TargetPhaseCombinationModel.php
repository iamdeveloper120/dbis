<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;

class TargetPhaseCombinationModel extends Model
{
    protected $table = 'target_phase_combinations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'initial_phase_id', 'final_phase_id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'];

    public function getCombinationsWithPhaseNames()
    {
        // Join with the target_phases table to get the phase names
        return $this->select('target_phase_combinations.*, ip.name as initial_phase_name, fp.name as final_phase_name')
                    ->join('target_phases as ip', 'ip.id = target_phase_combinations.initial_phase_id', 'left')
                    ->join('target_phases as fp', 'fp.id = target_phase_combinations.final_phase_id', 'left')
                    ->findAll();
    }
}
