<?php

namespace App\Models\KPI;

use CodeIgniter\Model;
use App\Models\Auth\UserModel; 
use App\Models\ClientSessions\ManualDailySessionModel;
use App\Models\ClientSessions\ManualWeeklySessionModel;


class KPITargetModel extends Model
{
    /*********************************************************** */
    /********** Target Data ************************************ */
    /*********************************************************** */

    public function get_clients_target_and_rate_table_data()
    {
        $query = $this->db->query("
            SELECT
            c.internal_mrn AS client,
            c.internal_mrn,
            c.skills_target_rate AS target_skills,
            c.doi_target_rate AS target_doi,
            DATE_FORMAT(DATE(CONCAT(vcm.months, '-01')), '%Y-%m') AS 'year_month',
            vcm.skill_rate AS skill_rate,
            vcm.target_status AS skill_status,
            vcm.doi_rate AS doi_rate,
            vcm.target_status AS doi_status,
            vcm.target_status AS target_status
        FROM
            view_clients_met_target_month_vise vcm
            LEFT JOIN view_clients_all_targets c ON vcm.client_id = c.id
            LEFT JOIN view_clients_target_start_month ctsm ON vcm.client_id = ctsm.client_id
        WHERE             
             DATE(CONCAT(vcm.months, '-01')) > DATE(CONCAT(ctsm.start_month, '-01')) 
        ORDER BY
            client, 'year_month' ASC;
        ");

        $result = $query->getResultArray();

        $clients = [];
        $months = [];

        foreach ($result as $row) {
            $client = $row['client'];
            $yearMonth = $row['year_month'];

            if (!isset($clients[$client])) {
                $clients[$client] = [
                    'internal_mrn' => $row['internal_mrn'],
                    'skills_target_rate' => $row['target_skills'],
                    'doi_target_rate' => $row['target_doi'],
                    'data' => [],
                ];
            }

            if (!in_array($yearMonth, $months)) {
                $months[] = $yearMonth;
            }

            $clients[$client]['data'][$yearMonth] = [
                'skill_rate' => $row['skill_rate'],
                'target_status' => $row['target_status'],
                'doi_rate' => $row['doi_rate'],
            ];
        }
        sort($months);
        $data = [
            'clients' => $clients,
            'months' => $months,
        ];


        return $data;
    }

    /*********************************************************** */
    /********* Overall Percentage of clients met target ******** */
    /*********************************************************** */
    public function get_overall_percentage_of_clients_met_target()
    {
        $query = $this->db->query("
            SELECT 
                c.id,
                c.internal_mrn,
                SUM(CASE WHEN vcm.target_status = 1 THEN 1 ELSE 0 END) AS target_met_count,
                SUM(CASE WHEN vcm.target_status = 0 THEN 1 ELSE 0 END) AS target_not_met_count
            FROM
                clients c
                LEFT JOIN view_clients_target_start_month ctsm ON c.id = ctsm.client_id
                LEFT JOIN view_clients_met_target_month_vise vcm ON c.id = vcm.client_id
                    AND (vcm.target_status = 1 OR vcm.target_status = 0)
                    AND DATE(CONCAT(vcm.months, '-01')) > DATE(CONCAT(ctsm.start_month, '-01'))
            GROUP BY
                c.id, c.internal_mrn
            ORDER BY
            c.internal_mrn asc
        ");

        $result = $query->getResultArray();
        $clients = array();
        $internalMrns = array();
        $percentages = array();

        foreach ($result as $row) {
            $clients[] = $row['id'];
            $internalMrns[] = $row['internal_mrn'];

            $total = $row['target_met_count'] + $row['target_not_met_count'];
            $percentage = ($total > 0) ? round(($row['target_met_count'] / $total * 100), 2) : 0;
            $percentages[] = $percentage;
        }

        $clientData = array(
            'clients' => $clients,
            'internal_mrns' => $internalMrns,
            'percentages' => $percentages,
        );

        return $clientData;
    }

    public function get_selected_client_month_vise_target($id)
    {
        $query = $this->db->query("
        SELECT vcm.*
        FROM view_clients_met_target_month_vise vcm                
        Left JOIN view_clients_target_start_month ctsm ON vcm.client_id = ctsm.client_id
        where vcm.client_id=$id
        AND DATE(CONCAT(vcm.months, '-01')) > DATE(CONCAT(ctsm.start_month, '-01'))
        ORDER BY
        vcm.months asc;
        ");

        $result = $query->getResultArray();
        return $result;
    }

    /*********************************************************** */
    /******* Montly Percentage of clients met target *********** */
    /*********************************************************** */
    public function get_month_vise_percentage_clients_met_target()
    {
        $query = $this->db->query("
            SELECT 
            vcm.months,
            SUM(CASE WHEN vcm.target_status = 1 THEN 1 ELSE 0 END) AS target_met_count,
            SUM(CASE WHEN vcm.target_status = 0 THEN 1 ELSE 0 END) AS target_not_met_count
        FROM
            view_clients_target_start_month ctsm
            INNER JOIN view_clients_met_target_month_vise vcm ON ctsm.client_id = vcm.client_id
            AND (vcm.target_status = 1 OR vcm.target_status = 0)
            AND DATE(CONCAT(vcm.months, '-01')) > DATE(CONCAT(ctsm.start_month, '-01'))
        WHERE
            vcm.months IS NOT NULL
        GROUP BY
            vcm.months
        ORDER BY
            vcm.months
        ");

        $result = $query->getResultArray();
        $months = array();
        $percentages = array();

        foreach ($result as $row) {
            $months[] =  date('M-Y', strtotime($row['months']));

            $total = $row['target_met_count'] + $row['target_not_met_count'];
            $percentage = ($total > 0) ? round(($row['target_met_count'] / $total * 100), 2) : 0;
            $percentages[] = $percentage;
        }
        $clientData = array(
            'months' => $months,
            'percentages' => $percentages,
        );

        return $clientData;
    }
    public function get_client_month_vise_target_by_month($month)
    {
        $query = $this->db->query("
            SELECT
            c.internal_mrn AS client,
            c.internal_mrn,
            c.skills_target_rate AS target_skills,
            c.doi_target_rate AS target_doi,
            DATE_FORMAT(DATE(CONCAT(vcm.months, '-01')), '%Y-%m') AS 'year_month',
            vcm.skill_rate AS skill_rate,            
            vcm.doi_rate AS doi_rate,
            vcm.target_status AS target_status
        FROM
            view_clients_met_target_month_vise vcm
            LEFT JOIN view_clients_all_targets c ON vcm.client_id = c.id
            LEFT JOIN view_clients_target_start_month ctsm ON vcm.client_id = ctsm.client_id
        WHERE             
             DATE(CONCAT(vcm.months, '-01')) > DATE(CONCAT(ctsm.start_month, '-01')) AND vcm.months = '$month'
        ORDER BY
            client, 'year_month' ASC;
        ");

        $result = $query->getResultArray();


        return $result;
    }
    /*********************************************************** */
    /**********  All Supervisor KPI********* */
    /*********************************************************** */
    public function get_clients_percentage_met_target_by_supervisor()
    {
        $data = [];
        $userModel = model(UserModel::class);
        $sessionModel = model(ManualDailySessionModel::class);
        $weeklySessionModel = model(ManualWeeklySessionModel::class);

        $supervisors = $userModel->getUsersByGroups(['supervisor']);
        $data['supervisors'] = $supervisors;

        // Retrieve distinct months and clients for each supervisor
        $clientTargetStatusByMonth = $this->getTargetStatusByMonthByClient();
        $clientData = [];
        foreach ($supervisors as $supervisor) {
            $supervisorId = $supervisor->id;

            $months = $this->getDistinctMonths($sessionModel, $weeklySessionModel, $supervisorId);
            $clientsByMonth = $this->getClientsByMonth($sessionModel, $weeklySessionModel, $supervisorId, $months);

            // Target Status of clients by month
            foreach ($clientsByMonth as $month => $clients) {
                foreach ($clients as $clientId => $client) {
                    // Get the target_status and internal_mrn for the given client and month
                    $targetStatus = $clientTargetStatusByMonth[$clientId][$month]['target_status'] ?? '';
                    $internalMrn = $clientTargetStatusByMonth[$clientId][$month]['internal_mrn'] ?? '';
                    $clientsByMonth[$month][$clientId]['target_status'] = $targetStatus;
                    $clientsByMonth[$month][$clientId]['internal_mrn'] = $internalMrn;
                }
            }

            $clientData[] = [
                'supervisorId' => $supervisor->id,
                'supervisorName' => $supervisor->first_name . ' ' . $supervisor->last_name,
                'months' => $months,
                'clientsByMonth' => $clientsByMonth
            ];
        }

        $data['clientData'] = $clientData;
        $data['min_month'] = $this->getClientTargetMinMonth();
        return $data;
    }
    private function getTargetStatusByMonthByClient()
    {
        $queryResult = $this->db->table('view_clients_met_target_month_vise')
            ->select('view_clients_met_target_month_vise.target_status, clients.internal_mrn, view_clients_met_target_month_vise.client_id, view_clients_met_target_month_vise.months')
            ->join('clients', 'clients.id = view_clients_met_target_month_vise.client_id')
            ->get()
            ->getResult();

        $resultArray = array();

        foreach ($queryResult as $row) {
            $client_id = $row->client_id;
            $month = $row->months;

            // Store data for each client and each month
            $resultArray[$client_id][$month] = array(
                'target_status' => $row->target_status,
                'internal_mrn' => $row->internal_mrn
            );
        }

        return $resultArray;
    }
    
    private function getDistinctMonths($sessionModel, $weeklySessionModel, $supervisorId)
    {
        $months = [];

        $sessionMonths = $sessionModel->distinct()
            ->select("DATE_FORMAT(week_date, '%Y-%m') as month")
            ->where('supervisor_id', $supervisorId)
            ->findAll();

        $weeklySessionMonths = $weeklySessionModel->distinct()
            ->select("DATE_FORMAT(week_date, '%Y-%m') as month")
            ->where('supervisor_id', $supervisorId)
            ->findAll();

        $sessionMonthsArray = array_column($sessionMonths, 'month');
        $weeklySessionMonthsArray = array_column($weeklySessionMonths, 'month');
        $months = array_unique(array_merge($sessionMonthsArray, $weeklySessionMonthsArray));

        return $months;
    }
    private function getClientsByMonth($sessionModel, $weeklySessionModel, $supervisorId, $months)
    {
        $clientsByMonth = [];
        $clientStartMonth = $this->getClientTargetStartMonths();

        foreach ($months as $month) {
            $sessionClients = $sessionModel->distinct()
                ->select('client_id')
                ->where('supervisor_id', $supervisorId)
                ->where("DATE_FORMAT(week_date, '%Y-%m')", $month)
                ->findAll();

            $weeklySessionClients = $weeklySessionModel->distinct()
                ->select('client_id')
                ->where('supervisor_id', $supervisorId)
                ->where("DATE_FORMAT(week_date, '%Y-%m')", $month)
                ->findAll();

            $sessionClientsArray = array_column($sessionClients, 'client_id');
            $weeklySessionClientsArray = array_column($weeklySessionClients, 'client_id');

            $clients = array_unique(array_merge($sessionClientsArray, $weeklySessionClientsArray));

            $clientsByMonth[$month] = [];
            foreach ($clients as $clientId) {
                if (array_key_exists($clientId, $clientStartMonth)) {
                    $startMonth = $clientStartMonth[$clientId];
                    if ($month > $startMonth) {
                        $clientsByMonth[$month][$clientId] = [];
                    }
                }
            }
        }


        return $clientsByMonth;
    }

    private function getClientTargetStartMonths()
    {
        $clientStartMonth = [];
        $resultSet = $this->db->table('view_clients_target_start_month')
            ->select('client_id, start_month')
            ->get()
            ->getResultArray();
        foreach ($resultSet as $row) {
            $client_id = $row['client_id'];
            $start_month = $row['start_month'];
            $clientStartMonth[$client_id] = $start_month;
        }

        return $clientStartMonth;
    }
    private function getClientTargetMinMonth()
    {

        $resultSet = $this->db->table('view_clients_target_start_month')
            ->select('MIN(start_month) as min_month')
            ->get()
            ->getRow();


        return $resultSet->min_month;
    }
    /*********************************************************** */
    /**
     * Generate a consistent color based on the input string.
     *
     * @param string $input The input string (e.g., supervisor name or ID)
     * @return string The generated color in rgba format (e.g., rgba(255, 0, 0, 1))
     */
    function getRandomColor($input)
    {
        // Seed the random generator based on the input string
        mt_srand(crc32($input));

        // Generate random RGB values
        $red = mt_rand(0, 255);
        $green = mt_rand(0, 255);
        $blue = mt_rand(0, 255);

        // Return the color in rgba format
        return "rgba($red, $green, $blue, 1)";
    }
    /*********************************************************** */
}
