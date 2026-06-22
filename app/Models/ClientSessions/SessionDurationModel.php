<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\SessionDuration;


class SessionDurationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_sessions_teaching_duration';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = SessionDuration::class;
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


    public function getTeachingDuration($session_id)
    {

        $result = $this->db->table('daily_sessions_teaching_duration')
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
    public function hasAtLeastOneRecord($session_id)
    {
        $result = $this->db->table($this->table)
            ->select('id')
            ->where('session_id', $session_id)
            ->limit(1)
            ->get()
            ->getRow();

        return $result !== null; // Returns TRUE if at least one record exists
    }

    /**
     * Check if given start and end time overlaps with existing teaching durations
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
                    'message' => "The entered duration overlaps with an existing teaching record ({$duration->start_time} - {$duration->end_time})."
                ];
            }
        }

        return ['status' => true, 'message' => ''];
    }
}
