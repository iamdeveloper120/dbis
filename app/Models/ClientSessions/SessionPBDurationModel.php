<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\SessionPBDuration;


class SessionPBDurationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_sessions_pb_duration';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = SessionPBDuration::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['session_id', 'session_date', 'client_id', 'start_time', 'end_time'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    public function getPBDuration($session_id)
    {

        $result = $this->db->table('daily_sessions_pb_duration')
            ->select('*')
            ->where('session_id', $session_id)
            ->get()
            ->getResult();


        // Calculate total duration
        $totalDuration = $this->calculateTotalDuration($result);

        return $totalDuration;
    }
    // Helper function to calculate total duration
    private function calculateTotalDuration($teachingDurationRows)
    {
        $totalSeconds = 0;

        foreach ($teachingDurationRows as $row) {
            $startTime = strtotime($row->start_time); // Convert to timestamp for calculations
            $endTime = $row->end_time ? strtotime($row->end_time) : strtotime(currentDate('H:i:s')); // Use current time if end_time is null

            $duration = $endTime - $startTime;
            $totalSeconds += $duration;
        }

        // Format total duration in H:i:s format
        $totalDuration = gmdate('H:i:s', $totalSeconds);

        return $totalDuration;
    }
    public function hasEmptyEndTime($session_id)
    {
        $result = $this->db->table($this->table)
            ->select('id')
            ->where('session_id', $session_id)
            ->where('(end_time IS NULL OR end_time = "")', null, false)  // Check if end_time is null or empty
            ->limit(1)  // We just need to know if at least one exists
            ->get()
            ->getRow();

        return $result !== null;  // Return true if found, false otherwise
    }

    // Method to check if any PB duration entry is missing a corresponding record in daily_sessions_pb_records
    public function hasMissingPbRecords($session_id)
    {
        $builder = $this->db->table($this->table)
            ->select('daily_sessions_pb_duration.id')
            ->join('daily_sessions_pb_records', 'daily_sessions_pb_duration.id = daily_sessions_pb_records.pb_timer_id', 'left')
            ->where('daily_sessions_pb_duration.session_id', $session_id)
            ->where('daily_sessions_pb_records.id IS NULL');  // Find missing records
        
        $result = $builder->get()->getResult();

        return !empty($result);  // Return true if any missing record exists
    }

    /**
     * Check if given start and end time overlaps with existing PB durations
     */
    public function hasOverlap($session_id, $start_time, $end_time, $exclude_id = null)
    {
        $startTime = strtotime($start_time);
        $endTime = strtotime($end_time);

        $query = $this->where('session_id', $session_id);
        if ($exclude_id) {
            $query->where('id !=', $exclude_id);
        }

        $existingDurations = $query->findAll();

        foreach ($existingDurations as $duration) {
            $existingStart = strtotime($duration->start_time);
            $existingEnd = strtotime($duration->end_time);

            if ($startTime < $existingEnd && $endTime > $existingStart) {
                return [
                    'status' => false,
                    'message' => "The entered duration overlaps with an existing PB duration record ({$duration->start_time} - {$duration->end_time})."
                ];
            }
        }

        return ['status' => true, 'message' => ''];
    }
}
