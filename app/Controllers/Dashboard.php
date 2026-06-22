<?php

namespace App\Controllers;

use App\Controllers\AdminController;
use App\Models\ClientSessions\DailySessionModel;
use App\Models\ClientProgram\ClientProgramChangeModel;
use App\Models\ClientProgram\ClientProgramChangeAlertModel;

use App\Models\ClientProgram\ClientTargetsRetainedModel;
use App\Models\ClientProgram\ClientTargetModel;
use App\Models\ClientSessions\DailySessionDataProcessedModel;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientDataSheet\ClientLiveDailyDataModel;

use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends AdminController
{
    public function index(): string
    {
        $this->page_title = 'Dashboard';
        return view('dashboard', [
            'page_title' => $this->page_title
        ]);
    }

    public function data(): ResponseInterface
    {
        $clientModel = model(ClientModel::class);
        $clients = $clientModel->get_dashboard_active_client_list();
        $clientIds = array_column($clients, 'id'); // ✅ This is used to limit all data
        $start = $this->request->getPost('start_date');
        $end   = $this->request->getPost('end_date');

        // Fallback to today if date range is missing
        if (empty($start) || empty($end)) {
            $today = date('Y-m-d');
            $start = $end = $today;
        }

        $counts = [
            'in_progress'     => 0,
            'in_review'       => 0,
            'processed'       => 0,
            'conflict'        => 0,
            'program_alerts'  => 0,
            'program_changes' => 0,
        ];

        // Sessions
        $sessionModel = new DailySessionModel();
        $sessions = $sessionModel->get_client_executed_sessions(NULL, $start, $end, NULL, NULL, NULL, $clientIds);
        // ✅ NEW STEP: Extract only clients that actually have sessions
        $clientIdsWithSessions = array_unique(array_column($sessions, 'client_id'));

        // If no sessions, keep empty array (no charts)
        if (empty($clientIdsWithSessions)) {
            $clientIdsWithSessions = [];
        }

        foreach ($sessions as $session) {
            switch ((int) $session['status']) {
                case 1:
                    $counts['in_progress']++;
                    break;
                case 2:
                    $counts['in_review']++;
                    break;
                case 3:
                    $counts['processed']++;
                    break;
                case 4:
                    $counts['conflict']++;
                    break;
            }
        }

        // Add this after session status processing
        $ratingCounts = [
            'poor' => 0,
            'good' => 0,
            'excellent' => 0,
        ];
        $ratingMrns = [
            'poor' => [],
            'good' => [],
            'excellent' => [],
        ];

        foreach ($sessions as $session) {
            $rating = (int) $session['session_rating'];
            $mrn = $session['internal_mrn'] ?? null;

            if (!$mrn) {
                continue; // skip if MRN is null
            }

            switch ($rating) {
                case 1:
                    $ratingCounts['poor']++;
                    if (!in_array($mrn, $ratingMrns['poor'])) {
                        $ratingMrns['poor'][] = $mrn;
                    }
                    break;
                case 2:
                    $ratingCounts['good']++;
                    if (!in_array($mrn, $ratingMrns['good'])) {
                        $ratingMrns['good'][] = $mrn;
                    }
                    break;
                case 3:
                    $ratingCounts['excellent']++;
                    if (!in_array($mrn, $ratingMrns['excellent'])) {
                        $ratingMrns['excellent'][] = $mrn;
                    }
                    break;
            }
        }


        $sessionComments = [];
        foreach ($sessions as $s) {
            if (!empty($s['instructor_comments']) || !empty($s['comments'])) {
            }
            $sessionComments[] = [
                'client'              => $s['internal_mrn'],
                'instructor_name'     => trim($s['instructor_first_name'] . ' ' . $s['instructor_last_name']),
                'session_date'        => app_date($s['session_date']),
                'start_time'          => $s['start_time'],
                'end_time'            => $s['end_time'],
                'qr'                  => $s['session_rating'],
                'instructor_comments' => $s['instructor_comments'],
                'wow_moments'         => $s['comments']
            ];
        }

        // Program Alerts
        $alertModel = new ClientProgramChangeAlertModel();
        $counts['program_alerts'] = $alertModel
            ->whereIn('client_id', $clientIds)
            ->where('session_date >=', $start)
            ->where('session_date <=', $end)
            ->countAllResults();

        // Program Changes
        $changeModel = new ClientProgramChangeModel();
        $counts['program_changes'] = $changeModel
            ->whereIn('client_id', $clientIds)
            ->where('session_date >=', $start)
            ->where('session_date <=', $end)
            ->countAllResults();


        // Step: Load progress data for active clients         
        $progressData = $clientModel->getProgressAll($clientIds);
        $progressData = array_values(array_filter($progressData, static function ($row) {
            if (!isset($row['percentage'])) {
                return false;
            }
            return (float) $row['percentage'] > 0;
        }));


        usort($progressData, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage']; // Descending order
        });

        $dailyDataModel = model(ClientLiveDailyDataModel::class);
        $aggregated = $dailyDataModel->getAggregatedMetricsByClient($clientIds, $start, $end);

        $changeCountsByClient = $this->indexBy(
            $changeModel->getClientProgramChangeCounts($clientIds, $start, $end),
            'client_id'
        );
        $changeCountsByClient = $changeModel->getClientProgramChangeCounts($clientIds, $start, $end);
        log_message('debug', 'Change count results: ' . json_encode($changeCountsByClient));
        $changeCountsByClient = $this->indexBy($changeCountsByClient, 'client_id');
        log_message('debug', 'Change count results: ' . json_encode($changeCountsByClient));

        $alertCountsByClient = $alertModel->getClientProgramAlertCounts($clientIds, $start, $end);
        log_message('debug', 'Alert count results: ' . json_encode($alertCountsByClient));
        $alertCountsByClient = $this->indexBy($alertCountsByClient, 'client_id');
        log_message('debug', 'Alert count results: ' . json_encode($alertCountsByClient));

        $aggregatedByClient = $this->indexBy($aggregated, 'client_id');

        $clientMetrics = [];
        foreach ($clients as $client) {
            if (!in_array($client->id, $clientIdsWithSessions)) {
                continue; // SKIP CLIENTS WITH NO SESSIONS
            }
            $cid = $client->id;
            $row = $aggregatedByClient[$cid] ?? [];

            $clientMetrics[] = [
                'client'            => $client->internal_mrn,
                'pb_frequency'      => isset($row['total_pb_frequency']) ? (int) $row['total_pb_frequency'] : 0,
                'pb_duration_secs'  => isset($row['total_pb_duration_seconds']) ? (int) $row['total_pb_duration_seconds'] : 0,
                'skills_retained'   => isset($row['total_skills_retained']) ? (int) $row['total_skills_retained'] : 0,
                'doi'               => isset($row['total_doi']) ? (int) $row['total_doi'] : 0,
                'mands_freq'        => isset($row['total_mands']) ? (int) $row['total_mands'] : 0,
                'mands_variety'     => isset($row['total_mands_variety']) ? (int) $row['total_mands_variety'] : 0,
                'program_changes'   => $changeCountsByClient[$cid]['total_changes'] ?? 0,
                'program_alerts'    => $alertCountsByClient[$cid]['total_alerts'] ?? 0,
            ];
        }


        $response = [
            'status' => 'success',
            'data'   => $counts,
            'progress' => $progressData,
            'metrics'  => $clientMetrics,
            'ratings'  => $ratingCounts, // ✅ add this
            'ratings_clients' => $ratingMrns,
            'session_comments'  => $sessionComments, // ✅ add this
        ];

        return $this->response->setJSON($response);
    }
    private function indexBy(array $array, string $key): array
    {

        $output = [];
        foreach ($array as $row) {
            $output[$row[$key]] = $row;
        }
        return $output;
    }


    public function access_denied()
    {
        return view('access_denied');
    }
}
