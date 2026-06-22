<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientEffectiveTeachingProcedureModel extends Model
{
    protected $table      = 'client_effective_teaching_procedures';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'client_id',
        'competing_positive_reinforcers',
        'mix_and_vary_tasks',
        'errorless_teaching_procedures',
        'easy_to_hard_percentage',
        'easy_responses_fade_start',
        'schedule_of_reinforcement',
        'general_comment',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Upsert by client_id (1:1 table).
     */
    public function upsertByClientId(int $clientId, array $data): bool
    {
        $existing = $this->where('client_id', $clientId)->first();
        $data['client_id'] = $clientId;

        if ($existing) {
            return (bool) $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }
}
