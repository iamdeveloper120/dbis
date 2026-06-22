<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;

class DailySessionDataProcessedModel extends Model
{
    protected $table = 'daily_session_data_processed';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'collection_id',
        'session_id',
        'session_date',
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'client_probe_set_id',
        'next_phase_id',
        'is_program_changed',
        'processed_detail',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function saveProcessedData($originalData, $processedData, $next_phase_id, $is_program_changed)
    {
        // Step 1: Set all previous records for this target as inactive
        $this->where('client_id', $originalData['client_id'])
            ->where('target_id', $originalData['target_id'])
            ->where('client_probe_set_id', $originalData['client_probe_set_id'])
            ->where('is_active', 1)
            ->set(['is_active' => 0])
            ->update();

        // Step 2: Insert the new record as active
        $this->insert([
            'collection_id'   => $originalData['id'],
            'session_id'      => $originalData['session_id'],
            'session_date'    => $originalData['session_date'],
            'client_id'       => $originalData['client_id'],
            'domain_id'       => $originalData['domain_id'],
            'goal_id'         => $originalData['goal_id'],
            'target_id'       => $originalData['target_id'],
            'client_probe_set_id' => $originalData['client_probe_set_id'],
            'next_phase_id'   => $next_phase_id,
            'is_program_changed' => $is_program_changed,
            'processed_detail' => json_encode($processedData),
            'created_by'      => auth()->user()->id,
        ]);

        return $this->getInsertID();  // Return the ID of the inserted processed data

    }

    public function getTargetLastProcessedData($client_id, $target_id, $client_probe_set_id)
    {
        // Query to fetch last processed data for the given probe set and target
        return $this->db->table('daily_session_data_processed')
            ->select('*')
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    public function getTargetLastProcessedDataByDate($client_id, $target_id, $client_probe_set_id, $session_date)
    {
        
        // Query to fetch the last processed data up to the given session date
        return $this->db->table('daily_session_data_processed')
            ->select('*')
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('session_date <=', $session_date) // Ensure session_date is on or before the given date
            ->orderBy('session_date', 'DESC')        // Get the most recent session date first
            ->orderBy('id', 'DESC')                 // In case of multiple entries on the same date, use the latest ID
            ->limit(1)                              // Fetch only one record
            ->get()
            ->getRowArray();
    }
}
