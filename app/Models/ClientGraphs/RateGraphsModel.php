<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;


class RateGraphsModel extends Model
{
    protected $DBGroup  = 'default';

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
    /************************************************************************ */
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

    /*********************************************************** */
}
