<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProgramChangeAlertModel extends Model
{
    protected $table = 'client_program_change_alert';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'processed_data_id',
        'collection_id',
        'session_id',
        'session_date',
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'client_probe_set_id',
        'is_alert_handled',
        'is_change_made',
        'comments',
        'created_at',
        'created_by'
    ];

    public function saveChangeAlert($data)
    {
        $this->insert($data);
        return $this->getInsertID();  // Return the ID of the inserted record
    }

    public function getClientProgramAlertCounts(array $clientIds, string $startDate, string $endDate): array
    {
        if (empty($clientIds)) return [];

        return $this->select('client_program_change_alert.client_id, clients.internal_mrn, COUNT(*) as total_alerts')
            ->join('clients', 'clients.id = client_program_change_alert.client_id', 'left')
            ->whereIn('client_program_change_alert.client_id', $clientIds)
            ->where('session_date >=', $startDate)
            ->where('session_date <=', $endDate)
            ->groupBy('client_program_change_alert.client_id')
            ->findAll();
    }
}
