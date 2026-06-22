<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;


class CumulativeGraphsModel extends Model
{
    protected $DBGroup  = 'default';

    //*********************************************************** */
    // Cumulative Graph
    /************************************************************ */
    /*********************************************************** */
    function cumulative_sum_start($client_id, $start_date)
    {

        $sql = "select client_id, sum(skills_retained) as skills_retained, sum(doi) as doi from view_cumulative_graph_data WHERE client_id=$client_id AND (week_date < '$start_date')";

        $query = $this->db->query($sql);
        $row = $query->getRow(0);
        return [
            'skill_start' => $row->skills_retained,
            'doi_start' =>  $row->doi
        ];
    }
    public function get_cumulative_data($client_id, $start_date, $end_date)
    {
        $skill_start = 0;
        $doi_start = 0;
        if ($start_date !== NULL && $end_date !== NULL) {
            $cum_start = $this->cumulative_sum_start($client_id, $start_date);

            $skill_start = $cum_start['skill_start'] == '' ? 0 : $cum_start['skill_start'];
            $doi_start = $cum_start['doi_start'] == '' ? 0 : $cum_start['doi_start'];
        }
        $sql  =   "SET @cumulative_sum_skill_retained := $skill_start, @cumulative_sum_doi := $doi_start";
        $this->db->query($sql);

        if ($start_date !== NULL && $end_date !== NULL) {

            $sql = "select * from (select week_date,hours,status,
        skills_retained,if(skills_retained IS NULL,  @cumulative_sum_skill_retained := @cumulative_sum_skill_retained, @cumulative_sum_skill_retained := @cumulative_sum_skill_retained + skills_retained) as cumulative_skill, 
        doi, if(doi IS NULL, @cumulative_sum_doi := @cumulative_sum_doi, @cumulative_sum_doi := @cumulative_sum_doi + doi) as cumulative_doi,
        NULL as p_key from view_cumulative_graph_data WHERE client_id=$client_id AND (week_date >= '$start_date' AND week_date <= '$end_date') order by week_date ASC)p UNION  (SELECT p_date as week_date, NULL as hours, 2 as status, NULL as skills_retained, NULL as cumulative_skill, NULL as doi, NULL as cumulative_doi, 
        p_key FROM `client_graph_phase_line` where client_id=$client_id AND graph_type='Cumulative' AND (p_date >= '$start_date' AND p_date <= '$end_date')) order by week_date ASC";
        } else {
            $sql = "select * from (select week_date,hours,status,
        skills_retained,if(skills_retained IS NULL,  @cumulative_sum_skill_retained := @cumulative_sum_skill_retained, @cumulative_sum_skill_retained := @cumulative_sum_skill_retained + skills_retained) as cumulative_skill, 
        doi, if(doi IS NULL, @cumulative_sum_doi := @cumulative_sum_doi, @cumulative_sum_doi := @cumulative_sum_doi + doi) as cumulative_doi,
        NULL as p_key from view_cumulative_graph_data WHERE client_id=$client_id order by week_date ASC)p UNION  (SELECT p_date as week_date, NULL as hours, 2 as status, NULL as skills_retained, NULL as cumulative_skill, NULL as doi, NULL as cumulative_doi, 
        p_key FROM `client_graph_phase_line` where client_id=$client_id AND graph_type='Cumulative') order by week_date ASC";
        }


        $query = $this->db->query($sql);
        $months = [];
        $skills = [];
        $doi = [];
        $no_session = [];

        foreach ($query->getResult() as $row) {

            if ($row->status == 0) {
                $week_date = stringToDate($row->week_date, CC_DATE_FORMAT);
                $skills[] = NULL;
                $doi[] = NULL;
                $no_session[] = 0;
                $months[] = $week_date;
            }

            if ($row->status == 1) {
                $week_date = stringToDate($row->week_date, CC_DATE_FORMAT);
                $months[] = $week_date;
                if ($row->cumulative_skill == 0 || $row->cumulative_skill == NULL) {
                    $skills[] = NULL;
                } else {
                    $skills[] = $row->cumulative_skill;
                }

                if ($row->cumulative_doi == 0 || $row->cumulative_doi == NULL) {
                    $doi[] = NULL;
                } else {
                    $doi[] = $row->cumulative_doi;
                }
                $no_session[] = NULL;
            }

            if ($row->status == 2) {
                $week_date = stringToDate($row->week_date, CC_DATE_FORMAT);
                $months[] = $week_date;
                $skills[] = NULL;
                $doi[] = NULL;
                $no_session[] = NULL;
            }
        }
        $no_session = $this->removeConsecutiveNoSession($no_session);
        $graph_data = [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $skills,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBorderColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2,


                ],
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $doi,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2,
                ],
                [
                    'label' => 'No Session',
                    'data' => $no_session,
                    'lineTension' => 0,
                    'borderWidth' => 2,
                    'rotation' => 35,
                    'pointBorderWidth' => 1,
                    'pointRadius' => 5,
                    'showLine' => false,
                    'fill' => false,
                ]

            ]
        ];

        $phaseline_data = $this->get_phase_line_data($client_id, 'Cumulative', $start_date, $end_date);

        $result_array = [
            'table' =>  $phaseline_data['table'],
            'phaseline' => $phaseline_data['phaseline'],
            'graph_data' => $graph_data

        ];

        return $result_array;
    }
    //*********************************************************** */
    // Phaseline for both Graph Cumulative Graph & Target Rate Graph
    /************************************************************ */
    public function get_phase_line_data($client_id, $graph_type, $start_date = NULL, $end_date = NULL)
    {
        $builder = $this->db->table('client_graph_phase_line');
        $builder->where('client_graph_phase_line.client_id', $client_id);
        $builder->where('client_graph_phase_line.graph_type', $graph_type);
        if ($start_date !== NULL && $end_date !== NULL) {
            $builder->where('client_graph_phase_line.p_date >= ', $start_date);
            $builder->where('client_graph_phase_line.p_date <=', $end_date);
        }
        $builder->orderBy('client_graph_phase_line.p_date', 'ASC');

        $query = $builder->get();

        /*  $table = new \CodeIgniter\View\Table();
       $template = [
            'table_open' => '<table class="col-md-12 phase_line_key_table table table-striped table-bordered table-hover">',
        ];
        $table->setTemplate($template);
        $table->setHeading('#',"Date", 'Phase Line Key');*/
        $tableHeading = '
            <th style="width: 5%;">#</th>
            <th style="width:10%;">Date</th>
            <th style="width: 85%;">Phase Line Key</th>';

        $tableRows = '';
        $key_id = 1;
        $phaseline1 = [];
        $phaseline2 = [];

        foreach ($query->getResult() as $row) {
            $week_date = '';

            if ($graph_type == 'Target_Rate') {
                $week_date = stringToDate($row->p_date, 'M-Y');
            }
            if ($graph_type == 'Cumulative') {
                $week_date = stringToDate($row->p_date, CC_DATE_FORMAT);
            }



            $phaseline1[] = [
                'drawTime' => "afterDatasetsDraw",
                'type'  => "line",
                'mode'  => "vertical",
                'scaleID'  => "x-axis-0",
                'value'  => $week_date,
                'borderWidth'  => 1,
                'borderColor'  => "rgba(0, 0, 0, 0.1)",
                'label' =>  [
                    'backgroundColor' => 'transparent',
                    'fontColor' => '#000000',
                    'content'  => $key_id,
                    'enabled' => true,
                    'display' => true,
                    'position' => "top",
                    'yAdjust' => -5,
                    'xAdjust' =>  5,
                    'fontSize' =>  13,

                ]
            ];

            //$table->addRow($key_id, $week_date, $row->p_key);
            $tableRows .= '
        <tr>
            <td>' . $key_id . '</td>
            <td>' . $week_date . '</td>
            <td>' . $row->p_key . '</td>
        </tr>';
            $key_id++;
        }
        $tableHTML = '<table class="col-md-12 phase_line_key_table table table-striped table-bordered table-hover">
            <thead>' . $tableHeading . '</thead>
            <tbody>' . $tableRows . '</tbody>
        </table>';
        return [
            //'table' => $table->generate(),
            'table' => $tableHTML,
            'phaseline' =>  $phaseline1
        ];
    }
    //*********************************************************** */
    // Target Graph Data
    /************************************************************ */
    public function target_months($client_id, $graph_type)
    {
        $sql = "SELECT DATE_FORMAT(t_date,'%Y-%m') as target_month " .
            "FROM `client_graph_target_month` " .
            "where client_id='$client_id' and graph_type='$graph_type'";

        $query = $this->db->query($sql);
        $target_months = [];
        foreach ($query->getResult() as $row) {
            $target_months[] = $row->target_month;
        }

        return $target_months;
    }
    /*********************************************************** */
    public function target_months_min_max($client_id, $graph_type)
    {

        $sql = "SELECT min(t_date) as target_min_date, max(t_date) as target_max_date " .
            "FROM `client_graph_target_month` " .
            "where client_id=$client_id and graph_type='$graph_type' ";

        $query = $this->db->query($sql);
        $target_min_max_months = [];
        foreach ($query->getResult() as $row) {
            $target_min_max_months = array(
                'min' => $row->target_min_date,
                'max' => $row->target_max_date
            );
        }

        return $target_min_max_months;
    }
    /*********************************************************** */
    function target_rate($client_id, $graph_type)
    {
        $sql = '';

        if ($graph_type == 'Skills') {
            $sql = "SELECT target_rate  "
                . "FROM view_clients_skills_target_rate "
                . "where id=$client_id and status=1  ";
        } else {
            $sql = "SELECT target_rate  "
                . "FROM view_clients_doi_target_rate "
                . "where id=$client_id and status=1  ";
        }

        $query = $this->db->query($sql);
        $row = $query->getRow(0);
        return $row->target_rate;
    }
    /*********************************************************** */
    function get_target_rate_data($client_id, $graph_type)
    {
        $phaseline_data = $this->get_phase_line_data($client_id, $graph_type, NULL, NULL);

        $target_rate = $this->target_rate($client_id, $graph_type);

        $target_months = $this->target_months($client_id, $graph_type);

        $target_months_min_max = $this->target_months_min_max($client_id, $graph_type);


        $monthyear = [];
        $monthly_average = [];
        $target_average = [];


        $from = '';
        $rate_column = '';
        $label = '';
        if ($graph_type == 'Skills') {
            $label = 'Retained Skills';
            $from = 'view_clients_skills_monthly_rate';
            $rate_column = 'skill_rate';
        } else {
            $label = 'Degree of Independence';
            $from = 'view_clients_doi_monthly_rate';
            $rate_column = 'doi_rate';
        }

        $sql =  "SELECT client_id, sortDate, displayDate, $rate_column AS average "
            . "FROM $from  "
            . "where  client_id =  $client_id "
            . " UNION "
            . " SELECT client_id, p_date as sortDate, date_format(p_date,'%b-%Y') as displayDate, NULL AS average FROM client_graph_phase_line "
            . "where  client_id = $client_id and graph_type='Target_Rate' "
            . "order by sortDate ASC";

        $query = $this->db->query($sql);

        $t_min_date = date("Y-m", strtotime($target_months_min_max['min']));
        $t_max_date = date("Y-m", strtotime($target_months_min_max['max']));
        $no_data = [];
        foreach ($query->getResult() as $row) {
            if (empty($target_months_min_max)) {
                $monthyear[] = $row->displayDate;
                $monthly_average[] = $row->average;
                if ($row->average == '' ||  $row->average == NULL) {
                    $no_data[] = 0;
                } else {
                    $no_data[] = NULL;
                }
            } else {

                $skill_date = date("Y-m", strtotime($row->sortDate));

                if ($skill_date >= $t_min_date && $skill_date <= $t_max_date) {
                    if (in_array($skill_date, $target_months)) {
                        $monthyear[] = $row->displayDate;
                        $monthly_average[] = $row->average;
                        $target_average[] = null;
                        $no_data[] = NULL;
                    }
                }
                if ($skill_date == $t_max_date) {
                    $monthyear[] = ".";
                    $monthly_average[] = null;
                    $target_average[] = null;
                    $no_data[] = NULL;
                }
                if ($skill_date > $t_max_date) {
                    $monthyear[] = $row->displayDate;
                    $monthly_average[] = $row->average;
                    $target_average[] = $target_rate;
                    if ($row->average == '' ||  $row->average == NULL) {
                        $no_data[] = 0;
                    } else {
                        $no_data[] = NULL;
                    }
                }
            }
        }
        $i = 0;


        $graph_data = [
            'labels' => $monthyear,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $monthly_average,
                    'lineTension' =>  0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBorderColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' =>  'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' =>  2,
                    'borderWidth' =>  2


                ],
                [
                    'label' => 'Target',
                    'data' => $target_average,
                    'lineTension' => 0,
                    'backgroundColor' =>  'rgba(255, 255, 255, 0.0)',
                    'borderColor' =>  'red', //'rgba(32, 116, 186, 1)',
                    'pointBorderColor' =>  'red', //rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' =>  'red', //'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' =>  0,
                    'borderWidth' =>  2,
                    'fill' => true,
                    'pointRadius' => 1,
                    'pointStyle' => 'dash',
                ],
                [
                    'label' => 'No Data',
                    'data' => $no_data,
                    'lineTension' => 0,
                    'borderWidth' => 2,
                    'pointStyle' => '',
                    'rotation' => 35,
                    'pointBorderWidth' => 1,
                    'pointRadius' => 5,
                    'showLine' => false,
                    'fill' => false
                ]

            ]
        ];

        $phaseline = $phaseline_data['phaseline'];
        $phaseline_table = $phaseline_data['table'];

        if (!empty($target_months_min_max)) {

            $phaseline[] = array(
                'drawTime' => "afterDatasetsDraw",
                'type' => "line",
                'mode' => "vertical",
                'scaleID' => "x-axis-0",
                'value' => '.',
                'borderWidth' => 1,

                'borderColor' => "#ecedef",
                'yMin' => 1,
                'yMax' => 1,
                'label' => array(
                    'backgroundColor' => 'transparent',
                    'fontColor' => '#000000',
                    'content' => 'Baseline',
                    'enabled' => true,
                    'position' => "top",
                    'yAdjust' => -5,
                    'xAdjust' => 30,
                    'fontSize' => 13,
                )
            );
            $i++;
        }


        return array(
            'graph_data' => $graph_data,
            'phase_line' => $phaseline,
            'phase_line_table' => $phaseline_table
        );
    }
    /***************************************************************************************** */
    // Following Function will remove consecutive no session
    /***************************************************************************************** */
    private function removeConsecutiveNoSession($arr)
    {
        $consecutiveZeros = 0;
        $consecutiveZeroIndices = [];

        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] === 0) {
                $consecutiveZeros++;
                $consecutiveZeroIndices[] = $i;
            } else {
                if ($consecutiveZeros > 1) {
                    $middleIndex = $consecutiveZeroIndices[floor(count($consecutiveZeroIndices) / 2)];
                    $arr[$middleIndex] = 0;
                    foreach ($consecutiveZeroIndices as $index) {
                        if ($index !== $middleIndex) {
                            $arr[$index] = null;
                        }
                    }
                }
                $consecutiveZeros = 0;
                $consecutiveZeroIndices = [];
            }
        }

        return $arr;
    }
    /*********************************************************** */
    public function getDomains($clientId)
    {
        // Base query: selecting domains that have goals with the specified probe type
        $builder = $this->db->table('client_program_domains d');
        $builder->select('d.id, d.name, d.domain_code');
        // Ensure we only get domains where probe sets have the specified type
        $builder->where('d.client_id', $clientId);
        $builder->orderBy('d.domain_code', 'Asc');

        return $builder->get()->getResult();
    }
    public function getGoalsByDomain($clientId, $domainId)
    {
        $builder = $this->db->table('client_program_goals g');
        $builder->select('g.id, g.name, g.goal_code');
        // Filter by client, domain, and probe type
        $builder->where('g.client_id', $clientId);
        $builder->where('g.domain_id', $domainId);
        $builder->orderBy('g.goal_code', 'Asc');

        return $builder->get()->getResult();
    }
    /*public function get_cumulative_data_by_domain_and_goal($client_id, $domain_id = null, $goal_id = null)
    {
        // Filters
        $domain_filter = $domain_id ? "AND domain_id = $domain_id" : "";
        $goal_filter = $goal_id ? "AND goal_id = $goal_id" : "";
    
        // Query Skills Retained
        $sql_skills = "
            SELECT session_date, COUNT(*) as count
            FROM client_program_targets_retained
            WHERE client_id = $client_id $domain_filter $goal_filter
            GROUP BY session_date
            ORDER BY session_date ASC
        ";
        $skill_result = $this->db->query($sql_skills)->getResult();
    
        // Query Degrees of Independence
        $sql_doi = "
            SELECT session_date, COUNT(*) as count
            FROM client_program_targets_doi
            WHERE client_id = $client_id $domain_filter $goal_filter
            GROUP BY session_date
            ORDER BY session_date ASC
        ";
        $doi_result = $this->db->query($sql_doi)->getResult();
    
        // Prepare cumulative arrays
        $skills_dates = [];
        $skills_data = [];
        $cumulative_skill = 0;
    
        foreach ($skill_result as $row) {
            $cumulative_skill += (int) $row->count;
            $skills_dates[] = stringToDate($row->session_date, CC_DATE_FORMAT);
            $skills_data[] = $cumulative_skill;
        }
    
        $doi_dates = [];
        $doi_data = [];
        $cumulative_doi = 0;
    
        foreach ($doi_result as $row) {
            $cumulative_doi += (int) $row->count;
            $doi_dates[] = stringToDate($row->session_date, CC_DATE_FORMAT);
            $doi_data[] = $cumulative_doi;
        }
    
        // Graph structure for Skills Retained
        $skills_graph_data = [
            'labels' => $skills_dates,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $skills_data,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ]
            ]
        ];
    
        // Graph structure for DOI
        $doi_graph_data = [
            'labels' => $doi_dates,
            'datasets' => [
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $doi_data,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ]
            ]
        ];
    
        // Return both
        $result_array = [
            'skills_graph_data' => $skills_graph_data,
            'doi_graph_data' => $doi_graph_data
        ];
    
        return $result_array;
    }*/
    /* public function get_cumulative_data_by_domain_and_goal($client_id, $domain_id = null, $goal_id = null)
    {


        // Filters
        $domain_filter = $domain_id ? "AND domain_id = $domain_id" : "";
        $goal_filter = $goal_id ? "AND goal_id = $goal_id" : "";

        // Fetch raw session data (daily)
        $sql_skills = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_retained
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $skills_raw = $this->db->query($sql_skills)->getResult();

        $sql_doi = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_doi
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $doi_raw = $this->db->query($sql_doi)->getResult();

        // Group data by week end date
        $group_by_week = function ($data) {
            $weekly = [];
            foreach ($data as $row) {
                $week_end = get_week_end_date($row->session_date); // Y-m-d
                if (!isset($weekly[$week_end])) {
                    $weekly[$week_end] = 0;
                }
                $weekly[$week_end] += (int)$row->count;
            }
            ksort($weekly);
            return $weekly;
        };

        $skills_weekly = $group_by_week($skills_raw);
        $doi_weekly = $group_by_week($doi_raw);

        // Compute cumulative data
        $skills_dates = [];
        $skills_data = [];
        $cumulative_skill = 0;

        foreach ($skills_weekly as $week_end => $count) {
            $cumulative_skill += $count;
            $skills_dates[] = stringToDate($week_end, CC_DATE_FORMAT); // display format
            $skills_data[] = $cumulative_skill;
        }

        $doi_dates = [];
        $doi_data = [];
        $cumulative_doi = 0;

        foreach ($doi_weekly as $week_end => $count) {
            $cumulative_doi += $count;
            $doi_dates[] = stringToDate($week_end, CC_DATE_FORMAT);
            $doi_data[] = $cumulative_doi;
        }

        // Graph data: Skills Retained
        $skills_graph_data = [
            'labels' => $skills_dates,
            'datasets' => [[
                'label' => 'Skills Retained',
                'data' => $skills_data,
                'lineTension' => 0,
                'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                'borderColor' => 'rgba(0, 0, 0, 1)',
                'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                'pointBorderWidth' => 2,
                'borderWidth' => 2,
            ]]
        ];

        // Graph data: DOI
        $doi_graph_data = [
            'labels' => $doi_dates,
            'datasets' => [[
                'label' => 'Degrees Of Independence',
                'data' => $doi_data,
                'lineTension' => 0,
                'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                'borderColor' => 'rgba(32, 116, 186, 1)',
                'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                'pointBorderWidth' => 2,
                'borderWidth' => 2,
            ]]
        ];

        // Graph data: Combined Skills & DOI
        // Re-align labels and data
        $combined_labels = array_unique(array_merge($skills_dates, $doi_dates));
        sort($combined_labels);

        // Map data by date for easy lookup
        $skills_map = array_combine($skills_dates, $skills_data);
        $doi_map = array_combine($doi_dates, $doi_data);

        $combined_skills = [];
        $combined_doi = [];

        foreach ($combined_labels as $label) {
            $combined_skills[] = $skills_map[$label] ?? null; // or 0 if you prefer
            $combined_doi[] = $doi_map[$label] ?? null;
        }

        $combined_graph_data = [
            'labels' => $combined_labels,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $combined_skills,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $combined_doi,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ]
            ]
        ];

        return [
            'skills_graph_data' => $skills_graph_data,
            'doi_graph_data' => $doi_graph_data,
            'combined_graph_data' => $combined_graph_data
        ];
    }*/
    public function get_cumulative_data_by_domain_and_goal($client_id, $domain_id = null, $goal_id = null)
    {
        helper('date'); // Load date helper with get_week_end_date()

        // Filters
        $domain_filter = $domain_id ? "AND domain_id = $domain_id" : "";
        $goal_filter = $goal_id ? "AND goal_id = $goal_id" : "";

        // Fetch session data grouped by day
        $sql_skills = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_retained
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $skills_raw = $this->db->query($sql_skills)->getResult();

        $sql_doi = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_doi
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $doi_raw = $this->db->query($sql_doi)->getResult();

        // Group data by week end date
        $group_by_week = function ($data) {
            $weekly = [];
            foreach ($data as $row) {
                $week_end = get_week_end_date($row->session_date); // Y-m-d
                if (!isset($weekly[$week_end])) {
                    $weekly[$week_end] = 0;
                }
                $weekly[$week_end] += (int)$row->count;
            }
            ksort($weekly);
            return $weekly;
        };

        $skills_weekly = $group_by_week($skills_raw);
        $doi_weekly = $group_by_week($doi_raw);

        // Get all unique week end dates
        $all_week_ends = array_unique(array_merge(array_keys($skills_weekly), array_keys($doi_weekly)));
        sort($all_week_ends);

        // Build cumulative combined graph data
        $combined_labels = [];
        $combined_skills = [];
        $combined_doi = [];

        // Before loop
        $cumulative_skills = 0;
        $cumulative_doi = 0;
        // Flags to know when first real data appears
        $skills_started = false;
        $doi_started = false;


        foreach ($all_week_ends as $week_end) {
            if (isset($skills_weekly[$week_end])) {
                $cumulative_skills += $skills_weekly[$week_end];
            }
            if (isset($doi_weekly[$week_end])) {
                $cumulative_doi += $doi_weekly[$week_end];
            }

            // Determine if each line should start independently
            if ($cumulative_skills > 0) {
                $skills_started = true;
            }
            if ($cumulative_doi > 0) {
                $doi_started = true;
            }

            // Add data for skills
            if ($skills_started) {
                $combined_skills[] = $cumulative_skills;
            } else {
                $combined_skills[] = null; // do not show on graph before start
            }

            // Add data for doi
            if ($doi_started) {
                $combined_doi[] = $cumulative_doi;
            } else {
                $combined_doi[] = null; // do not show on graph before start
            }

            // Always add week label
            $combined_labels[] = stringToDate($week_end, CC_DATE_FORMAT);
        }
        // Build final combined chart dataset
        $combined_graph_data = [
            'labels' => $combined_labels,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $combined_skills,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $combined_doi,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ]
            ]
        ];

        return [
            'combined_graph_data' => $combined_graph_data
        ];
    }
    public function get_cumulative_data_by_domain_and_goal_old($client_id, $domain_id = null, $goal_id = null)
    {
        helper('date'); // Load date helper with get_week_end_date()

        // Filters
        $domain_filter = $domain_id ? "AND domain_id = $domain_id" : "";
        $goal_filter = $goal_id ? "AND goal_id = $goal_id" : "";

        // Fetch session data grouped by day
        $sql_skills = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_retained
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $skills_raw = $this->db->query($sql_skills)->getResult();

        $sql_doi = "
        SELECT session_date, COUNT(*) as count
        FROM client_program_targets_doi
        WHERE client_id = $client_id $domain_filter $goal_filter
        GROUP BY session_date
        ORDER BY session_date ASC
    ";
        $doi_raw = $this->db->query($sql_doi)->getResult();

        // Group data by week end date
        $group_by_week = function ($data) {
            $weekly = [];
            foreach ($data as $row) {
                $week_end = get_week_end_date($row->session_date); // Y-m-d
                if (!isset($weekly[$week_end])) {
                    $weekly[$week_end] = 0;
                }
                $weekly[$week_end] += (int)$row->count;
            }
            ksort($weekly);
            return $weekly;
        };

        $skills_weekly = $group_by_week($skills_raw);
        $doi_weekly = $group_by_week($doi_raw);

        // Get all unique week end dates
        $all_week_ends = array_unique(array_merge(array_keys($skills_weekly), array_keys($doi_weekly)));
        sort($all_week_ends);

        // Build cumulative combined graph data
        $combined_labels = [];
        $combined_skills = [];
        $combined_doi = [];

        $cumulative_skills = null;
        $cumulative_doi = null;

        foreach ($all_week_ends as $week_end) {
            if (isset($skills_weekly[$week_end])) {
                $cumulative_skills = ($cumulative_skills ?? 0) + $skills_weekly[$week_end];
            }
            // If no new value, carry forward previous
            $combined_skills[] = $cumulative_skills;

            if (isset($doi_weekly[$week_end])) {
                $cumulative_doi = ($cumulative_doi ?? 0) + $doi_weekly[$week_end];
            }
            $combined_doi[] = $cumulative_doi;

            $combined_labels[] = stringToDate($week_end, CC_DATE_FORMAT);
        }

        // Build final combined chart dataset
        $combined_graph_data = [
            'labels' => $combined_labels,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $combined_skills,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $combined_doi,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ]
            ]
        ];

        return [
            'combined_graph_data' => $combined_graph_data
        ];
    }
}
