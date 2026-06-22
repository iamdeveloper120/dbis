<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProgramTargetOverridesModel extends Model
{
    protected $table = 'client_program_targets_overrides';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'probe_set_id',
        'phase_id',
        'consecutive_criteria',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    /**
     * Get the override value for consecutive_criteria if it exists.
     * 
     * @param int $clientId
     * @param int $domainId
     * @param int $goalId
     * @param int $targetId
     * @param int $probeSetId
     * @param int $phaseId
     * @return int|null
     */
    public function getConsecutiveCriteriaOverride(int $clientId, int $domainId, int $goalId, int $targetId, int $probeSetId, int $phaseId): ?int
    {
        $override = $this->where('client_id', $clientId)
            ->where('domain_id', $domainId)
            ->where('goal_id', $goalId)
            ->where('target_id', $targetId)
            ->where('probe_set_id', $probeSetId)
            ->where('phase_id', $phaseId)
            ->first();

        return $override['consecutive_criteria'] ?? null;
    }
}
