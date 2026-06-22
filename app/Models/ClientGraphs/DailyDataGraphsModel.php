<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DailySession;

class DailyDataGraphsModel extends Model
{
    protected $DBGroup  = 'default';


    public function get_client_session_data_for_graphs($client_id, $start_date, $end_date)
    {

        $builder = $this->db->table('view_daily_data_combined');
        $builder->select('view_daily_data_combined.*, ROUND(TIME_TO_SEC(view_daily_data_combined.total_duration_of_problem_behavior)/60,2) as total_min,u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, u2.first_name as instructor_first_name, u2.last_name as instructor_last_name,clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = view_daily_data_combined.supervisor_id', 'left');
        $builder->join('users u2', 'u2.id = view_daily_data_combined.instructor_id ', 'left');
        $builder->join('clients', 'clients.id = view_daily_data_combined.client_id ', 'left');
        $builder->where('view_daily_data_combined.client_id', $client_id);
        if ($start_date !== NULL && $end_date !== NULL) {
            $builder->where('view_daily_data_combined.week_date >= ', $start_date);
            $builder->where('view_daily_data_combined.week_date <=', $end_date);
        }
        $builder->orderBy('view_daily_data_combined.week_date', 'ASC');
        $query = $builder->get();

        $dates = [];
        $skills_retained = [];
        $doi = [];
        $total_mands = [];
        $variety_of_mands = [];
        $frequency_of_problem_behavior = [];
        $total_duration_of_problem_behavior = [];
        $session_quality_rating = [];
        foreach ($query->getResult(DailySession::class) as $session) {
            $week_date = stringToDate($session->week_date, CC_DATE_FORMAT);
            $dates[] = $week_date;
            $skills_retained[] = $session->skills_retained; //($session->skills_retained == 0 ? 'NULL' : $session->skills_retained);
            $doi[] = $session->doi; //($session->doi == 0 ? 'NULL' : $session->doi);
            $total_mands[] = $session->total_mands; //($session->total_mands == 0 ? 'NULL' : $session->total_mands);
            $variety_of_mands[] = $session->variety_of_mands;
            $frequency_of_problem_behavior[] = ($session->frequency_of_problem_behavior == null ? 0 : $session->frequency_of_problem_behavior); // $session->frequency_of_problem_behavior; //($session->frequency_of_problem_behavior == 0 ? 'NULL' : $session->frequency_of_problem_behavior);
            $total_duration_of_problem_behavior[] = ($session->total_min == null ? 0 : $session->total_min); // $session->total_min;
            $session_quality_rating[] = $session->session_quality_rating;
        }

        $total_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Total Mands',
                    'data' => $total_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $variety_of_mands_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Variety of Mands',
                    'data' => $variety_of_mands,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $frequency_of_problem_behavior_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Frequency of problem behavior',
                    'data' => $frequency_of_problem_behavior,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $total_duration_of_problem_behavior_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Total duration of problem behavior',
                    'data' => $total_duration_of_problem_behavior,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $session_quality_rating_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Session quality rating',
                    'data' => $session_quality_rating,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];

        $skills_retained_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Skills retained',
                    'data' => $skills_retained,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];
        $doi_data = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Degrees of Independence',
                    'data' => $doi,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2
                ]

            ]
        ];

        $result_array = [
            'total_mands' => $total_mands_data,
            'variety_of_mands' => $variety_of_mands_data,
            'frequency_of_problem_behavior' => $frequency_of_problem_behavior_data,
            'total_duration_of_problem_behavior' => $total_duration_of_problem_behavior_data,
            'session_quality_rating' => $session_quality_rating_data,
            'skills_retained' => $skills_retained_data,
            'doi' => $doi_data,
        ];
        return $result_array;
    }
}
