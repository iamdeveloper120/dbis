<?php

namespace App\Validation;

use CodeIgniter\I18n\Time;

class CustomRules
{
    public function compareTimes(string $str, string $fields, array $data): bool
    {
        // Get start time and end time from form data
        $startTime = \DateTime::createFromFormat('H:i:s', $data[$fields]);
        $endTime = \DateTime::createFromFormat('H:i:s', $str);

        // If either time is not valid, return false
        if (!$startTime || !$endTime) {
            return false;
        }

        // Compare the two times
        if ($endTime > $startTime) {
            return true; // End time is greater than start time
        } else {
            return false; // End time is not greater than start time
        }
    }

    function compareDates(string $str, string $fields, array $data): bool
    {

        $id = NULL;
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $start_date = new \DateTime($start_date);
        $start_date = $start_date->format('Y-m-d');

        $end_date = new \DateTime($end_date);
        $end_date = $end_date->format('Y-m-d');

        if ($end_date >= $start_date) {

            return true;
        } else {

            return false;
        }
    }
    public function valid_time_format(string $str): bool
    {
        // Regular expression to match the time format (HH:MM:SS)
        $pattern = '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/';
        // Check if the input matches the time format

        return (bool) preg_match($pattern, $str);
    }

    public function array_not_empty(array $arr, string $fields, array $data): bool
    {
        if (count($arr) === 0) {
            return false;
        }

        return true;
    }

    function is_weekly_session_date_exist(string $str, ?string $fields = '', array $data = [], ?string &$error = null): bool
    {
        $id = NULL;
        $week_start_day = (int) CC_WEEK_START_DAY;

        $client_id = $data['client_id'];
        $week_date = $data['week_date'];

        $week_date = stringToDate($week_date, 'Y-m-d');

        // Calculate the week start and end dates
        $week_day = (new \DateTime($week_date))->format('w'); // Get the current day of the week (0 = Sunday, 6 = Saturday)
        $day_difference = ($week_day - $week_start_day + 7) % 7; // Adjust based on week start day
        $week_start_date = (new \DateTime($week_date))->modify("-$day_difference days")->format('Y-m-d');
        $week_end_date = (new \DateTime($week_start_date))->modify('+6 days')->format('Y-m-d');


        $db = \Config\Database::connect();

        $sql = "SELECT * FROM daily_session_manual WHERE client_id='$client_id'  AND (week_date >= '$week_start_date' AND week_date <= '$week_end_date')";
        $query_daily_manually  = $db->query($sql);
        if ($query_daily_manually->getNumRows() > 0) {
            $error = 'Manually Daily session data already exists for given date';
            return false;
        }

        $sql_live_session = "SELECT * FROM daily_sessions WHERE client_id='$client_id'  AND (session_date >= '$week_start_date' AND session_date <= '$week_end_date')";
        $query_live_session = $db->query($sql_live_session);
        if ($query_live_session->getNumRows() > 0) {
            $error = 'Live session dta already exists for selected date';
            return false;
        }



        $sql_weekly = "SELECT * FROM daily_session_manual_weekly WHERE client_id='$client_id'  AND (week_date >= '$week_start_date' AND week_date <= '$week_end_date')";
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
            $sql_weekly = "SELECT * FROM daily_session_manual_weekly   WHERE client_id='$client_id' AND (week_date >= '$week_start_date' AND week_date <= '$week_end_date') AND id != '$id'";
        }

        $query_weekly = $db->query($sql_weekly);
        if ($query_weekly->getNumRows() > 0) {
            $error = 'Weekly session data already exists';
            return false;
        }

        // No conflicts, allow data entry
        return true;
    }
    function is_manual_daily_session_date_exist(string $str, ?string $fields = '', array $data = [], ?string &$error = null): bool
    {
        $client_id = $data['client_id'];
        $week_date = stringToDate($data['week_date'], 'Y-m-d');

        // Convert CC_WEEK_START_DAY to an integer
        $week_start_day = (int) CC_WEEK_START_DAY;

        $db = \Config\Database::connect();

        // Check 1: Daily session already exists in `daily_session_manual`
        $sql = "SELECT * FROM daily_session_manual WHERE client_id='$client_id' AND week_date='$week_date'";
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
            $sql = "SELECT * FROM daily_session_manual WHERE client_id='$client_id' AND week_date='$week_date' AND id != '$id'";
        }

        $query = $db->query($sql);
        if ($query->getNumRows() > 0) {
            $error = 'Manual daily session already exists';
            return false;
        }

        // Check 2: Weekly data exists in `daily_session_manual_weekly`
        // Calculate the week start and end dates
        $week_day = (new \DateTime($week_date))->format('w'); // Get the current day of the week (0 = Sunday, 6 = Saturday)
        $day_difference = ($week_day - $week_start_day + 7) % 7; // Adjust based on week start day
        $week_start_date = (new \DateTime($week_date))->modify("-$day_difference days")->format('Y-m-d');
        $week_end_date = (new \DateTime($week_start_date))->modify('+6 days')->format('Y-m-d');


        $sql_weekly = "SELECT * FROM daily_session_manual_weekly 
                   WHERE client_id='$client_id' 
                   AND (week_date >= '$week_start_date' AND week_date <= '$week_end_date')";

        $query_weekly = $db->query($sql_weekly);
        if ($query_weekly->getNumRows() > 0) {
            $error = 'Weekly session data already exists';
            return false;
        }


        $sql_live_session = "SELECT * FROM daily_sessions WHERE client_id='$client_id' AND session_date='$week_date'";

        $query_live_session = $db->query($sql_live_session);
        if ($query_live_session->getNumRows() > 0) {
            $error = 'Live session already exists for selected date';
            return false;
        }

        // No conflicts, allow data entry
        return true;
    }

    function is_phase_line_date_exist(string $str, ?string $fields = '', array $data = [], ?string &$error = null): bool
    {
        $id = NULL;
        $client_id = $data['client_id'];
        $p_date = $data['p_date'];
        $graph_type = $data['graph_type'];
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
        }

        $db = \Config\Database::connect();

        $p_date = stringToDate($p_date, 'Y-m-d');

        $sql = '';
        if ($id != NULL) {
            $sql = "SELECT * FROM client_graph_phase_line WHERE client_id='$client_id' AND p_date ='$p_date' AND graph_type='$graph_type' AND id !='$id'";
        } else {
            $sql = "SELECT * FROM client_graph_phase_line WHERE client_id='$client_id' AND p_date ='$p_date' AND graph_type='$graph_type' ";
        }


        $query  = $db->query($sql);

        if ($query->getNumRows() > 0) {
            if ($graph_type == "Cumulative") {
                $error = 'Cumulative graph phase line date exist';
            } else {
                $error = 'Rate graph phase line date exist';
            }
            return false;
        } else {
            return true;
        }
    }

  
    function is_target_date_exist(string $str, ?string $fields = '', array $data = [], ?string &$error = null): bool
    {
        $id = null;
        $client_id = $data['client_id'];
        $t_date = '01-' . $data['t_date']; // Assuming t_date is in 'mm-yyyy' format
        $graph_type = $data['graph_type'];

        if (array_key_exists('id', $data)) {
            $id = $data['id'];
        }

        $db = \Config\Database::connect();
        $t_date = stringToDate($t_date, 'Y-m-d'); // Convert to date format for year and month extraction

        // Extract the year and month from the given date
        $year = date('Y', strtotime($t_date));
        $month = date('m', strtotime($t_date));

        $builder = $db->table('client_graph_target_month');
        $builder->where('client_id', $client_id);
        $builder->where('YEAR(t_date)', $year); // Check year
        $builder->where('MONTH(t_date)', $month); // Check month
        $builder->where('graph_type', $graph_type);

        // If an ID is provided, exclude that ID from the check
        if ($id !== null) {
            $builder->where('id !=', $id);
        }

        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            // Set appropriate error message
            $error = ($graph_type == "Skills") ? 'Skills Retained Target Month exists' : 'DOI Target Month exists';
            return false;
        }

        return true;
    }
}
