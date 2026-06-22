<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProgramChangeModel  extends Model
{
    protected $table = 'client_program_change';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'alert_id',
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'collection_id',
        'processed_data_id',
        'session_id',
        'session_date',
        'client_probe_set_id',
        'consecutive_criteria',
        'other_ant',
        'other_con',
        'incorrect_response',
        'behavioral_variables',
        'description',
        'created_at',
        'created_by'
    ];

    public function getProgramChangeWithUser($pg_change_id)
    {
        return $this->select('client_program_change.*, users.first_name, users.last_name')
            ->join('users', 'users.id = client_program_change.created_by', 'left')
            ->where('client_program_change.id', $pg_change_id)
            ->asObject()
            ->first();
    }
    public function getProgramChangeWithUserByAlert($pg_alert_id)
    {
        return $this->select('client_program_change.*, users.first_name, users.last_name')
            ->join('users', 'users.id = client_program_change.created_by', 'left')
            ->where('client_program_change.alert_id', $pg_alert_id)
            ->asObject()
            ->first();
    }

    public function saveChanges($data)
    {
        $this->insert($data);
        return $this->getInsertID();  // Return the ID of the inserted record
    }

    public function getClientProgramChangeCounts(array $clientIds, string $startDate, string $endDate): array
    {
        if (empty($clientIds)) return [];

        return $this->select('client_program_change.client_id, clients.internal_mrn, COUNT(*) as total_changes')
            ->join('clients', 'clients.id = client_program_change.client_id', 'left')
            ->whereIn('client_program_change.client_id', $clientIds)
            ->where('session_date >=', $startDate)
            ->where('session_date <=', $endDate)
            ->groupBy('client_program_change.client_id')
            ->findAll();
    }
}
