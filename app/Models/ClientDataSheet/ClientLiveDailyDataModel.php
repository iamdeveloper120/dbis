<?php

namespace App\Models\ClientDataSheet;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DailySession;

class ClientLiveDailyDataModel extends Model
{

    public function getClientLiveDailyData($client_id, $start_date, $end_date)
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

        // Add Filters based on selected Client and Dates
        if ($client_id) {
            $builder->where('vc.client_id', $client_id);
        }
        if ($start_date && $end_date) {
            $builder->where('vc.week_date >=', $start_date);
            $builder->where('vc.week_date <=', $end_date);
        }
        $builder->orderBy('vc.week_date', 'desc');

        // Execute the Query
        $result = $builder->get()->getResult(DailySession::class);

        // Prepare the data array for DataTable

        foreach ($result as $row) {
            $row->rating = $row->rating();
            $row->supervisor_name = $row->supervisor_name();
            $row->instructor_name = $row->instructor_name();
            $row->is_session = $row->is_session();
            $row->is_program_change = $row->is_program_change();
        }

        return $result;
    }

    public function getAggregatedMetricsByClient(array $clientIds, string $startDate, string $endDate): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $builder = $this->db->table('view_daily_data_combined vc');

        $builder->select('
        vc.client_id,
        clients.internal_mrn,
        SUM(vc.frequency_of_problem_behavior) AS total_pb_frequency,
        SUM(TIME_TO_SEC(vc.total_duration_of_problem_behavior)) AS total_pb_duration_seconds,
        SUM(vc.skills_retained) AS total_skills_retained,
        SUM(vc.doi) AS total_doi,
        SUM(vc.total_mands) AS total_mands,
        SUM(vc.variety_of_mands) AS total_mands_variety,
        SUM(vc.program_change_made) AS total_program_changes
    ');

        $builder->join('clients', 'clients.id = vc.client_id', 'left');
        $builder->whereIn('vc.client_id', $clientIds);
        $builder->where('vc.week_date >=', $startDate);
        $builder->where('vc.week_date <=', $endDate);
        $builder->groupBy('vc.client_id');

        return $builder->get()->getResultArray();
    }

    public function getClientDashboardWeeklyMetrics(int $clientId, string $startDate, string $endDate): array
    {
        $builder = $this->db->table('view_daily_data_combined vc');

        $builder->select('
            COALESCE(SUM(vc.hours), 0) AS total_hours_delivered,
            COALESCE(SUM(vc.skills_retained), 0) AS total_targets_mastered,
            COALESCE(SUM(vc.doi), 0) AS total_developing_independence,
            COALESCE(SUM(vc.variety_of_mands), 0) AS total_mand_frequency,
            COALESCE(SUM(vc.frequency_of_problem_behavior), 0) AS total_behaviour_incidents
        ');

        $builder->where('vc.client_id', $clientId);
        $builder->where('vc.week_date >=', $startDate);
        $builder->where('vc.week_date <=', $endDate);

        return $builder->get()->getRowArray() ?? [];
    }
}
