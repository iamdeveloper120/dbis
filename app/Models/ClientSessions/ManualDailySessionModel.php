<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DailySession;


class ManualDailySessionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_session_manual';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = DailySession::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['week_date', 'client_id', 'instructor_id', 'supervisor_id', 'hours', 'skills_retained', 'doi', 'total_mands', 'variety_of_mands', 'frequency_of_problem_behavior', 'total_duration_of_problem_behavior', 'session_quality_rating', 'program_change_made', 'comments', 'status', 'extra_1', 'extra_2', 'extra_3', 'created_by', 'created_at', 'updated_by', 'updated_at'];

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

    public function getSelectedRow($id)
    {
        // Query to fetch data from the view with joins
        $builder = $this->db->table('view_daily_data_combined vc');
        $builder->select(' 
            vc.id,     
            clients.mrn,
            clients.internal_mrn,
            vc.week_date AS date, 
            vc.hours,
            vc.skills_retained,
            vc.doi,
            vc.total_mands,
            vc.variety_of_mands,
            vc.frequency_of_problem_behavior,
            vc.total_duration_of_problem_behavior,
            vc.session_quality_rating,
            vc.program_change_made,
            users_instructor.first_name AS instructor_first_name,
            users_instructor.last_name AS instructor_last_name,
            users_supervisor.first_name AS supervisor_first_name,
            users_supervisor.last_name AS supervisor_last_name,
            vc.comments,
            vc.status, 
            vc.data_source
        ');

        // Joining with Clients and Users tables
        $builder->join('clients', 'clients.id = vc.client_id', 'left');
        $builder->join('users as users_instructor', 'users_instructor.id = vc.instructor_id', 'left');
        $builder->join('users as users_supervisor', 'users_supervisor.id = vc.supervisor_id', 'left');

        $builder->where('vc.id', $id);

        // Execute the Query and fetch the result as an object
        $row = $builder->get()->getRow();
        if ($row) {
            // Manually cast the result into the DailySession entity
            $dailySession = new DailySession();

            // Map the result to the DailySession entity fields
            $dailySession->fill((array)$row);

            // Now you can use the entity's methods
            $dailySession->rating = $dailySession->rating();
            $dailySession->supervisor_name = $dailySession->supervisor_name();
            $dailySession->instructor_name = $dailySession->instructor_name();
            $dailySession->is_session = $dailySession->is_session();
            $dailySession->is_program_change = $dailySession->is_program_change();

            return $dailySession;
        }

        return null;
    }
}
