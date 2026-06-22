<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DailySession;


class DailySessionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = DailySession::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['session_date', 'client_id', 'instructor_id', 'supervisor_id', 'start_time', 'end_time', 'manual_duration', 'session_rating', 'instructor_comments', 'supervisor_comments', 'comments', 'note', 'status', 'flag', 'created_by', 'created_at', 'updated_by', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
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



    public function get_client_executed_sessions($client_id = NULL, $start_date = NULL, $end_date = NULL, $supervisor_id = NULL, $instructor_id = NULL, $status = NULL, $clients_to_user = NULL)
    {
        // Need check which user is accessing this. for super admin and manamement. need to show all session for selected client. for supervisor and tutor need to show only they belong

        $builder = $this->db->table('daily_sessions');
        $builder->select('
            daily_sessions.*,
            u1.first_name as supervisor_first_name,
            u1.last_name as supervisor_last_name,
            u2.first_name as instructor_first_name,
            u2.last_name as instructor_last_name,
            clients.mrn,
            clients.internal_mrn,
            clients.first_name as client_first_name,
            clients.last_name as client_last_name,
            sh.hours as teaching_duration,
            pb.total_duration_of_problem_behavior,
            pb.frequency_of_problem_behavior,
            md.total_mands,
            md.variety_of_mands,
            md.total_duration_formatted as total_duration_of_mands,
            md.frequency_of_mands_per_minute
        ');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        // Left joins with session duration views
        $builder->join('view_live_data_session_hours_by_session sh', 'sh.session_id = daily_sessions.id', 'left');
        $builder->join('view_live_data_pb_duration_by_session pb', 'pb.session_id = daily_sessions.id', 'left');
        $builder->join('view_mands_totals_and_variety_by_session md', 'md.session_id = daily_sessions.id', 'left');


        if ($client_id !== NULL) {
            $builder->where('daily_sessions.client_id', $client_id);
        } else {
            if (!auth()->user()->inGroup('management', 'superadmin')) {
                $builder->whereIn('daily_sessions.client_id', $clients_to_user);
            }
        }
        if ($supervisor_id !== NULL) {
            $builder->where('daily_sessions.supervisor_id', $supervisor_id);
        }
        if ($instructor_id !== NULL) {
            $builder->where('daily_sessions.instructor_id', $instructor_id);
        }
        if ($status !== NULL) {
            $builder->where('daily_sessions.status', $status);
        }
        if ($start_date !== NULL && $end_date !== NULL) {
            $builder->where('daily_sessions.session_date >= ', $start_date);
            $builder->where('daily_sessions.session_date <=', $end_date);
        }
        $builder->orderBy('daily_sessions.session_date', 'DESC');
        $query = $builder->get();
        $result = $query->getResultArray(DailySession::class);

        return $result;
    }


    public function get_client_executed_session($id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('
            daily_sessions.*,
            u1.first_name as supervisor_first_name,
            u1.last_name as supervisor_last_name,
            u2.first_name as instructor_first_name,
            u2.last_name as instructor_last_name,
            clients.mrn,
            clients.internal_mrn,
            clients.first_name as client_first_name,
            clients.last_name as client_last_name,
            sh.hours as teaching_duration,
            pb.total_duration_of_problem_behavior,
            pb.frequency_of_problem_behavior,
            md.total_mands,
            md.variety_of_mands,
            md.total_duration_formatted as total_duration_of_mands,
            md.frequency_of_mands_per_minute
        ');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');


        // Left joins with session duration views
        $builder->join('view_live_data_session_hours_by_session sh', 'sh.session_id = daily_sessions.id', 'left');
        $builder->join('view_live_data_pb_duration_by_session pb', 'pb.session_id = daily_sessions.id', 'left');
        $builder->join('view_mands_totals_and_variety_by_session md', 'md.session_id = daily_sessions.id', 'left');

        $builder->where('daily_sessions.id', $id); // Changed to check against session_id

        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class); // Changed to getRow

        return $result;
    }

    public function get_recent_instructor_comments($client_id, $limit = 5)
    {
        return $this->db->table('daily_sessions')
            ->select('id, session_date, instructor_comments')
            ->where('client_id', $client_id)
            ->where('session_date IS NOT NULL', null, false)
            ->where('instructor_comments IS NOT NULL', null, false)
            ->where("TRIM(instructor_comments) <> ''", null, false)
            ->where("TRIM(instructor_comments) <> 'NA'", null, false)
            ->orderBy('session_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function get_recent_wow_comments($client_id, $limit = 10)
    {
        return $this->db->table('daily_sessions')
            ->select('id, session_date, comments')
            ->where('client_id', $client_id)
            ->where('session_date IS NOT NULL', null, false)
            ->where('comments IS NOT NULL', null, false)
            ->where("TRIM(comments) <> ''", null, false)
            ->where("TRIM(comments) <> 'NA'", null, false)
            ->orderBy('session_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    // Below all method need to update or remove. 


    public function addOrUpdateManuallySessionValidation($data)
    {
        // Extract data for easy use
        $session_id = isset($data['id']) ? $data['id'] : null;
        $client_id = $data['client_id'];
        $instructor_id = $data['instructor_id'];
        $session_date = $data['session_date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];

        // Validate time format
        if (!$this->isValidTime($start_time) || !$this->isValidTime($end_time)) {
            return ['success' => false, 'message' => 'Invalid time format.'];
        }

        // Validate start time is less than end time
        if ($start_time >= $end_time) {
            return ['success' => false, 'message' => 'Start time must be less than end time.'];
        }

        // Check for client scheduling conflicts, excluding the current session if updating
        if ($this->hasClientConflict($session_id, $client_id, $session_date, $start_time, $end_time)) {
            return ['success' => false, 'message' => 'Client session conflict detected.'];
        }

        // Check for tutor scheduling conflicts, excluding the current session if updating
        if ($this->hasInstructorConflict($session_id, $instructor_id, $session_date, $start_time, $end_time)) {
            return ['success' => false, 'message' => 'Instructor session conflict detected.'];
        }

        // Return success message or any other information
        return ['success' => true, 'message' => 'No Conflict'];
    }
    // Helper method to check for client scheduling conflicts, excluding the current session if updating
    public function hasClientConflict($current_session_id, $client_id, $session_date, $start_time, $end_time)
    {

        $query = $this->db->table('daily_sessions')
            ->where('client_id', $client_id)
            ->where('session_date', $session_date);

        if ($current_session_id != null) {
            $query->where('id !=', $current_session_id); // Exclude the current session if updating
        }

        // Check for conflicts with sessions where `end_time` is NULL
        $query->groupStart()
            ->groupStart()
            ->where('end_time IS NULL')
            ->where('start_time >=', $start_time)
            ->where('start_time <', $end_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('end_time IS NOT NULL')
            ->groupStart()
            ->where('start_time <=', $start_time)
            ->where('end_time >', $start_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('start_time <', $end_time)
            ->where('end_time >=', $end_time)
            ->groupEnd()
            ->groupEnd()
            ->groupEnd();


        $sql = $query->getCompiledSelect();

        $query = $this->db->query($sql);
        $conflict = $query->getResult();

        return !empty($conflict); // Return true if a conflict exists, false otherwise
    }

    // Helper method to check for tutor scheduling conflicts, excluding the current session if updating
    public function hasInstructorConflict($current_session_id, $instructor_id, $session_date, $start_time, $end_time)
    {
        $query = $this->db->table('daily_sessions')
            ->where('instructor_id', $instructor_id)
            ->where('session_date', $session_date);

        if ($current_session_id !== null) {
            $query->where('id !=', $current_session_id); // Exclude the current session if updating
        }

        $query->groupStart()
            ->groupStart()
            ->where('end_time IS NULL')
            ->where('start_time >=', $start_time)
            ->where('start_time <', $end_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('end_time IS NOT NULL')
            ->groupStart()
            ->where('start_time <=', $start_time)
            ->where('end_time >', $start_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('start_time <', $end_time)
            ->where('end_time >=', $end_time)
            ->groupEnd()
            ->groupEnd()
            ->groupEnd();

        $sql = $query->getCompiledSelect();

        $query = $this->db->query($sql);
        $conflict = $query->getResult();

        return !empty($conflict); // Return true if a conflict exists, false otherwise
    }

    public function hasClientStartTimeConflict($client_id, $session_date, $start_time)
    {
        $query = $this->db->table('daily_sessions')
            ->where('client_id', $client_id)
            ->where('session_date', $session_date)
            ->groupStart()
            ->where('start_time', $start_time) // Exact start time match
            ->orGroupStart()
            ->where('end_time IS NULL')
            ->where('start_time <=', $start_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('end_time IS NOT NULL')
            ->where('start_time <=', $start_time)
            ->where('end_time >', $start_time)
            ->groupEnd()
            ->groupEnd();

        $sql = $query->getCompiledSelect();

        $query = $this->db->query($sql);
        $conflict = $query->getResult();

        return !empty($conflict); // Return true if a conflict exists, false otherwise
    }

    public function hasInstructorStartTimeConflict($instructor_id, $session_date, $start_time)
    {
        $query = $this->db->table('daily_sessions')
            ->where('instructor_id', $instructor_id)
            ->where('session_date', $session_date)
            ->groupStart()
            ->where('start_time', $start_time) // Exact start time match
            ->orGroupStart()
            ->where('end_time IS NULL')
            ->where('start_time <=', $start_time)
            ->groupEnd()
            ->orGroupStart()
            ->where('end_time IS NOT NULL')
            ->where('start_time <=', $start_time)
            ->where('end_time >', $start_time)
            ->groupEnd()
            ->groupEnd();

        $sql = $query->getCompiledSelect();

        $query = $this->db->query($sql);
        $conflict = $query->getResult();

        return !empty($conflict); // Return true if a conflict exists, false otherwise
    }


    protected function isValidTime($time): bool
    {
        // Regular expression to match the time format (HH:MM:SS)
        $pattern = '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/';
        // Check if the input matches the time format
        return (bool) preg_match($pattern, $time);
    }

    /*public function isCancelled($id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('*');
        $builder->where('id', $id);
        $builder->where('status', 2);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }
        return false;
    }*/

    public function getFutureSessions($currentDate, $endTime, $clientId)
    {
        return $this->db->table('daily_sessions')
            ->where('session_date >', $currentDate)
            ->where('end_time >', $endTime)
            ->where('client_id', $clientId)
            ->get()
            ->getResult();
    }


    public function getActiveSession($session_date, $client_id, $instructor_id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('daily_sessions.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        $builder->where('daily_sessions.session_date', $session_date);
        $builder->where('daily_sessions.client_id', $client_id);
        $builder->where('daily_sessions.instructor_id', $instructor_id);
        $builder->where('daily_sessions.status', 1);
        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class);

        return $result;
    }

    public function getSessionByID($session_id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('daily_sessions.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        $builder->where('daily_sessions.id', $session_id);
        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class);

        return $result;
    }
    public function getClientActiveSessionForGivenDate($session_date, $client_id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('daily_sessions.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        $builder->where('daily_sessions.session_date', $session_date);
        $builder->where('daily_sessions.client_id', $client_id);
        $builder->where('daily_sessions.status', 1);
        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class);

        return $result;
    }
    public function getInstructorActiveSessionForGivenDate($session_date, $instructor_id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('daily_sessions.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        $builder->where('daily_sessions.session_date', $session_date);
        $builder->where('daily_sessions.instructor_id', $instructor_id);
        $builder->where('daily_sessions.status', 1);
        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class);

        return $result;
    }

    public function isSessionActiveForAnotherTherapist($session_date, $client_id, $instructor_id)
    {
        $builder = $this->db->table('daily_sessions');
        $builder->select('daily_sessions.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_sessions.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = daily_sessions.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = daily_sessions.client_id ', 'left');

        $builder->where('daily_sessions.session_date', $session_date);
        $builder->where('daily_sessions.start_time >=', currentDate('H:i:s'));
        $builder->where('daily_sessions.client_id', $client_id);
        $builder->where('daily_sessions.instructor_id !=', $instructor_id);
        $builder->where('daily_sessions.status', 1);
        $query = $builder->get();
        $result = $query->getRow(0, DailySession::class);
        if (isset($result)) {
            return true;
        }
        return false;
    }

    public function isAnySessionNotProcessedInPast($date, $client_id)
    {
        $builder = $this->db->table('daily_sessions');

        // Only select the necessary fields for this check
        $builder->select('id');

        // Apply conditions to check for sessions before the given date
        $builder->where('daily_sessions.session_date <', $date);

        // Apply condition for the client_id
        $builder->where('daily_sessions.client_id', $client_id);

        // Check for session statuses 1, 2, or 4
        $builder->whereIn('daily_sessions.status', [1, 2, 4]);

        // Fetch the first result to check if a session exists
        $query = $builder->get();
        $result = $query->getRow();

        // Return true if any session is found, false otherwise
        return isset($result);
    }

    /**
     * Check if given start and end time are within session time
     */
    public function isWithinSessionTime($session_id, $start_time, $end_time)
    {
        $session = $this->where('id', $session_id)->first();
        if (!$session) {
            return ['status' => false, 'message' => 'Session not found.'];
        }

        $sessionStart = strtotime($session->start_time);
        $sessionEnd = strtotime($session->end_time);
        $startTime = strtotime($start_time);
        $endTime = strtotime($end_time);

        if ($startTime < $sessionStart || $endTime > $sessionEnd) {

            return ['status' => false, 'message' => "The duration must be within the session time range ({$session->start_time} - {$session->end_time})."];
        }

        return ['status' => true, 'message' => ''];
    }

    /**
     * This method should return true if at least one record exists in any of the tables, otherwise false.
     */

    public function isSessionDataExistsInOtherTables($sessionId)
    {
        $tables = [
            'daily_sessions_mands_duration',
            'daily_sessions_pb_duration',
            'daily_sessions_pb_records',
            'daily_sessions_teaching_duration',
            'daily_session_data_collection',
            'mands_session_data'
        ];

        foreach ($tables as $table) {
            $exists = $this->db->table($table)
                ->where('session_id', $sessionId)
                ->countAllResults();

            if ($exists > 0) {
                return true; // If a record is found, return true immediately
            }
        }

        return false; // No records found in any table
    }

    public function isSessionDateSame($sessionId, $date)
    {
        $session = $this->where('id', $sessionId)->first();

        if (!$session) {
            return false; // Session not found
        }

        return $session->session_date === $date;
    }
}
