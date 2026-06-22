<?php

namespace App\Controllers\ClientSessions;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\Auth\UserModel;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientProgram\ClientDomainModel;
use App\Models\ClientProgram\ClientGoalModel;
use App\Models\ClientProgram\ClientProbeSetModel;

use App\Models\ClientProgram\ClientProgramModel;
use App\Models\Mands\MandsReinforcerModel;
use App\Models\Mands\ClientMandsReinforcerModel;

use App\Models\ClientSessions\DailySessionModel;
use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientProblemBehavior\DailySessionsPbRecordsModel;

use App\Models\Mands\MandsSessionDataModel;

use App\Models\ClientProblemBehavior\DropdownItemModel;
use App\Models\ClientProblemBehavior\ClientAbcItemModel;
use App\Models\ClientSessions\SessionPBDurationModel;

use App\Models\ClientSessions\SessionMandDurationModel;
use App\Models\ClientSessions\SessionDurationModel;

use App\Models\ClientDataSheet\ClientDataSheetModel;

use App\Models\ClientSessions\DailySessionDataProcessedModel;

use App\Models\ClientSessions\DailySessionProcessingLog;

class SessionReviewController extends AdminController
{
    use ResponseTrait;
    protected $dailySessionModel;
    protected $clientModel;
    protected $dailySessionDataCollectionModel;
    protected $pbRecordsModel;
    protected $mandsSessionDataModel;
    protected $mandsReinforcerModel;
    protected $clientMandsReinforcerModel;
    protected $dropdownItemModel;
    protected $clientAbcItemModel;
    protected $sessionPBDurationModel;

    protected $sessionMandDurationModel;
    protected $sessionDurationModel;

    protected $userModel;
    protected $clientProgramModel;

    protected $clientDataSheetModel;

    protected $clientDomainModel;
    protected $clientGoalModel;
    protected $clientProbeSetModel;
    protected $processModel;
    protected $dailySessionProcessingLog;


    public function __construct()
    {
        $this->dailySessionModel = new DailySessionModel();
        $this->dailySessionDataCollectionModel = new DailySessionDataCollectionModel();
        $this->clientModel = new ClientModel();
        $this->pbRecordsModel = new DailySessionsPbRecordsModel();
        $this->mandsSessionDataModel = new MandsSessionDataModel();
        $this->mandsReinforcerModel = new MandsReinforcerModel();
        $this->clientMandsReinforcerModel = new ClientMandsReinforcerModel();
        $this->dropdownItemModel = new DropdownItemModel();
        $this->clientAbcItemModel = new ClientAbcItemModel();
        $this->sessionPBDurationModel = new SessionPBDurationModel();

        $this->sessionMandDurationModel = new SessionMandDurationModel();
        $this->sessionDurationModel = new SessionDurationModel();
        $this->userModel = new UserModel();
        $this->clientProgramModel = new ClientProgramModel();

        $this->clientDataSheetModel = new ClientDataSheetModel();

        $this->clientDomainModel = new ClientDomainModel();
        $this->clientGoalModel = new ClientGoalModel();
        $this->clientProbeSetModel = new ClientProbeSetModel();
        $this->processModel = new DailySessionDataProcessedModel();

        $this->dailySessionProcessingLog = new DailySessionProcessingLog();
    }
    /********************************************************************************************************************************** */
    // Review Program collected data
    public function index($session_id)
    {
        // Fetch session data
        $session = $this->dailySessionModel->get_client_executed_session($session_id);
        $client = $this->clientModel->find($session->client_id);

        // Ensure session is in review status
        /*if ($session->status != 2) {
            return redirect()->back()->with('error', 'Session is not in review state.');
        }*/

        // Fetch collected data
        $collectedData = $this->dailySessionDataCollectionModel->getSessionData($session_id);

        return view('ClientSessionsReview/ProgramReview/index', [
            'client' => $client,
            'session' => $session,
            'collectedData' => $collectedData,
            'page_title' => 'Program Review'
        ]);
    }


    /********************************************************************************************************************************** */
    // Review Mands collected data
    public function mandsReview($session_id)
    {
        // Fetch session data
        $session = $this->dailySessionModel->get_client_executed_session($session_id);
        $client = $this->clientModel->find($session->client_id);

        // Ensure session is in review status
        /*if ($session->status != 2) {
            return redirect()->back()->with('error', 'Session is not in review state.');
        }*/

        // Fetch collected data
        $mandsData = $this->mandsSessionDataModel->getDailyDataBySession($session->client_id, $session->id);

        return view('ClientSessionsReview/MandsReview/index', [
            'client' => $client,
            'session' => $session,
            'mandsData' => $mandsData,
            'page_title' => 'Mands Review'
        ]);
    }

    /********************************************************************************************************************************** */
    // Review Mands collected data
    public function problemBehaviorReview($session_id)
    {
        // Fetch session data
        $session = $this->dailySessionModel->get_client_executed_session($session_id);
        $client = $this->clientModel->find($session->client_id);

        // Ensure session is in review status
        /*if ($session->status != 2) {
            return redirect()->back()->with('error', 'Session is not in review state.');
        }*/
        // Resolve dropdown options: client-specific first, then master fallback
        $antecedents = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $session->client_id, 'antecedent'));
        $behaviors = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $session->client_id, 'behavior'));
        $consequences = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $session->client_id, 'consequence'));
        // Fetch collected data
        $pbDailyData = $this->pbRecordsModel->getCompleteRecordSet($session->client_id, $session->id);


        return view('ClientSessionsReview/ProblemBehaviorReview/index', [
            'client' => $client,
            'session' => $session,
            'pbDailyData' => $pbDailyData,
            'antecedents' => $antecedents,
            'behaviors' => $behaviors,
            'consequences' => $consequences,
            'page_title' => 'Problem Behavior Review'
        ]);
    }

    /********************************************************************************************************************************** */
    // Review Mands collected data
    public function sessionDuration($session_id)
    {
        // Fetch session data
        $session = $this->dailySessionModel->get_client_executed_session($session_id);
        $client = $this->clientModel->find($session->client_id);


        return view('ClientSessionsReview/SessionDuration/index', [
            'client' => $client,
            'session' => $session,
            'page_title' => 'Session Duration',
        ]);
    }

    public function teachingDurationList()
    {
        $session_id = $this->request->getPost('session_id');
        $teachingDurations = $this->sessionDurationModel->asObject()->where('session_id', $session_id)->findAll();

        // Initialize total minutes to accumulate
        $totalMinutes = 0;

        foreach ($teachingDurations as $duration) {
            if ($duration->end_time != null) {
                $startTime = new \DateTime($duration->start_time);
                $endTime = new \DateTime($duration->end_time);

                // Calculate duration in minutes for each entry and accumulate
                $interval = $startTime->diff($endTime);
                $entryMinutes = ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $totalMinutes += $entryMinutes; // Accumulate total minutes

                // Format each entry for display (not affecting total)
                $duration->duration_time_format = $interval->format('%H:%I:%S');
                $duration->duration_decimal_format = number_format($entryMinutes / 60, 2, '.', ''); // Convert to hours for display only
            } else {
                $duration->duration_time_format = null;
                $duration->duration_decimal_format = null;
            }
        }

        // Final conversion of total minutes to hours and rounding (as per SQL view logic)
        $totalDecimalFormat = number_format($totalMinutes / 60, 2, '.', ''); // Final rounded total in hours

        // Calculate total duration in HH:MM:SS for display
        $totalHours = (int) floor($totalMinutes / 60);           // Ensure integer for hours
        $totalMinutesPart = (int) ($totalMinutes - (floor($totalMinutes / 60) * 60)); // Replace modulus with subtraction logic
        $totalSecs = (int) round(($totalMinutes - floor($totalMinutes)) * 60); // Ensure integer for seconds

        $totalTimeFormat = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutesPart, $totalSecs);
        // Append total row with consistent rounding
        $teachingDurations[] = (object)[
            'id' => null,
            'session_id' => $session_id,
            'session_date' => null,
            'client_id' => null,
            'start_time' => null,
            'end_time' => 'Total Duration',
            'duration_time_format' => $totalTimeFormat,
            'duration_decimal_format' => $totalDecimalFormat
        ];

        // Debug output to verify types before returning


        return $this->response->setJSON(['data' => $teachingDurations]);
    }

    public function mandsDurationList()
    {
        $session_id = $this->request->getPost('session_id');
        $mandsDurations = $this->sessionMandDurationModel->asObject()->where('session_id', $session_id)->findAll();

        // Initialize total minutes to accumulate
        $totalMinutes = 0;

        foreach ($mandsDurations as $duration) {
            if ($duration->end_time != null) {
                $startTime = new \DateTime($duration->start_time);
                $endTime = new \DateTime($duration->end_time);

                // Calculate duration in minutes for each entry and accumulate
                $interval = $startTime->diff($endTime);
                $entryMinutes = ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $totalMinutes += $entryMinutes; // Accumulate total minutes

                // Format each entry for display (not affecting total)
                $duration->duration_time_format = $interval->format('%H:%I:%S');
                $duration->duration_decimal_format = number_format($entryMinutes / 60, 2, '.', ''); // Convert to hours for display only
            } else {
                $duration->duration_time_format = null;
                $duration->duration_decimal_format = null;
            }
        }

        // Final conversion of total minutes to hours and rounding (as per SQL view logic)
        $totalDecimalFormat = number_format($totalMinutes / 60, 2, '.', ''); // Final rounded total in hours

        // Calculate total duration in HH:MM:SS for display
        $totalHours = floor($totalMinutes / 60);
        $totalMinutesPart = (int) ($totalMinutes - (floor($totalMinutes / 60) * 60)); // Replace modulus with subtraction logic
        $totalSecs = round(($totalMinutes - floor($totalMinutes)) * 60);
        $totalTimeFormat = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutesPart, $totalSecs);

        // Append total row with consistent rounding
        $mandsDurations[] = (object)[
            'id' => null,
            'session_id' => $session_id,
            'session_date' => null,
            'client_id' => null,
            'start_time' => null,
            'end_time' => 'Total Duration',
            'duration_time_format' => $totalTimeFormat,
            'duration_decimal_format' => $totalDecimalFormat
        ];

        return $this->response->setJSON(['data' => $mandsDurations]);
    }

    public function pbDurationList()
    {
        $session_id = $this->request->getPost('session_id');
        $mandsDurations = $this->sessionPBDurationModel->asObject()->where('session_id', $session_id)->findAll();

        // Initialize total minutes to accumulate
        $totalMinutes = 0;

        foreach ($mandsDurations as $duration) {
            if ($duration->end_time != null) {
                $startTime = new \DateTime($duration->start_time);
                $endTime = new \DateTime($duration->end_time);

                // Calculate duration in minutes for each entry and accumulate
                $interval = $startTime->diff($endTime);
                $entryMinutes = ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $totalMinutes += $entryMinutes; // Accumulate total minutes

                // Format each entry for display (not affecting total)
                $duration->duration_time_format = $interval->format('%H:%I:%S');
                $duration->duration_decimal_format = number_format($entryMinutes / 60, 2, '.', ''); // Convert to hours for display only
            } else {
                $duration->duration_time_format = null;
                $duration->duration_decimal_format = null;
            }
        }

        // Final conversion of total minutes to hours and rounding (as per SQL view logic)
        $totalDecimalFormat = number_format($totalMinutes / 60, 2, '.', ''); // Final rounded total in hours

        // Calculate total duration in HH:MM:SS for display
        $totalHours = floor($totalMinutes / 60);
        $totalMinutesPart = (int) ($totalMinutes - (floor($totalMinutes / 60) * 60)); // Replace modulus with subtraction logic
        $totalSecs = round(($totalMinutes - floor($totalMinutes)) * 60);
        $totalTimeFormat = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutesPart, $totalSecs);

        // Append total row with consistent rounding
        $mandsDurations[] = (object)[
            'id' => null,
            'session_id' => $session_id,
            'session_date' => null,
            'client_id' => null,
            'start_time' => null,
            'end_time' => 'Total Duration',
            'duration_time_format' => $totalTimeFormat,
            'duration_decimal_format' => $totalDecimalFormat
        ];

        return $this->response->setJSON(['data' => $mandsDurations]);
    }

    /********************************************************************************************************************************** */
    // Process data confirmation screen    

    public function processConfirmation($sessionId)
    {
        $db = \Config\Database::connect();
        // Fetch session details $session = $this->dailySessionModel->get_client_executed_session($session_id);
        $session = $this->dailySessionModel->get_client_executed_session($sessionId);
        $client = $this->clientModel->find($session->client_id);

        // Fetch last processing log
        $lastProcessingLog = $this->dailySessionProcessingLog
            ->select('daily_session_processing_log.*, users.first_name, users.last_name')
            ->join('users', 'users.id = daily_session_processing_log.processed_by', 'left')
            ->where('daily_session_processing_log.session_id', $sessionId)
            ->orderBy('daily_session_processing_log.processed_at', 'DESC')
            ->first();

        $conflictedTargets = [];
        $deletedTargets = [];
        $targetIds = [];
        $sessionDetails = [];

        if ($lastProcessingLog) {
            // Decode session and processing details
            $sessionDetails = json_decode($lastProcessingLog['session_details'], true);
            $processingResponses = json_decode($lastProcessingLog['details'], true);

            // Extract Conflicted & Deleted Targets
            foreach ($processingResponses as $response) {
                if (isset($response['is_deleted']) && $response['is_deleted']) {
                    $deletedTargets[] = [
                        'data' => $response['data'],
                        'message' => $response['message']
                    ];
                    $targetIds[] = $response['data']['target_id'];
                } elseif (isset($response['status']) && $response['status'] == 'conflict' && !isset($response['is_deleted'])) {
                    $conflictedTargets[] = [
                        'data' => $response['data'],
                        'message' => $response['message']
                    ];
                    $targetIds[] = $response['data']['target_id'];
                }
            }

            // Remove duplicates
            $targetIds = array_unique($targetIds);

            // Fetch Target Details (Domain, Goal, Target Names)
            $targetDetails = [];
            if (!empty($targetIds)) {
                $targetDetailsQuery = $db->table('client_program_targets')
                    ->select('client_program_targets.id as target_id, client_program_targets.name as target_name, 
                          client_program_goals.id as goal_id, client_program_goals.name as goal_name,
                          client_program_domains.id as domain_id, client_program_domains.name as domain_name')
                    ->join('client_program_goals', 'client_program_goals.id = client_program_targets.goal_id', 'left')
                    ->join('client_program_domains', 'client_program_domains.id = client_program_goals.domain_id', 'left')
                    ->whereIn('client_program_targets.id', $targetIds)
                    ->get()
                    ->getResultArray();

                foreach ($targetDetailsQuery as $detail) {
                    $targetDetails[$detail['target_id']] = $detail;
                }
            }

            // Append Domain, Goal, Target Names & Extract Data
            foreach ($conflictedTargets as &$conflicted) {
                $targetId = $conflicted['data']['target_id'];
                if (isset($targetDetails[$targetId])) {
                    $conflicted['data']['domain_name'] = $targetDetails[$targetId]['domain_name'];
                    $conflicted['data']['goal_name'] = $targetDetails[$targetId]['goal_name'];
                    $conflicted['data']['target_name'] = $targetDetails[$targetId]['target_name'];
                }

                $collectedData = json_decode($conflicted['data']['collected_data'], true);
                $conflicted['data']['phase_name'] = $collectedData['phase']['name'] ?? 'N/A';
                $conflicted['data']['result'] = implode(', ', $collectedData['result'] ?? []);
            }

            foreach ($deletedTargets as &$deleted) {
                $targetId = $deleted['data']['target_id'];
                if (isset($targetDetails[$targetId])) {
                    $deleted['data']['domain_name'] = $targetDetails[$targetId]['domain_name'];
                    $deleted['data']['goal_name'] = $targetDetails[$targetId]['goal_name'];
                    $deleted['data']['target_name'] = $targetDetails[$targetId]['target_name'];
                }

                $collectedData = json_decode($deleted['data']['collected_data'], true);
                $deleted['data']['phase_name'] = $collectedData['phase']['name'] ?? 'N/A';
                $deleted['data']['result'] = implode(', ', $collectedData['result'] ?? []);
            }
        }

        // Fetch session processing history
        $processingLogs = $this->dailySessionProcessingLog
            ->select('daily_session_processing_log.*, users.first_name, users.last_name') // Select required fields
            ->join('users', 'users.id = daily_session_processing_log.processed_by', 'left') // Join users table
            ->where('session_id', $sessionId)
            ->orderBy('processed_at', 'DESC')
            ->findAll(); // Fetch all processing attempts

        return view('ClientSessionsReview/ProcessConfirmation/index', [
            'client' => $client,
            'session' => $session,
            'lastProcessingLog' => $lastProcessingLog,
            'conflictedTargets' => $conflictedTargets,
            'deletedTargets' => $deletedTargets,
            'processingLogs' => $processingLogs,  // Send logs to the view
            'sessionDetails' => $sessionDetails,
            'page_title' => 'Process Confirmation',
        ]);
    }


    public function getProcessingDetails()
    {
        $logId = $this->request->getPost('logId');
        $db = \Config\Database::connect();
        $log = $this->dailySessionProcessingLog
            ->select('daily_session_processing_log.*, users.first_name, users.last_name')
            ->join('users', 'users.id = daily_session_processing_log.processed_by', 'left')
            ->where('daily_session_processing_log.id', $logId)
            ->first();

        if (!$log) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log not found']);
        }

        // Decode JSON stored in the log
        $processingResponses = json_decode($log['details'], true);
        $sessionDetails = json_decode($log['session_details'], true);

        $conflictedTargets = [];
        $deletedTargets = [];
        $processedTargets = [];
        $targetIds = [];

        // Extract Conflicted & Deleted Targets
        foreach ($processingResponses as $response) {
            if (isset($response['is_deleted']) && $response['is_deleted']) {
                $deletedTargets[] = [
                    'data' => $response['data'],
                    'message' => $response['message']
                ];
                $targetIds[] = $response['data']['target_id'];
            } elseif (isset($response['status']) && $response['status'] == 'conflict' && !isset($response['is_deleted'])) {
                $conflictedTargets[] = [
                    'data' => $response['data'],
                    'message' => $response['message']
                ];
                $targetIds[] = $response['data']['target_id'];
            } elseif (isset($response['success']) && $response['success']) {
                $processedTargets[] = [
                    'data' => $response['data'],
                    'message' => $response['message']
                ];
                $targetIds[] = $response['data']['target_id'];
            }
        }

        // Remove duplicates
        $targetIds = array_unique($targetIds);

        // Fetch Target Details (Domain, Goal, Target Names)
        $targetDetails = [];
        if (!empty($targetIds)) {
            $targetDetailsQuery = $db->table('client_program_targets')
                ->select('client_program_targets.id as target_id, client_program_targets.name as target_name, 
                      client_program_goals.id as goal_id, client_program_goals.name as goal_name,
                      client_program_domains.id as domain_id, client_program_domains.name as domain_name')
                ->join('client_program_goals', 'client_program_goals.id = client_program_targets.goal_id', 'left')
                ->join('client_program_domains', 'client_program_domains.id = client_program_goals.domain_id', 'left')
                ->whereIn('client_program_targets.id', $targetIds)
                ->get()
                ->getResultArray();

            foreach ($targetDetailsQuery as $detail) {
                $targetDetails[$detail['target_id']] = $detail;
            }
        }


        // Append Domain, Goal, Target Names & Extract Data
        foreach ($conflictedTargets as &$conflicted) {
            $targetId = $conflicted['data']['target_id'];
            if (isset($targetDetails[$targetId])) {
                $conflicted['data']['domain_name'] = $targetDetails[$targetId]['domain_name'];
                $conflicted['data']['goal_name'] = $targetDetails[$targetId]['goal_name'];
                $conflicted['data']['target_name'] = $targetDetails[$targetId]['target_name'];
            }

            $collectedData = json_decode($conflicted['data']['collected_data'], true);
            $conflicted['data']['phase_name'] = $collectedData['phase']['name'] ?? 'N/A';
            $conflicted['data']['result'] = implode(', ', $collectedData['result'] ?? []);
        }

        foreach ($deletedTargets as &$deleted) {
            $targetId = $deleted['data']['target_id'];
            if (isset($targetDetails[$targetId])) {
                $deleted['data']['domain_name'] = $targetDetails[$targetId]['domain_name'];
                $deleted['data']['goal_name'] = $targetDetails[$targetId]['goal_name'];
                $deleted['data']['target_name'] = $targetDetails[$targetId]['target_name'];
            }

            $collectedData = json_decode($deleted['data']['collected_data'], true);
            $deleted['data']['phase_name'] = $collectedData['phase']['name'] ?? 'N/A';
            $deleted['data']['result'] = implode(', ', $collectedData['result'] ?? []);
        }
        foreach ($processedTargets as &$processed) {
            $targetId = $processed['data']['target_id'];
            if (isset($targetDetails[$targetId])) {
                $processed['data']['domain_name'] = $targetDetails[$targetId]['domain_name'];
                $processed['data']['goal_name'] = $targetDetails[$targetId]['goal_name'];
                $processed['data']['target_name'] = $targetDetails[$targetId]['target_name'];
            }

            $collectedData = json_decode($processed['data']['collected_data'], true);
            $processed['data']['phase_name'] = $collectedData['phase']['name'] ?? 'N/A';
            $processed['data']['result'] = implode(', ', $collectedData['result'] ?? []);
        }



        // Render view
        $html = view('ClientSessionsReview/ProcessConfirmation/log', [
            'log' => $log,
            'sessionDetails' => $sessionDetails,
            'conflictedTargets' => $conflictedTargets,
            'deletedTargets' => $deletedTargets,
            'processedTargets' => $processedTargets
        ]);

        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    /********************************************************************************* */
    public function getTargetScreenForManuallyEntry($session_id)
    {

        // Get active session detail for client for given date, by therapist
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);

        $client = $this->clientModel->find($sessionDetail->client_id);
        // Get domain and goals for given client for session
        $program = $this->clientProgramModel->getClientProgramForLiveSession($sessionDetail->client_id);

        $this->page_title = 'Daily Sessions Target List';

        return  view(
            'ClientSessionsReview/ProgramReview/program_list',
            [
                'sessionDetail' => $sessionDetail,
                'client' =>  $client,
                'program' =>  $program,
                'page_title' => $this->page_title
            ]
        );
    }

    public function get_target_list_temp_to_delete_later_after_confirmation_of_below()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $session_id = $this->request->getPost('session_id');
        // $probe_set_id = $this->request->getPost('probe_set_id'); // Receive the selected probe set ID

        // Retrieve the active session for the client
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);

        $active_probe_set = $this->clientProgramModel->get_active_probe_set($client_id, $goal_id);

        $goal = $this->clientGoalModel->find($goal_id);
        $domain = $this->clientDomainModel->find($domain_id);
        $targets = [];

        if ($active_probe_set) {
            // Fetch the targets with associated probe set data 
            if ($active_probe_set->master_probe_set_id == 6) {
                $targets = $this->clientProgramModel->get_target_list_for_percentage_yes_no_probes($client_id, $domain_id, $goal_id, $active_probe_set->probe_set_id, $sessionDetail->session_date, $sessionDetail->id);
            } else {
                $targets = $this->clientProgramModel->get_target_list($client_id, $domain_id, $goal_id, $active_probe_set->probe_set_id, $sessionDetail->session_date);
            }

            foreach ($targets as &$target) {
                // Retrieve the current phase of the target
                $currentPhase = $target['probe_set']['combination']['current_phase_name']; // Use the phase fetched from the query

                $rules = $target['probe_set']['rules'];

                // Find the rule related to the current phase
                $currentPhaseRule = null;
                foreach ($rules as $rule) {
                    if ($rule['phase_name'] == $currentPhase) {
                        $currentPhaseRule = $rule['rule_data'];
                        break;
                    }
                }

                if ($currentPhaseRule) {
                    // Extract consecutive_criteria from either override or the rule
                    $consecutiveCriteria = $rule['consecutive_criteria'];
                    $sameDay = $currentPhaseRule['same_day_check'] ?? 0;

                    // Handle input rendering
                    if (isset($target['probe_set']['inputs']) && !empty($target['probe_set']['inputs'])) {
                        $target['input_html'] = $this->renderInputs($target['probe_set']['inputs'], $consecutiveCriteria, $sameDay, $target);
                    } else {
                        $target['input_html'] = '<p>No probe set inputs available.</p>';
                    }
                } else {
                    $target['input_html'] = '<p>No rules found for current phase.</p>';
                }

                // Attach client_probe_set_id and current_phase_id to each target
                $target['client_probe_set_id'] = $target['probe_set']['probe_set_id'];
                $target['current_phase_id'] = $target['probe_set']['combination']['current_phase_id'];

                $target['phase_name'] = $currentPhase;
                $target['additional_data'] = [
                    'rules' => $rules,
                    'current_rule' => $currentPhaseRule,
                ];
                if ($active_probe_set->master_probe_set_id == 6) {
                    $target['existingEntry'] = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target['target_id'], $active_probe_set->probe_set_id, $sessionDetail->id);
                }
            }
        }


        // Prepare the data for the view
        $data = [
            'active_probe_set' => $active_probe_set,
            'targets' => $targets,
            'goal' => $goal,
            'domain' => $domain,
            'client_id' => $client_id,
            'session_id' => $session_id,
            'session_date' => currentDate(),
        ];


        if ($active_probe_set->master_probe_set_id == 6) {
            return view('ClientSessionsReview/ProgramReview/target_list_percentage_probe_yes_no', $data);
        } else {
            return view('ClientSessionsReview/ProgramReview/target_list', $data);
        }
    }
    public function get_target_list()
    {
        $client_id   = $this->request->getPost('client_id');
        $domain_id   = $this->request->getPost('domain_id');
        $goal_id     = $this->request->getPost('goal_id');
        $session_id  = $this->request->getPost('session_id');

        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);

        $active_probe_set = $this->clientProgramModel->get_active_probe_set($client_id, $goal_id);
        if (!$active_probe_set) return 'This goal does not have an active probe set assigned. Please configure a probe set to proceed with data collection.';


        $probe_type = (int) $active_probe_set->master_probe_set_id;

        switch ($probe_type) {
            case 6:
                return $this->handlePercentageYesNoTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set);
            case 7:
                return $this->handleStimulusTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set);
            default:
                return $this->handleDefaultTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set);
        }
    }
    private function handleDefaultTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set)
    {

        $goal = $this->clientGoalModel->find($goal_id);
        $domain = $this->clientDomainModel->find($domain_id);
        $targets = $this->clientProgramModel->get_target_list($client_id, $domain_id, $goal_id, $active_probe_set->probe_set_id, $sessionDetail->session_date);

        foreach ($targets as &$target) {
            // Retrieve the current phase of the target
            $currentPhase = $target['probe_set']['combination']['current_phase_name']; // Use the phase fetched from the query

            $rules = $target['probe_set']['rules'];

            // Find the rule related to the current phase
            $currentPhaseRule = null;
            foreach ($rules as $rule) {
                if ($rule['phase_name'] == $currentPhase) {
                    $currentPhaseRule = $rule['rule_data'];
                    break;
                }
            }

            if ($currentPhaseRule) {
                // Extract consecutive_criteria from either override or the rule
                $consecutiveCriteria = $rule['consecutive_criteria'];
                $sameDay = $currentPhaseRule['same_day_check'] ?? 0;

                // Handle input rendering
                if (isset($target['probe_set']['inputs']) && !empty($target['probe_set']['inputs'])) {
                    $target['input_html'] = $this->renderInputs($target['probe_set']['inputs'], $consecutiveCriteria, $sameDay, $target);
                } else {
                    $target['input_html'] = '<p>No probe set inputs available.</p>';
                }
            } else {
                $target['input_html'] = '<p>No rules found for current phase.</p>';
            }

            // Attach client_probe_set_id and current_phase_id to each target
            $target['client_probe_set_id'] = $target['probe_set']['probe_set_id'];
            $target['current_phase_id'] = $target['probe_set']['combination']['current_phase_id'];

            $target['phase_name'] = $currentPhase;
            $target['additional_data'] = [
                'rules' => $rules,
                'current_rule' => $currentPhaseRule,
            ];
            if ($active_probe_set->master_probe_set_id == 6) {
                $target['existingEntry'] = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target['target_id'], $active_probe_set->probe_set_id, $sessionDetail->id);
            }
        }
        // Prepare the data for the view
        $data = [
            'active_probe_set' => $active_probe_set,
            'targets' => $targets,
            'goal' => $goal,
            'domain' => $domain,
            'client_id' => $client_id,
            'session_id' => $sessionDetail->id,
            'session_date' => currentDate(),
        ];
        return view('ClientSessionsReview/ProgramReview/target_list', $data);
    }
    private function handlePercentageYesNoTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set)
    {

        $goal = $this->clientGoalModel->find($goal_id);
        $domain = $this->clientDomainModel->find($domain_id);
        $targets = $this->clientProgramModel->get_target_list_for_percentage_yes_no_probes($client_id, $domain_id, $goal_id, $active_probe_set->probe_set_id, $sessionDetail->session_date, $sessionDetail->id);

        foreach ($targets as &$target) {
            // Retrieve the current phase of the target
            $currentPhase = $target['probe_set']['combination']['current_phase_name']; // Use the phase fetched from the query

            $rules = $target['probe_set']['rules'];

            // Find the rule related to the current phase
            $currentPhaseRule = null;
            foreach ($rules as $rule) {
                if ($rule['phase_name'] == $currentPhase) {
                    $currentPhaseRule = $rule['rule_data'];
                    break;
                }
            }

            if ($currentPhaseRule) {
                // Extract consecutive_criteria from either override or the rule
                $consecutiveCriteria = $rule['consecutive_criteria'];
                $sameDay = $currentPhaseRule['same_day_check'] ?? 0;

                // Handle input rendering
                if (isset($target['probe_set']['inputs']) && !empty($target['probe_set']['inputs'])) {
                    $target['input_html'] = $this->renderInputs($target['probe_set']['inputs'], $consecutiveCriteria, $sameDay, $target);
                } else {
                    $target['input_html'] = '<p>No probe set inputs available.</p>';
                }
            } else {
                $target['input_html'] = '<p>No rules found for current phase.</p>';
            }

            // Attach client_probe_set_id and current_phase_id to each target
            $target['client_probe_set_id'] = $target['probe_set']['probe_set_id'];
            $target['current_phase_id'] = $target['probe_set']['combination']['current_phase_id'];

            $target['phase_name'] = $currentPhase;
            $target['additional_data'] = [
                'rules' => $rules,
                'current_rule' => $currentPhaseRule,
            ];
            if ($active_probe_set->master_probe_set_id == 6) {
                $target['existingEntry'] = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target['target_id'], $active_probe_set->probe_set_id, $sessionDetail->id);
            }
        }
        // Prepare the data for the view
        $data = [
            'active_probe_set' => $active_probe_set,
            'targets' => $targets,
            'goal' => $goal,
            'domain' => $domain,
            'client_id' => $client_id,
            'session_id' => $sessionDetail->id,
            'session_date' => currentDate(),
        ];
        return view('ClientSessionsReview/ProgramReview/target_list_percentage_probe_yes_no', $data);
    }
    private function handleStimulusTargets($client_id, $domain_id, $goal_id, $sessionDetail, $active_probe_set)
    {
        $goal = $this->clientGoalModel->find($goal_id);
        $domain = $this->clientDomainModel->find($domain_id);
        $targets = $this->clientProgramModel->get_target_list_for_stimulus_probes($client_id, $domain_id, $goal_id, $active_probe_set->probe_set_id, $sessionDetail->session_date, $sessionDetail->id);
        $stepDataModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        foreach ($targets as &$target) {
            // Retrieve the current phase of the target
            $currentPhase = $target['probe_set']['combination']['current_phase_name']; // Use the phase fetched from the query

            $rules = $target['probe_set']['rules'];

            // Find the rule related to the current phase
            $currentPhaseRule = null;
            foreach ($rules as $rule) {
                if ($rule['phase_name'] == $currentPhase) {
                    $currentPhaseRule = $rule['rule_data'];
                    break;
                }
            }

            // Attach client_probe_set_id and current_phase_id to each target
            $target['client_probe_set_id'] = $target['probe_set']['probe_set_id'];
            $target['current_phase_id'] = $target['probe_set']['combination']['current_phase_id'];

            $target['phase_name'] = $currentPhase;
            $target['additional_data'] = [
                'rules' => $rules,
                'current_rule' => $currentPhaseRule,
            ];


            $stepInputs = $stepDataModel->where([
                'client_id' => $client_id,
                'session_id' => $sessionDetail->id,
                'target_id' => $target['target_id'],
            ])->findAll();

            // Organize for fast lookup
            $prefillInputs = [];
            foreach ($stepInputs as $row) {
                $phase = $row['phase_id'];
                $method = $row['method'];
                $step = $row['step_id'];
                $attempt = (int) $row['attempt_no'];

                $prefillInputs[$phase][$method][$step][$attempt] = $row['input_result'];
            }

            $target['prefill_step_inputs'] = $prefillInputs;
        }
        // Prepare the data for the view
        $data = [
            'active_probe_set' => $active_probe_set,
            'targets' => $targets,
            'goal' => $goal,
            'domain' => $domain,
            'client_id' => $client_id,
            'session_id' => $sessionDetail->id,
            'session_date' =>  $sessionDetail->session_date,
        ];
        return view('ClientSessionsReview/ProgramReview/StimulusProgram/target_list_stimulus_probe', $data);
    }
    // Probe Set Input Rendering
    private function renderInputs($inputs, $consecutiveCriteria, $sameDay, $target)
    {
        $html = '';
        for ($i = 0; $i < ($sameDay ? $consecutiveCriteria : 1); $i++) {
            switch ($inputs['type']) {
                case 'yes_no':
                    $html .= view('ClientSessionsLive/input_yes_no', [
                        'choices' => $inputs['choices'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                case 'count':
                    $html .= view('ClientSessionsLive/input_count', [
                        'range' => $inputs['range'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                case 'traffic_light':
                    $html .= view('ClientSessionsLive/input_traffic_light', [
                        'choices' => $inputs['choices'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                case 'prompt_level':
                    $html .= view('ClientSessionsLive/input_prompt_level', [
                        'choices' => $inputs['choices'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                case 'duration':
                    $html .= view('ClientSessionsLive/input_duration', [
                        'choices' => $inputs['choices'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                case 'percentage_yes_no':
                    $html .= view('ClientSessionsLive/input_percentage_yes_no', [
                        'choices' => $inputs['choices'],
                        'index' => $i,
                        'target' => $target
                    ]);
                    break;
                default:
                    $html .= '<p>Unknown Input Format</p>';
            }
        }
        return $html;
    }


    public function save_session_target()
    {
        // Get Request Data
        $client_id = $this->request->getPost('client_id');
        $target_id = $this->request->getPost('target_id');
        $goal_id = $this->request->getPost('goal_id');
        $domain_id = $this->request->getPost('domain_id');
        $session_id = $this->request->getPost('session_id');
        $current_phase_id = $this->request->getPost('current_phase_id');
        $client_probe_set_id = $this->request->getPost('client_probe_set_id');


        $probeData = json_decode($this->request->getPost('radio_data'), true);

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }

        $result = [];
        // Aggregate all results into a single array
        foreach ($probeData as $data) {
            $result[] = $data['result'];
        }

        // Need to prepare JSON data for saving in the `collection` table for each probe set. The collected data should include the following fields:
        $collected_data = $this->prepareCollectedDataJson($result, $client_id, $target_id, $client_probe_set_id, $current_phase_id, $sessionDate);

        $rowData = [
            'session_id' => $session_id,
            'session_date' => $sessionDate,
            'client_id' => $client_id,
            'domain_id' => $domain_id,
            'goal_id' => $goal_id,
            'target_id' => $target_id,
            'client_probe_set_id' => $client_probe_set_id,
            'current_phase_id' => $current_phase_id,
            'collected_data' => $collected_data, // JSON data prepared in controller
            'is_processed' => 0,
            'is_default' => 0,
            'created_at' => currentDate('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
        ];

        // Save Probe Data using the model method, passing session date along
        if ($this->dailySessionDataCollectionModel->checkForDuplicateEntry($client_id, $target_id, $client_probe_set_id, $sessionDate)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Duplication: Target data already collected for today sessions.',
            ]);
        } else {
            $this->dailySessionDataCollectionModel->insert($rowData);
            // Check if session is fully processed
            $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($rowData['session_id']);

            // Update session status
            $updatedSessionData = [
                'status' => $sessionProcessingStatus['status_code'],
                'note' => $sessionProcessingStatus['status_name']
            ];
            // Save session update
            $this->dailySessionModel->update($rowData['session_id'], $updatedSessionData);

            // Response to client
            return $this->response->setJSON([
                'success' => 'Yes',
                'message' => 'Data saved successfully.',
            ]);
        }
    }

    private function prepareCollectedDataJson($result, $client_id, $target_id, $client_probe_set_id, $current_phase_id, $session_date)
    {
        // Fetch additional details for the probe set (e.g., inputs, combination, rules)
        $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);

        // Fetch last processed data if exists
        $processedData = $this->processModel->getTargetLastProcessedDataByDate($client_id, $target_id, $client_probe_set_id, $session_date);


        // Determine frame set number based on processed data or defaults
        $frameSetNo = $this->getFrameSetNo($processedData, $probeSetDetails);

        // Prepare the JSON structure
        $collectedData = [
            'inputs' => json_decode($probeSetDetails['inputs'], true),
            'result' => $result,
            'probe_set_id' => $client_probe_set_id,
            'combination' => [
                'id' => $probeSetDetails['combination_id'],
                'name' => $probeSetDetails['combination_name'],
            ],
            'phase' => [
                'id' => $current_phase_id,
                'name' => $probeSetDetails['phase_name'],
            ],
            'rule' => [
                'default_rule' => $probeSetDetails['rule_data'],
                'frame_set_no' => $frameSetNo,
            ],
        ];

        return json_encode($collectedData);
    }

    private function getFrameSetNo(?array $processedData, array $probeSetDetails)
    {
        // Determine the frame set number based on the processed data or defaults
        $rule_data = json_decode($probeSetDetails['rule_data'], true);

        if (isset($rule_data['frame_check']) && $rule_data['frame_check']) {
            if ($processedData) {
                $processed_detail = json_decode($processedData['processed_detail'], true);
                return $processed_detail['next_frame_set_no'];
            } else {
                return 1; // Default starting frame set
            }
        }
        return null;
    }

    /***************************************************************** */
    public function save_percentage_session_target()
    {
        // Get Request Data
        $client_id = $this->request->getPost('client_id');
        $target_id = $this->request->getPost('target_id');
        $goal_id = $this->request->getPost('goal_id');
        $domain_id = $this->request->getPost('domain_id');
        $session_id = $this->request->getPost('session_id');
        $current_phase_id = $this->request->getPost('current_phase_id');
        $client_probe_set_id = $this->request->getPost('client_probe_set_id');


        $probeData = json_decode($this->request->getPost('radio_data'), true);
        $transition = $this->request->getPost('input_transition');

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }




        // Check if input transition exist

        /*if ($transition == '') {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Trail data required.',
            ]);
        }*/


        $answer = strtoupper($probeData[0]['result']);

        $existingEntry = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $sessionDetail->id);

        if ($existingEntry) {
            // Entry exists — update
            $updatedCollectedData = $this->updateCollectedDataJsonWithNewEntry(
                $existingEntry->collected_data,
                $transition,
                $answer // assuming 1 result per save
            );

            $this->dailySessionDataCollectionModel->update($existingEntry->id, [
                'collected_data' => $updatedCollectedData,
                'updated_at' => currentDate('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id,
            ]);

            return $this->response->setJSON([
                'success' => 'Yes',
                'message' => 'Data updated successfully.',
            ]);
        } else {
            // Need to prepare JSON data for saving in the `collection` table for each probe set. The collected data should include the following fields:
            $collected_data = $this->prepareCollectedDataJsonForFirstEntry($transition, $answer, $client_probe_set_id, $current_phase_id);

            $rowData = [
                'session_id' => $session_id,
                'session_date' => $sessionDate,
                'client_id' => $client_id,
                'domain_id' => $domain_id,
                'goal_id' => $goal_id,
                'target_id' => $target_id,
                'client_probe_set_id' => $client_probe_set_id,
                'current_phase_id' => $current_phase_id,
                'collected_data' => $collected_data, // JSON data prepared in controller
                'is_processed' => 0,
                'created_at' => currentDate('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id,
            ];

            // No existing entry — insert new one
            $this->dailySessionDataCollectionModel->insert($rowData);

            // Check if session is fully processed
            $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($rowData['session_id']);

            // Update session status
            $updatedSessionData = [
                'status' => $sessionProcessingStatus['status_code'],
                'note' => $sessionProcessingStatus['status_name']
            ];
            // Save session update
            $this->dailySessionModel->update($rowData['session_id'], $updatedSessionData);

            return $this->response->setJSON([
                'success' => 'Yes',
                'message' => 'Data saved successfully.',
            ]);
        }
    }

    private function prepareCollectedDataJsonForFirstEntry(string $transition, string $answer, int $client_probe_set_id, int $current_phase_id): string
    {
        // Fetch probe set details
        $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);

        // Initialize stats
        $totalYes = strtoupper($answer) == 'Y' ? 1 : 0;
        $totalNo  = strtoupper($answer) == 'N' ? 1 : 0;
        $percentage = $totalYes + $totalNo > 0 ? round(($totalYes / ($totalYes + $totalNo)) * 100, 2) : 0;

        // Build data object
        $collectedData = [
            'inputs' => json_decode($probeSetDetails['inputs'], true),
            'probe_set_id' => $client_probe_set_id,
            'combination' => [
                'id' => $probeSetDetails['combination_id'],
                'name' => $probeSetDetails['combination_name'],
            ],
            'phase' => [
                'id' => $current_phase_id,
                'name' => $probeSetDetails['phase_name'],
            ],
            'rule' => [
                'default_rule' => $probeSetDetails['rule_data'],
                'frame_set_no' => null,
            ],
            'result' => [$percentage],
            'statistics' => [
                'total_yes' => $totalYes,
                'total_no' => $totalNo,
                'percentage' => $percentage,
            ],
            'transitions' => [
                [
                    'transition' => $transition,
                    'answer' => strtoupper($answer),
                ],
            ],
        ];

        return json_encode($collectedData);
    }

    private function updateCollectedDataJsonWithNewEntry(string $existingJson, string $transition, string $answer): string
    {
        $data = json_decode($existingJson, true);

        // Add new transition
        $data['transitions'][] = [
            'transition' => $transition,
            'answer' => strtoupper($answer),
        ];

        // Recalculate statistics
        $totalYes = 0;
        $totalNo  = 0;

        foreach ($data['transitions'] as $entry) {
            if (strtoupper($entry['answer']) == 'Y') {
                $totalYes++;
            } elseif (strtoupper($entry['answer']) == 'N') {
                $totalNo++;
            }
        }

        $totalCount = $totalYes + $totalNo;
        $percentage = $totalCount > 0 ? round(($totalYes / $totalCount) * 100, 2) : 0;

        // Update stats and result
        $data['statistics'] = [
            'total_yes' => $totalYes,
            'total_no' => $totalNo,
            'percentage' => $percentage,
        ];
        $data['result'] = [$percentage];

        return json_encode($data);
    }

    /***************************************************************************************************** */
    /** Stimulus Probes Management */
    /***************************************************************************************************** */
    /** Baseline Chain */
    public function saveStimulusBaselineAttempt()
    {
        $client_id             = $this->request->getPost('client_id');
        $target_id             = $this->request->getPost('target_id');
        $goal_id               = $this->request->getPost('goal_id');
        $domain_id             = $this->request->getPost('domain_id');
        $session_id            = $this->request->getPost('session_id');
        $current_phase_id      = $this->request->getPost('current_phase_id');
        $client_probe_set_id   = $this->request->getPost('client_probe_set_id');
        $attempt_no            = (int) $this->request->getPost('attempt_no');
        $step_data             = $this->request->getPost('step_data');
        $method                = $this->request->getPost('method'); // 'baseline'



        // ✅ 1. Validate session + timers
        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }

        // ✅ 2. Ensure collection row exists or create

        $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);

        if (!$collection) {

            // Fetch probe set details
            $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);


            $collected_data = [
                'inputs' => json_decode($probeSetDetails['inputs'], true),
                'probe_set_id' => $client_probe_set_id,
                'combination' => [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ],
                'phase' => [
                    'id' => $current_phase_id,
                    'name' => $probeSetDetails['phase_name'],
                ],
                'rule' => [
                    'default_rule' => $probeSetDetails['rule_data'],
                    'frame_set_no' => null,
                ],
                'method'       => $method,
                'step_value'   => null,
                'statistics'   => [],
                'result'       => [],
            ];
            $this->dailySessionDataCollectionModel->insert([
                'session_id'         => $session_id,
                'session_date'       => $sessionDate,
                'client_id'          => $client_id,
                'domain_id'          => $domain_id,
                'goal_id'            => $goal_id,
                'target_id'          => $target_id,
                'client_probe_set_id' => $client_probe_set_id,
                'current_phase_id'   => $current_phase_id,
                'collected_data'     => json_encode($collected_data),
                'is_processed'       => 0,
                'created_by'         => auth()->user()->id,
            ]);
            $collection =  $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        }

        $collection_id = $collection->id;

        // ✅ 3. Insert/Update step-level data
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        foreach ($step_data as $entry) {
            $step_id = $entry['step_id'];
            $raw = $entry['input_result'];
            $result = $raw != null && $raw != '' ? strtoupper(trim($raw)) : '';

            $existingStep = $stepSessionModel->where([
                'client_id'   => $client_id,
                'target_id'   => $target_id,
                'step_id'     => $step_id,
                'session_id'  => $session_id,
                'attempt_no'  => $attempt_no,
                'phase_id'    => 1,
                'method'      => 'baseline'
            ])->first();

            $data = [
                'collection_id'        => $collection_id,
                'client_id'            => $client_id,
                'target_id'            => $target_id,
                'step_id'              => $step_id,
                'session_id'           => $session_id,
                'session_date'         => $sessionDate,
                'phase_id'             => 1,
                'method'               => 'baseline',
                'attempt_no'           => $attempt_no,
                'input_result'         => $result,
                'is_mastered_snapshot' => 0,
                'created_by'           => auth()->user()->id,
            ];

            if ($existingStep) {
                $data['updated_by'] = auth()->user()->id;
                $data['updated_at'] = currentDate('Y-m-d H:i:s');
                $stepSessionModel->update($existingStep['id'], $data);
            } else {
                $stepSessionModel->insert($data);
            }
        }

        // ✅ 4. Process baseline summary and update collected_data
        $baselineSummary = $this->compileBaselineSummary($client_id, $target_id, $session_id, $sessionDate, $collection_id);

        $updatedCollected = json_decode($collection->collected_data, true);
        $updatedCollected['statistics'] = $baselineSummary['statistics'];
        $updatedCollected['result']     = $baselineSummary['result'];

        $this->dailySessionDataCollectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at'     => currentDate('Y-m-d H:i:s'),
            'updated_by'     => auth()->user()->id,
        ]);

        // Check if session is fully processed
        $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($session_id);

        // Update session status
        $updatedSessionData = [
            'status' => $sessionProcessingStatus['status_code'],
            'note' => $sessionProcessingStatus['status_name']
        ];
        // Save session update
        $this->dailySessionModel->update($session_id, $updatedSessionData);

        return $this->respond([
            'success' => 'Yes',
            'message' => 'Baseline data saved.',
            'updated_result' => $baselineSummary
        ]);
    }
    private function compileBaselineSummary($client_id, $target_id, $session_id, $sessionDate, $collection_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();

        $all = $stepSessionModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'session_id' => $session_id,
            'phase_id'  => 1,
            'method'    => 'baseline'
        ])->findAll();

        // Step-wise attempt grouping
        $stepAttempts = [];

        // Default empty stats for 3 attempts
        $statsByAttempt = [];
        for ($i = 0; $i < 3; $i++) {
            $statsByAttempt[$i] = ['IND' => 0, 'NR' => 0, 'IR' => 0, 'OTHER' => 0, 'total' => 0];
        }

        // Accumulate actual values
        foreach ($all as $entry) {
            $step_id = $entry['step_id'];
            $attempt = (int) $entry['attempt_no'];
            $result  = strtoupper(trim($entry['input_result'] ?? ''));

            // Group results by step
            if (!isset($stepAttempts[$step_id])) {
                $stepAttempts[$step_id] = [];
            }
            $stepAttempts[$step_id][] = $result;

            // Count for attempt-level summary
            if (in_array($attempt, [0, 1, 2])) {
                $statsByAttempt[$attempt]['total']++;
                switch ($result) {
                    case 'IND':
                        $statsByAttempt[$attempt]['IND']++;
                        break;
                    case 'NR':
                        $statsByAttempt[$attempt]['NR']++;
                        break;
                    case 'IR':
                        $statsByAttempt[$attempt]['IR']++;
                        break;
                    default:
                        $statsByAttempt[$attempt]['OTHER']++;
                }
            }
        }

        // Delete any existing mastery records for this collection
        $masteryModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('method', 'baseline')
            ->where('collection_id', $collection_id)
            ->delete();

        // Determine newly mastered steps
        $masteredSteps = [];
        foreach ($stepAttempts as $step_id => $results) {
            if (count($results) === 3 && count(array_filter($results, fn($r) => $r === 'IND')) === 3) {
                $masteredSteps[] = $step_id;
            }
        }

        // Insert new mastery records if any
        foreach ($masteredSteps as $step_id) {
            $masteryModel->insert([
                'client_id'     => $client_id,
                'target_id'     => $target_id,
                'step_id'       => $step_id,
                'method'        => 'baseline',
                'collection_id' => $collection_id,
                'session_id'    => $session_id,
                'session_date'  => $sessionDate,
                'created_by'    => auth()->user()->id,
            ]);
        }
        // Build structured result with defaults
        $statistics = [];
        $resultPercentages = [];

        for ($a = 0; $a < 3; $a++) {
            $stats = $statsByAttempt[$a];
            $ind = $stats['IND'];
            $total = $stats['total'];
            $percentage = $total > 0 ? round(($ind / $total) * 100, 2) : 0;

            $statistics["attempt_$a"] = [
                'total_steps'  => $total,
                'total_IND'    => $stats['IND'],
                'total_IR'     => $stats['IR'],
                'total_NR'     => $stats['NR'],
                'other_count'  => $stats['OTHER'],
                'percentage'   => $percentage,
            ];

            $resultPercentages[] = $percentage;
        }

        return [
            'statistics' => $statistics,
            'result'     => $resultPercentages
        ];
    }
    /** Total Task Chain */
    public function saveStimulusTotalTaskAttempt()
    {
        $client_id           = $this->request->getPost('client_id');
        $target_id           = $this->request->getPost('target_id');
        $goal_id             = $this->request->getPost('goal_id');
        $domain_id           = $this->request->getPost('domain_id');
        $session_id          = $this->request->getPost('session_id');
        $current_phase_id    = $this->request->getPost('current_phase_id');
        $client_probe_set_id = $this->request->getPost('client_probe_set_id');
        $step_data           = $this->request->getPost('step_data');
        $method              = $this->request->getPost('method'); // total_task

        // ✅ 1. Validate session + timers
        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;
        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }

        $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        if (!$collection) {
            $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);
            $collected_data = [
                'inputs' => json_decode($probeSetDetails['inputs'], true),
                'probe_set_id' => $client_probe_set_id,
                'combination' => [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ],
                'phase' => [
                    'id' => $current_phase_id,
                    'name' => $probeSetDetails['phase_name'],
                ],
                'rule' => [
                    'default_rule' => $probeSetDetails['rule_data'],
                    'frame_set_no' => null,
                ],
                'method' => $method,
                'statistics' => [],
                'result' => []
            ];
            $this->dailySessionDataCollectionModel->insert([
                'session_id' => $session_id,
                'session_date' => $sessionDate,
                'client_id' => $client_id,
                'domain_id' => $domain_id,
                'goal_id' => $goal_id,
                'target_id' => $target_id,
                'client_probe_set_id' => $client_probe_set_id,
                'current_phase_id' => $current_phase_id,
                'collected_data' => json_encode($collected_data),
                'is_processed' => 0,
                'created_by' => auth()->user()->id,
            ]);
            $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        }

        $collection_id = $collection->id;

        // Save step-level data
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        foreach ($step_data as $entry) {
            $step_id = $entry['step_id'];
            $raw = $entry['input_result'];
            $result = $raw !== null && $raw !== '' ? strtoupper(trim($raw)) : '';

            $existing = $stepSessionModel->where([
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id'   => $step_id,
                'session_id' => $session_id,
                'phase_id'  => $current_phase_id,
                'method'    => 'total_task',
            ])->first();

            $data = [
                'collection_id' => $collection_id,
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id' => $step_id,
                'session_id' => $session_id,
                'session_date' => $sessionDate,
                'phase_id' => $current_phase_id,
                'method' => 'total_task',
                'attempt_no' => 0,
                'input_result' => $result,
                'is_mastered_snapshot' => 0,
                'created_by' => auth()->user()->id,
            ];

            if ($existing) {
                $data['updated_by'] = auth()->user()->id;
                $data['updated_at'] = currentDate('Y-m-d H:i:s');
                $stepSessionModel->update($existing['id'], $data);
            } else {
                $stepSessionModel->insert($data);
            }
        }

        // Process summary
        $summary = $this->compileTotalTaskSummary($client_id, $target_id, $session_id, $current_phase_id);

        $updatedCollected = json_decode($collection->collected_data, true);
        $updatedCollected['statistics'] = $summary['statistics'];
        $updatedCollected['result'] = [$summary['result']];

        $this->dailySessionDataCollectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

        // Check if session is fully processed
        $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($session_id);

        // Update session status
        $updatedSessionData = [
            'status' => $sessionProcessingStatus['status_code'],
            'note' => $sessionProcessingStatus['status_name']
        ];
        // Save session update
        $this->dailySessionModel->update($session_id, $updatedSessionData);

        return $this->respond([
            'success' => 'Yes',
            'message' => 'Total Task data saved.',
            'updated_result' => $summary
        ]);
    }

    private function compileTotalTaskSummary($client_id, $target_id, $session_id, $phase_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $records = $stepSessionModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'session_id' => $session_id,
            'phase_id' => $phase_id,
            'method' => 'total_task'
        ])->findAll();

        $indCount = 0;
        $fpCount = 0;
        $ppCount = 0;
        $otherCount = 0;
        $totalSteps = 0;

        foreach ($records as $row) {
            $totalSteps++;
            $raw = $row['input_result'];
            $val = ($raw != null && $raw != '') ? strtoupper(trim($raw)) : '';
            if ($val == 'IND') $indCount++;
            if ($val == 'FP') $fpCount++;
            if ($val == 'PP') $ppCount++;
            if ($val != 'IND' && $val != 'FP' && $val != 'PP') $otherCount++;
        }
        $percentage = $totalSteps > 0 ? round(($indCount / $totalSteps) * 100, 2) : 0;
        return [
            'statistics' => [
                'total_steps' => $totalSteps,
                'total_IND'   => $indCount,
                'total_FP'    => $fpCount,
                'total_PP'    => $ppCount,
                'other_count' => $otherCount,
            ],
            'result' => $percentage
        ];
    }

    /** Forward Chain */
    public function saveStimulusForwardAttempt()
    {
        $client_id           = $this->request->getPost('client_id');
        $target_id           = $this->request->getPost('target_id');
        $goal_id             = $this->request->getPost('goal_id');
        $domain_id           = $this->request->getPost('domain_id');
        $session_id          = $this->request->getPost('session_id');
        $current_phase_id    = $this->request->getPost('current_phase_id');
        $client_probe_set_id = $this->request->getPost('client_probe_set_id');
        $step_data           = $this->request->getPost('step_data');
        $method              = $this->request->getPost('method'); // 'forward'

        // ✅ 1. Validate session + timers
        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }
        // ✅ 2. Ensure collection row exists or create
        $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        if (!$collection) {
            $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);
            $collected_data = [
                'inputs' => json_decode($probeSetDetails['inputs'], true),
                'probe_set_id' => $client_probe_set_id,
                'combination' => [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ],
                'phase' => [
                    'id' => $current_phase_id,
                    'name' => $probeSetDetails['phase_name'],
                ],
                'rule' => [
                    'default_rule' => $probeSetDetails['rule_data'],
                    'frame_set_no' => null,
                ],
                'method' => $method,
                'statistics' => [],
                'result' => []
            ];
            $this->dailySessionDataCollectionModel->insert([
                'session_id' => $session_id,
                'session_date' => $sessionDate,
                'client_id' => $client_id,
                'domain_id' => $domain_id,
                'goal_id' => $goal_id,
                'target_id' => $target_id,
                'client_probe_set_id' => $client_probe_set_id,
                'current_phase_id' => $current_phase_id,
                'collected_data' => json_encode($collected_data),
                'is_processed' => 0,
                'created_by' => auth()->user()->id,
            ]);
            $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        }

        $collection_id = $collection->id;

        // ✅ 3. Save input for one step only (forward collects one step per session)
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();

        $entry = $step_data[0]; // Only one expected
        $step_id = $entry['step_id'];
        $raw = $entry['input_result'];
        $result = $raw !== null && $raw !== '' ? strtoupper(trim($raw)) : '';

        $existing = $stepSessionModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'session_id' => $session_id,
            'phase_id'  => $current_phase_id,
            'method'    => 'forward',
        ])->first();

        $data = [
            'collection_id' => $collection_id,
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id' => $step_id,
            'session_id' => $session_id,
            'session_date' => $sessionDate,
            'phase_id' => $current_phase_id,
            'method' => 'forward',
            'attempt_no' => 0,
            'input_result' => $result,
            'is_mastered_snapshot' => 0,
            'created_by' => auth()->user()->id,
        ];

        if ($existing) {
            $data['updated_by'] = auth()->user()->id;
            $data['updated_at'] = currentDate('Y-m-d H:i:s');
            $stepSessionModel->update($existing['id'], $data);
        } else {
            $stepSessionModel->insert($data);
        }

        // ✅ 4. Summary
        $summary = $this->compileForwardChainingSummary($client_id, $target_id, $client_probe_set_id, $current_phase_id, $step_id, $result, $session_id, $sessionDate, $collection_id);

        $updatedCollected = json_decode($collection->collected_data, true);
        $updatedCollected['statistics'] = $summary['statistics'];
        $updatedCollected['result'] = [$summary['result']];

        $this->dailySessionDataCollectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

        // Check if session is fully processed
        $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($session_id);

        // Update session status
        $updatedSessionData = [
            'status' => $sessionProcessingStatus['status_code'],
            'note' => $sessionProcessingStatus['status_name']
        ];
        // Save session update
        $this->dailySessionModel->update($session_id, $updatedSessionData);

        return $this->respond([
            'success' => 'Yes',
            'message' => 'Forward chaining data saved.',
            'updated_result' => $summary
        ]);
    }

    private function compileForwardChainingSummary($client_id, $target_id, $client_probe_set_id, $current_phase_id, $step_id, $probe_value, $session_id, $sessionDate, $collection_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();
        $chainModel       = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $targetStepModel = new \App\Models\ClientProgram\ClientStimulusStepModel();


        // ✅ Step 2: Get chaining rule to determine required consecutive INDs
        $chainRow = $chainModel->where('target_id', $target_id)->first();
        $requiredConsecutive = 3; // default fallback

        if ($chainRow && !empty($chainRow->rule_override)) {
            $ruleOverride = json_decode($chainRow->rule_override, true);
            if (isset($ruleOverride['forward']['step_mastery']['value'])) {
                $requiredConsecutive = (int) $ruleOverride['forward']['step_mastery']['value'];
            }
        }

        $collectionModel = new \App\Models\ClientSessions\DailySessionDataCollectionModel();

        // Get all processed collection IDs up to the current session date
        $processedCollectionIds = $collectionModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('session_date <=', $sessionDate) // ⬅️ Prevent future session data
            ->where('is_processed', 1)
            ->select('id')
            ->findColumn('id');

        // Always include current collection ID (unprocessed, just collected)
        $currentCollectionId = $collection_id;
        $validCollectionIds = array_merge($processedCollectionIds, [$currentCollectionId]);

        // Fetch step attempts for valid collections only
        $allAttempts = $stepSessionModel
            ->where([
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id'   => $step_id,
                'method'    => 'forward',
                'phase_id'  => $current_phase_id,
            ])
            ->whereIn('collection_id', $validCollectionIds)
            ->orderBy('session_date', 'desc')
            ->findAll();

        $consecutiveInd = 0;
        foreach ($allAttempts as $entry) {
            if ($entry['input_result'] === 'IND') {
                $consecutiveInd++;
            } else {
                break; // Stop at first non-IND
            }
        }

        // ✅ Step 5: Check mastery and record it if not already marked
        $isMastered = false;
        $already = $masteryModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'method'    => 'forward',
        ])->first();

        if ($already) {
            $masteryModel->delete($already->id);
        }

        if ($consecutiveInd >= $requiredConsecutive) {
            $isMastered = true;
            $masteryModel->insert([
                'client_id'   => $client_id,
                'target_id'   => $target_id,
                'step_id'     => $step_id,
                'method'    => 'forward',
                'collection_id'     => $collection_id,
                'session_id'     => $session_id,
                'session_date'     => $sessionDate,
                'mastered_on' => currentDate('Y-m-d'),
                'created_by'  => auth()->user()->id
            ]);
        }

        // ✅ Step 6: Overall forward-chain percentage for this target.
        // Denominator: all configured step IDs for the target.
        // Numerator: distinct mastered step IDs from baseline + forward.
        $targetStepIds = $targetStepModel
            ->where('target_id', $target_id)
            ->select('id')
            ->findColumn('id');
        $targetStepIds = array_map('intval', $targetStepIds ?? []);
        $totalSteps = count($targetStepIds);

        $masteredCount = 0;
        if ($totalSteps > 0) {
            $masteredStepIds = $masteryModel
                ->distinct()
                ->select('step_id')
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->whereIn('method', ['baseline', 'forward'])
                ->whereIn('step_id', $targetStepIds)
                ->findColumn('step_id');

            $masteredCount = count($masteredStepIds ?? []);
        }

        $percentage = $totalSteps > 0 ? round(($masteredCount / $totalSteps) * 100, 2) : 0;

        return [
            'statistics' => [
                'step_id'         => $step_id,
                'probe_value'     => $probe_value,
                'total_attempts'  => count($allAttempts),
                'required_ind'    => $requiredConsecutive,
                'consecutive_ind' => $consecutiveInd,
                'is_mastered'     => $isMastered,
                'total_steps'     => $totalSteps,
                'mastered_steps'  => $masteredCount,
                'method'    => 'forward',
            ],
            'result' => $percentage
        ];
    }

    /** Backward Chain */
    public function saveStimulusBackwardAttempt()
    {
        $client_id           = $this->request->getPost('client_id');
        $target_id           = $this->request->getPost('target_id');
        $goal_id             = $this->request->getPost('goal_id');
        $domain_id           = $this->request->getPost('domain_id');
        $session_id          = $this->request->getPost('session_id');
        $current_phase_id    = $this->request->getPost('current_phase_id');
        $client_probe_set_id = $this->request->getPost('client_probe_set_id');
        $step_data           = $this->request->getPost('step_data');
        $method              = $this->request->getPost('method'); // 'forward'

        // ✅ 1. Validate session + timers
        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session data not found.',
            ]);
        }
        $sessionDate = $sessionDetail->session_date;

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            $processedCount = $this->dailySessionDataCollectionModel
                ->where('session_date > ', $sessionDate) // Upward sessions
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->where('client_probe_set_id', $client_probe_set_id)
                ->where('is_processed', 1) // Only processed sessions
                ->countAllResults(); // Get count only

            // Check if processed count exceeds allowed limit
            if ($processedCount > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in past while there are more than ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' processed data entries for the given target.',
                ]);
            }
        }
        // ✅ 2. Ensure collection row exists or create
        $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        if (!$collection) {
            $probeSetDetails = $this->clientProbeSetModel->getProbeSetDetails($client_probe_set_id, $current_phase_id);
            $collected_data = [
                'inputs' => json_decode($probeSetDetails['inputs'], true),
                'probe_set_id' => $client_probe_set_id,
                'combination' => [
                    'id' => $probeSetDetails['combination_id'],
                    'name' => $probeSetDetails['combination_name'],
                ],
                'phase' => [
                    'id' => $current_phase_id,
                    'name' => $probeSetDetails['phase_name'],
                ],
                'rule' => [
                    'default_rule' => $probeSetDetails['rule_data'],
                    'frame_set_no' => null,
                ],
                'method' => $method,
                'statistics' => [],
                'result' => []
            ];
            $this->dailySessionDataCollectionModel->insert([
                'session_id' => $session_id,
                'session_date' => $sessionDate,
                'client_id' => $client_id,
                'domain_id' => $domain_id,
                'goal_id' => $goal_id,
                'target_id' => $target_id,
                'client_probe_set_id' => $client_probe_set_id,
                'current_phase_id' => $current_phase_id,
                'collected_data' => json_encode($collected_data),
                'is_processed' => 0,
                'created_by' => auth()->user()->id,
            ]);
            $collection = $this->dailySessionDataCollectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
        }

        $collection_id = $collection->id;

        // ✅ 3. Save input for one step only (forward collects one step per session)
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();

        $entry = $step_data[0]; // Only one expected
        $step_id = $entry['step_id'];
        $raw = $entry['input_result'];
        $result = $raw !== null && $raw !== '' ? strtoupper(trim($raw)) : '';

        $existing = $stepSessionModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'session_id' => $session_id,
            'phase_id'  => $current_phase_id,
            'method'    => 'backward',
        ])->first();

        $data = [
            'collection_id' => $collection_id,
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id' => $step_id,
            'session_id' => $session_id,
            'session_date' => $sessionDate,
            'phase_id' => $current_phase_id,
            'method' => 'backward',
            'attempt_no' => 0,
            'input_result' => $result,
            'is_mastered_snapshot' => 0,
            'created_by' => auth()->user()->id,
        ];

        if ($existing) {
            $data['updated_by'] = auth()->user()->id;
            $data['updated_at'] = currentDate('Y-m-d H:i:s');
            $stepSessionModel->update($existing['id'], $data);
        } else {
            $stepSessionModel->insert($data);
        }

        // ✅ 4. Summary
        $summary = $this->compileBackwardChainingSummary($client_id, $target_id, $client_probe_set_id, $current_phase_id, $step_id, $result, $session_id, $sessionDate, $collection_id);

        $updatedCollected = json_decode($collection->collected_data, true);
        $updatedCollected['statistics'] = $summary['statistics'];
        $updatedCollected['result'] = [$summary['result']];

        $this->dailySessionDataCollectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

        // Check if session is fully processed
        $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($session_id);

        // Update session status
        $updatedSessionData = [
            'status' => $sessionProcessingStatus['status_code'],
            'note' => $sessionProcessingStatus['status_name']
        ];
        // Save session update
        $this->dailySessionModel->update($session_id, $updatedSessionData);

        return $this->respond([
            'success' => 'Yes',
            'message' => 'Backward chaining data saved.',
            'updated_result' => $summary
        ]);
    }

    private function compileBackwardChainingSummary($client_id, $target_id, $client_probe_set_id, $current_phase_id, $step_id, $probe_value, $session_id, $sessionDate, $collection_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();
        $chainModel       = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $targetStepModel = new \App\Models\ClientProgram\ClientStimulusStepModel();



        // ✅ Step 2: Get chaining rule to determine required consecutive INDs
        $chainRow = $chainModel->where('target_id', $target_id)->first();
        $requiredConsecutive = 3; // default fallback

        if ($chainRow && !empty($chainRow->rule_override)) {
            $ruleOverride = json_decode($chainRow->rule_override, true);
            if (isset($ruleOverride['backward']['step_mastery']['value'])) {
                $requiredConsecutive = (int) $ruleOverride['backward']['step_mastery']['value'];
            }
        }

        $collectionModel = new \App\Models\ClientSessions\DailySessionDataCollectionModel();

        // Get all processed collection IDs up to the current session date
        $processedCollectionIds = $collectionModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('session_date <=', $sessionDate) // ⬅️ Prevent future session data
            ->where('is_processed', 1)
            ->select('id')
            ->findColumn('id');

        // Always include current collection ID (unprocessed, just collected)
        $currentCollectionId = $collection_id;
        $validCollectionIds = array_merge($processedCollectionIds, [$currentCollectionId]);

        // Fetch step attempts for valid collections only
        $allAttempts = $stepSessionModel
            ->where([
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id'   => $step_id,
                'method'    => 'backward',
                'phase_id'  => $current_phase_id,
            ])
            ->whereIn('collection_id', $validCollectionIds)
            ->orderBy('session_date', 'desc')
            ->findAll();

        $consecutiveInd = 0;
        foreach ($allAttempts as $entry) {
            if ($entry['input_result'] === 'IND') {
                $consecutiveInd++;
            } else {
                break; // Stop at first non-IND
            }
        }

        // ✅ Step 5: Check mastery and record it if not already marked
        $isMastered = false;
        $already = $masteryModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'method'    => 'backward',
        ])->first();

        if ($already) {
            $masteryModel->delete($already->id);
        }

        if ($consecutiveInd >= $requiredConsecutive) {
            $isMastered = true;
            $masteryModel->insert([
                'client_id'   => $client_id,
                'target_id'   => $target_id,
                'step_id'     => $step_id,
                'method'    => 'backward',
                'collection_id'     => $collection_id,
                'session_id'     => $session_id,
                'session_date'     => $sessionDate,
                'mastered_on' => currentDate('Y-m-d'),
                'created_by'  => auth()->user()->id
            ]);
        }

        // ✅ Step 6: Overall backward-chain percentage for this target.
        // Denominator: all configured step IDs for the target.
        // Numerator: distinct mastered step IDs from baseline + backward.
        $targetStepIds = $targetStepModel
            ->where('target_id', $target_id)
            ->select('id')
            ->findColumn('id');
        $targetStepIds = array_map('intval', $targetStepIds ?? []);
        $totalSteps = count($targetStepIds);

        $masteredCount = 0;
        if ($totalSteps > 0) {
            $masteredStepIds = $masteryModel
                ->distinct()
                ->select('step_id')
                ->where('client_id', $client_id)
                ->where('target_id', $target_id)
                ->whereIn('method', ['baseline', 'backward'])
                ->whereIn('step_id', $targetStepIds)
                ->findColumn('step_id');

            $masteredCount = count($masteredStepIds ?? []);
        }

        $percentage = $totalSteps > 0 ? round(($masteredCount / $totalSteps) * 100, 2) : 0;

        return [
            'statistics' => [
                'step_id'         => $step_id,
                'probe_value'     => $probe_value,
                'total_attempts'  => count($allAttempts),
                'required_ind'    => $requiredConsecutive,
                'consecutive_ind' => $consecutiveInd,
                'is_mastered'     => $isMastered,
                'total_steps'     => $totalSteps,
                'mastered_steps'  => $masteredCount,
                'method'    => 'backward',
            ],
            'result' => $percentage
        ];
    }

    private function recompileStepMasteryAfterDeletion($client_id, $target_id, $client_probe_set_id, $phase_id, $step_id, $method)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();
        $collectionModel  = new \App\Models\ClientSessions\DailySessionDataCollectionModel();

        // Get all valid (processed) collections
        $validCollectionIds = $collectionModel
            ->where('client_id', $client_id)
            ->where('target_id', $target_id)
            ->where('client_probe_set_id', $client_probe_set_id)
            ->where('is_processed', 1)
            ->select('id')
            ->findColumn('id');

        // Fetch relevant entries
        $attempts = $stepSessionModel
            ->where([
                'client_id' => $client_id,
                'target_id' => $target_id,
                'step_id'   => $step_id,
                'method'    => $method,
                'phase_id'  => $phase_id,
            ])
            ->whereIn('collection_id', $validCollectionIds)
            ->orderBy('session_date', 'desc')
            ->findAll();

        // Count consecutive INDs
        $consecutiveInd = 0;
        foreach ($attempts as $entry) {
            if ($entry['input_result'] === 'IND') {
                $consecutiveInd++;
            } else {
                break;
            }
        }

        // Get rule override
        $chainModel = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $chainRow = $chainModel->where('target_id', $target_id)->first();
        $required = 3;
        if ($chainRow && !empty($chainRow->rule_override)) {
            $r = json_decode($chainRow->rule_override, true);
            if (isset($r[$method]['step_mastery']['value'])) {
                $required = (int)$r[$method]['step_mastery']['value'];
            }
        }

        // Check and update mastery
        $existing = $masteryModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'step_id'   => $step_id,
            'method'    => $method,
        ])->first();

        if ($consecutiveInd >= $required) {
            if (!$existing) {
                // Insert mastery
                $masteryModel->insert([
                    'client_id' => $client_id,
                    'target_id' => $target_id,
                    'step_id'   => $step_id,
                    'method'    => $method,
                    'mastered_on' => currentDate('Y-m-d'),
                    'created_by' => auth()->user()->id,
                ]);
            }
        } else {
            if ($existing) {
                $masteryModel->delete($existing->id);
            }
        }
    }


    /****************************************************************** */

    // Update target data (called via AJAX)
    public function single()
    {
        $id = $this->request->getPost('id');
        $row_data = $this->dailySessionDataCollectionModel->getSingle($id);

        // Decode collected data JSON
        $collectedData = json_decode($row_data->collected_data, true);

        $inputsHtml = '';
        if ($collectedData['inputs']['type'] === 'stimulus_program') {
            // Load full target details including steps, chain, prefill            
            $domain = $this->clientDomainModel->find($row_data->domain_id);
            $goal = $this->clientGoalModel->find($row_data->goal_id);
            $target = $this->dailySessionDataCollectionModel->getFullStimulusTargetByCollection(
                $row_data->client_id,
                $row_data->target_id,
                $row_data->client_probe_set_id,
                $row_data->session_id,
                $row_data->session_date
            );

            // View will determine which partial to load: baseline, forward, etc.
            $inputsHtml = view("ClientSessionsReview/ProgramReview/StimulusProgramEdit/target_list_stimulus_probe.php", [
                'domain' => $domain,
                'goal' => $goal,
                'target' => $target,
                'session_id' => $row_data->session_id,
                'client_id' => $row_data->client_id,
            ]);
        } else {
            // fallback to existing renderInputsForReview()
            $inputsHtml = $this->renderInputsForReview(
                $collectedData['inputs'],
                $collectedData['result'],
                $collectedData
            );
        }

        $heading = "<div class='row'><div class='col-md-12'>";
        $heading .= "<b>Target:</b> " . esc($row_data->target_name) . "<br>";
        $heading .= "<b>Probe Set:</b> " . esc($row_data->probe_set_name);
        $heading .= "</div></div><hr>";

        $heading .= $inputsHtml;

        return $this->response->setJSON($this->getResponseObject('success', 'Success', 'Record fetched successfully', [], [
            'id'            => $row_data->id,
            'inputs_html'   => $heading,
        ]));
    }

    private function renderInputsForReview($inputs, $result, $collectedData)
    {
        $html = '';
        switch ($inputs['type']) {
            case 'yes_no':
                $html .= view('ClientSessionsReview/ProgramReview/input_yes_no', [
                    'choices' => $inputs['choices'],
                    'results' => $result
                ]);
                break;
            case 'count':
                $html .= view('ClientSessionsReview/ProgramReview/input_count', [
                    'range' => $inputs['range'],
                    'results' => $result
                ]);
                break;
            case 'traffic_light':
                $html .= view('ClientSessionsReview/ProgramReview/input_traffic_light', [
                    'choices' => $inputs['choices'],
                    'results' => $result
                ]);
                break;
            case 'prompt_level':
                $html .= view('ClientSessionsReview/ProgramReview/input_prompt_level', [
                    'choices' => $inputs['choices'],
                    'results' => $result
                ]);
                break;
            case 'duration':
                $html .= view('ClientSessionsReview/ProgramReview/input_duration', [
                    'choices' => $inputs['choices'],
                    'results' => $result
                ]);
                break;
            case 'percentage_yes_no':
                $html .= view('ClientSessionsReview/ProgramReview/input_percentage_yes_no', [
                    'choices' => $inputs['choices'],
                    'results' => $result,
                    'transitions' => $collectedData['transitions']
                ]);
                break;
            default:
                $html .= '<p>Unknown Input Format</p>';
        }
        return $html;
    }
    // Update target data (called via AJAX)
    public function updateData()
    {
        $dataId = $this->request->getPost('id'); // Get the record ID
        $selectedValues = $this->request->getPost('selected_value'); // Get the selected values array



        // Fetch the existing record
        $record = $this->dailySessionDataCollectionModel->find($dataId);

        if (!$record) {
            $response = $this->getResponseObject('error', 'Error', 'Record not Founded', [], []);
            return $this->response->setJSON($response);
        }
        if ($record->is_processed) {
            $response = $this->getResponseObject('error', 'Error', 'Record already processed. update is not allowed.', [], []);
            return $this->response->setJSON($response);
        }
        if ($record->is_conflicted) {
            $response = $this->getResponseObject('error', 'Error', 'Record has conflict. Update is not allowed.', [], []);
            return $this->response->setJSON($response);
        }

        // Decode the collected_data JSON object
        $collectedData = json_decode($record->collected_data, true);

        // Update the 'result' key with the new value
        $collectedData['result'] = $selectedValues;

        // Encode the JSON object back to string
        $updatedCollectedData = json_encode($collectedData);



        // Update the record in the database
        $this->dailySessionDataCollectionModel->update($dataId, ['collected_data' => $updatedCollectedData, 'updated_at' => currentDate('Y-m-d H:i:s'), 'updated_by' => auth()->user()->id]);


        $data = $this->dailySessionDataCollectionModel->getSingle($dataId);
        $row_data = [];

        $collectedData = json_decode($data->collected_data);
        $result = $collectedData->result;
        $phaseName = $collectedData->phase->name;
        $row_data[] = $data->domain_code;
        $row_data[] = $data->goal_code;
        $row_data[] = $data->target_name;
        $row_data[] = $data->probe_set_name;
        $row_data[] = $phaseName;
        // Assuming $result is the array of values you want to process
        $circle_elements = '';  // String to accumulate the HTML

        foreach ($result as $value) {
            // Start building each circle element
            $circle_elements .= '<div class="rounded-circle d-flex justify-content-center align-items-center" ';
            $circle_elements .= 'style="width: 40px; height: 40px; background-color: #e0e0e0; font-size: 14px;">';

            // Check if the value is valid (treat 0 as a valid value)
            if ($value != null && $value != '') {
                // Add the value to the circle
                $circle_elements .= htmlspecialchars($value);
            } else {
                // Add a cross icon if the value is null or empty
                $circle_elements .= '<i class="ri-close-line text-danger"></i>';
            }

            // Close the div for the circle
            $circle_elements .= '</div>';
        }

        // Wrap the elements in a container (d-inline-flex)
        $circle_container = '<div class="d-inline-flex flex-nowrap gap-1">' . $circle_elements . '</div>';

        // Add the generated HTML string to the row data array
        $row_data[] = $circle_container;
        $row_data[] = '<span class="badge border border-info text-info">Pending</span>';
        $row_btn = '<button data-id="' . $data->id . '" data-master-probe-set-id="' . $data->master_probe_set_id . '" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>';
        $row_btn .= '&nbsp;<button data-id="' . $data->id . '" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>';
        $row_data[] = $row_btn;
        $response = $this->getResponseObject('success', 'Success', 'Data updated successfully', [], $row_data);
        return $this->response->setJSON($response);
    }
    public function updatePercentageYesNoData()
    {
        $dataId = $this->request->getPost('id'); // Get the record ID
        $transitions = $this->request->getPost('transitions'); // Get the selected values array



        // Fetch the existing record
        $record = $this->dailySessionDataCollectionModel->find($dataId);


        if (!$record) {
            $response = $this->getResponseObject('error', 'Error', 'Record not Founded', [], []);
            return $this->response->setJSON($response);
        }
        if ($record->is_processed) {
            $response = $this->getResponseObject('error', 'Error', 'Record already processed. update is not allowed.', [], []);
            return $this->response->setJSON($response);
        }
        if ($record->is_conflicted) {
            $response = $this->getResponseObject('error', 'Error', 'Record has conflict. Update is not allowed.', [], []);
            return $this->response->setJSON($response);
        }



        // Decode the collected_data JSON object
        $collectedData = json_decode($record->collected_data, true);

        $collectedData['transitions'] = $transitions;

        $totalYes = 0;
        $totalNo = 0;

        foreach ($transitions as $item) {
            if (strtoupper($item['answer']) == 'Y') {
                $totalYes++;
            } elseif (strtoupper($item['answer']) == 'N') {
                $totalNo++;
            }
        }

        $total = $totalYes + $totalNo;
        $percentage = $total > 0 ? round(($totalYes / $total) * 100, 2) : 0;

        $collectedData['statistics'] = [
            'total_yes' => $totalYes,
            'total_no' => $totalNo,
            'percentage' => $percentage,
        ];

        // If your result is stored as an array
        $collectedData['result'] = [$percentage];


        // Update the record in the database
        $this->dailySessionDataCollectionModel->update($dataId, ['collected_data' => json_encode($collectedData), 'updated_at' => currentDate('Y-m-d H:i:s'), 'updated_by' => auth()->user()->id]);


        $data = $this->dailySessionDataCollectionModel->getSingle($dataId);
        $row_data = [];

        $collectedData = json_decode($data->collected_data);
        $result = $collectedData->result;
        $phaseName = $collectedData->phase->name;
        $row_data[] = $data->domain_code;
        $row_data[] = $data->goal_code;
        $row_data[] = $data->target_name;
        $row_data[] = $data->probe_set_name;
        $row_data[] = $phaseName;
        // Assuming $result is the array of values you want to process
        $circle_elements = '';  // String to accumulate the HTML

        foreach ($result as $value) {
            // Start building each circle element
            $circle_elements .= '<div class="rounded-circle d-flex justify-content-center align-items-center" ';
            $circle_elements .= 'style="width: 40px; height: 40px; background-color: #e0e0e0; font-size: 14px;">';

            // Check if the value is valid (treat 0 as a valid value)
            if ($value != null && $value != '') {
                // Add the value to the circle
                $circle_elements .= htmlspecialchars($value) . "%";
            } else {
                // Add a cross icon if the value is null or empty
                $circle_elements .= '<i class="ri-close-line text-danger"></i>';
            }

            // Close the div for the circle
            $circle_elements .= '</div>';
        }

        // Wrap the elements in a container (d-inline-flex)
        $circle_container = '<div class="d-inline-flex flex-nowrap gap-1">' . $circle_elements . '</div>';

        // Add the generated HTML string to the row data array
        $row_data[] = $circle_container;
        $row_data[] = '<span class="badge border border-info text-info">Pending</span>';
        $row_btn = '<button data-id="' . $data->id . '" data-master-probe-set-id="' . $data->master_probe_set_id . '" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>';
        $row_btn .= '&nbsp;<button data-id="' . $data->id . '" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>';
        $row_data[] = $row_btn;
        $response = $this->getResponseObject('success', 'Success', 'Data updated successfully', [], $row_data);
        return $this->response->setJSON($response);
    }

    // Delete a target's collected data
    public function deleteTarget()
    {
        $dataId = $this->request->getPost('id');

        // Check if the record is unprocessed (is_processed = 0)
        $record = $this->dailySessionDataCollectionModel->find($dataId);

        if (!$record) {
            // Record not found
            $response = $this->getResponseObject('error', 'Error', 'Record not found.', [], []);
            return $this->response->setJSON($response);
        }

        if ($record->is_processed == 1) {
            // If the record is processed, do not allow deletion
            $response = $this->getResponseObject('error', 'Error', 'Cannot delete a processed record.', [], []);
            return $this->response->setJSON($response);
        }

        // Begin DB transaction
        $db = \Config\Database::connect();
        $db->transException(true)->transStart();
        try {
            // If the record is unprocessed, proceed with the deletion
            $this->dailySessionDataCollectionModel->delete($dataId);

            // Check if session is fully processed
            $sessionProcessingStatus = $this->dailySessionDataCollectionModel->checkCollectedDataProcessingStatus($record->session_id);

            // Update session status
            $updatedSessionData = [
                'status' => $sessionProcessingStatus['status_code'],
                'note' => $sessionProcessingStatus['status_name']
            ];
            // Save session update
            $this->dailySessionModel->update($record->session_id, $updatedSessionData);


            $collectedData = json_decode($record->collected_data, true);
            $method = $collectedData['method'] ?? null;
            $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
            $step_id_row = $stepSessionModel
                ->where('collection_id', $dataId)
                ->select('step_id')
                ->first();
            $step_id = $step_id_row['step_id'] ?? null;

            $stepSessionModel->where('collection_id', $dataId)->delete();
            if (in_array($method, ['forward', 'backward']) && $step_id) {
                $this->recompileStepMasteryAfterDeletion(
                    $record->client_id,
                    $record->target_id,
                    $record->client_probe_set_id,
                    $record->current_phase_id,
                    $step_id,
                    $method
                );
            }
            if ($method === 'baseline') {
                $masteryModel = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();

                $masteryModel
                    ->where('client_id', $record->client_id)
                    ->where('target_id', $record->target_id)
                    ->where('method', 'baseline')
                    ->where('collection_id', $record->collection_id)
                    ->delete();
            }


            // ✅ Commit Transaction
            $db->transCommit();
            //$db->transRollback();  // Use for testing rollback
            if ($db->transStatus() === FALSE) {
                $db->transRollback();

                $response = $this->getResponseObject('error', 'Error', 'Database transaction failed.', [], []);
                return $this->response->setJSON($response);
            }

            $response = $this->getResponseObject('success', 'Success', 'Record deleted successfully.', [], []);
            return $this->response->setJSON($response);
        } catch (\Throwable $e) {
            // Rollback on failure
            $db->transRollback();

            log_message('error', 'Stimulus Deletion Error: ' . $e->getMessage());

            return $this->response->setJSON(
                $this->getResponseObject('error', 'Error', 'An error occurred while deleting the record.', [], [])
            );
        }
    }

    public function viewTargetConflictDetail()
    {
        $id = $this->request->getPost('id');
        $row_data = $this->dailySessionDataCollectionModel->getSingle($id);

        if (!$row_data) {
            return $this->response->setJSON(['success' => false, 'html' => 'Target data not found.']);
        }

        // Extract required details
        $clientId = $row_data->client_id;
        $probeSetId = $row_data->client_probe_set_id;
        $domainId = $row_data->domain_id;
        $goalId = $row_data->goal_id;
        $targetId = $row_data->target_id;

        // Fetch the necessary data
        $clientProgramData = $this->clientDataSheetModel->getSingleTargetDataSheetInformation($clientId, $probeSetId, $domainId, $goalId, $targetId);
        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();



        // ✅ **Return the view as an HTML response**
        return $this->response->setJSON([
            'success' => true,
            'html' => view(
                'ClientSessionsReview/ProgramReview/targetDetail',
                [
                    'existingData' => $row_data,
                    'phases' => $phaseArray,
                    'clientProgramData' => $clientProgramData,
                ]
            )
        ]);
    }

    /********************************************************************************************************************************** */
    // Update or Delete PB records (called via AJAX) 
    public function getPBRecord()
    {

        $duration_id = $this->request->getPost('duration_id');
        $pb_duration = $this->sessionPBDurationModel->where('id', $duration_id)->first();


        $record_id = $this->request->getPost('record_id');
        $pb_record = $record_id ? $this->pbRecordsModel->where('id', $record_id)->first() : null;

        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'message' => 'Record fetched successfully',
            'data' => [
                'pb_record' => $pb_record,
                'pb_duration' => $pb_duration
            ]
        ];

        return $this->response->setJSON($response);
    }

    public function createPBRecord()
    {


        $validationRules = [
            'a_session_id' => [
                'label' => 'Session',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Session required.'
                ]
            ],
            'a_antecedent' => [
                'label' => 'Antecedent',
                'rules' => 'required',
                'errors' => [
                    'required' => 'The antecedent field is required.'
                ]
            ],
            'a_consequence' => [
                'label' => 'Consequence',
                'rules' => 'required',
                'errors' => [
                    'required' => 'The consequence field is required.'
                ]
            ],
            'a_behavior' => [
                'label' => 'Behavior',
                'rules' => 'required',
                'errors' => [
                    'required' => 'At least one behavior must be selected.'
                ]
            ],
            'a_antecedent_other' => [
                'label' => 'Antecedent other',
                'rules' => $this->request->getPost('a_antecedent') == 'Other' ? 'required' : 'permit_empty',
                'errors' => [
                    'required' => 'Please specify the "Other" antecedent.'
                ]
            ],
            'a_consequence_other' => [
                'label' => 'Consequence other',
                'rules' => $this->request->getPost('a_consequence') == 'Other' ? 'required' : 'permit_empty',
                'errors' => [
                    'required' => 'Please specify the "Other" consequence.'
                ]
            ]
        ];


        // Run validation
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => $this->validator->getErrors(),
            ]);
        }

        // Run custom validation for behaviors
        if (!$this->validateBehaviors_new()) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => ['Please select or enter at least one valid behavior.'],
            ]);
        }



        $pb_duration_data = [
            'start_time' => $this->request->getPost('a_start_time'),
            'end_time' => $this->request->getPost('a_end_time')
        ];

        service('validation')->reset();

        $validationRules = [
            'start_time' => [
                'label' => 'Start Time',
                'rules' => 'required|valid_date[H:i:s]',
                'errors' => [
                    'required' => 'Start time required.',
                    'valid_date' => 'Enter valid start time [H:i:s].'
                ]
            ],
            'end_time' => [
                'label' => 'End Time',
                'rules' => 'required|valid_date[H:i:s]|compareTimes[start_time]',
                'errors' => [
                    'required' => 'End time is required.',
                    'valid_date' => 'Enter a valid end time in the format [H:i:s].',
                    'compareTimes' => 'End time must be greater than start time.'
                ]
            ],
        ];
        // Run validation
        if (!$this->validateData($pb_duration_data, $validationRules)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => $this->validator->getErrors(),
            ]);
        }

        $session_id = $this->request->getPost('a_session_id');
        $session = $this->dailySessionModel->getSessionByID($session_id);

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($session->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => ['You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.'],
                ]);
            }
        }

        // ✅ Check if duration is within session time
        $sessionCheck = $this->dailySessionModel->isWithinSessionTime(
            $session_id,
            $pb_duration_data['start_time'],
            $pb_duration_data['end_time']
        );

        if (!$sessionCheck['status']) {
            return $this->response->setJSON(['success' => 'No', 'message' => [$sessionCheck['message']]]);
        }

        // ✅ Check for overlaps
        $overlapCheck = $this->sessionPBDurationModel->hasOverlap(
            $session_id,
            $pb_duration_data['start_time'],
            $pb_duration_data['end_time']
        );

        if (!$overlapCheck['status']) {
            return $this->response->setJSON(['success' => 'No', 'message' => [$overlapCheck['message']]]);
        }

        try {
            $pb_duration = [
                'client_id' => $session->client_id,
                'session_id' => $session->id,
                'session_date' => $session->session_date,
                'start_time' => $this->request->getPost('a_start_time'),
                'end_time' => $this->request->getPost('a_end_time')
            ];

            $this->sessionPBDurationModel->insert($pb_duration);
            $pb_duration_id = $this->sessionPBDurationModel->getInsertID();


            $pb_record_data = [
                'pb_timer_id' => $pb_duration_id,
                'client_id' => $session->client_id,
                'session_id' => $session->id,
                'session_date' => $session->session_date,
                'antecedent' => $this->request->getPost('a_antecedent') == 'Other' ? trim($this->request->getPost('a_antecedent_other')) : trim($this->request->getPost('a_antecedent')),
                'consequence' => $this->request->getPost('a_consequence') == 'Other' ? trim($this->request->getPost('a_consequence_other')) : trim($this->request->getPost('a_consequence')),
                'behavior' => $this->getBehaviorJson_new(),
                'abc_comments' => $this->request->getPost('a_abc_comments')
            ];

            $this->pbRecordsModel->insert($pb_record_data);

            $new_record = $this->pbRecordsModel->getSingleCompleteRecordSet($pb_duration_id);
            return $this->response->setJSON([
                'success' => 'Yes',
                'message' => 'Record created successfully.',
                'record' => $new_record
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => ['Update failed: ' . $e->getMessage()]
            ]);
        }
    }
    public function updatePBRecord()
    {
        $validationRules = [
            'pb_duration_id' => [
                'label' => 'PB Duration',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Session required.'
                ]
            ],
            'antecedent' => [
                'label' => 'Antecedent',
                'rules' => 'required',
                'errors' => [
                    'required' => 'The antecedent field is required.'
                ]
            ],
            'consequence' => [
                'label' => 'Consequence',
                'rules' => 'required',
                'errors' => [
                    'required' => 'The consequence field is required.'
                ]
            ],
            'behavior' => [
                'label' => 'Behavior',
                'rules' => 'required',
                'errors' => [
                    'required' => 'At least one behavior must be selected.'
                ]
            ],
            'antecedent_other' => [
                'label' => 'Antecedent other',
                'rules' => $this->request->getPost('antecedent') == 'Other' ? 'required' : 'permit_empty',
                'errors' => [
                    'required' => 'Please specify the "Other" antecedent.'
                ]
            ],
            'consequence_other' => [
                'label' => 'Consequence other',
                'rules' => $this->request->getPost('consequence') == 'Other' ? 'required' : 'permit_empty',
                'errors' => [
                    'required' => 'Please specify the "Other" consequence.'
                ]
            ]
        ];


        // Run validation
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => $this->validator->getErrors(),
            ]);
        }

        // Run custom validation for behaviors
        if (!$this->validateBehaviors()) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => ['Please select or enter at least one valid behavior.'],
            ]);
        }



        $pb_duration_data = [
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time')
        ];

        service('validation')->reset();

        $validationRules = [
            'start_time' => [
                'label' => 'Start time',
                'rules' => 'required|valid_date[H:i:s]',
                'errors' => [
                    'required' => 'Start time required.',
                    'valid_date' => 'Enter valid start time [H:i:s].'
                ]
            ],
            'end_time' => [
                'label' => 'End Time',
                'rules' => 'required|valid_date[H:i:s]|compareTimes[start_time]',
                'errors' => [
                    'required' => 'End time is required.',
                    'valid_date' => 'Enter a valid end time in the format [H:i:s].',
                    'compareTimes' => 'End time must be greater than start time.'
                ]
            ],
        ];
        // Run validation
        if (!$this->validateData($pb_duration_data, $validationRules)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => $this->validator->getErrors(),
            ]);
        }

        $pb_record_id = $this->request->getPost('pb_record_id');
        $pb_duration_id = $this->request->getPost('pb_duration_id');

        // ✅ Fetch existing duration record
        $pb_duration = $this->sessionPBDurationModel->where('id', $pb_duration_id)->first();

        if (!$pb_duration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => ['Duration record not found.'],
            ]);
        }




        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($pb_duration->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }

        $pb_record_data = [
            'pb_timer_id' => $pb_duration_id,
            'client_id' => $pb_duration->client_id,
            'session_id' => $pb_duration->session_id,
            'session_date' => $pb_duration->session_date,
            'antecedent' => $this->request->getPost('antecedent') == 'Other' ? trim($this->request->getPost('antecedent_other')) : trim($this->request->getPost('antecedent')),
            'consequence' => $this->request->getPost('consequence') == 'Other' ? trim($this->request->getPost('consequence_other')) : trim($this->request->getPost('consequence')),
            'behavior' => $this->getBehaviorJson(),
            'abc_comments' => $this->request->getPost('abc_comments')
        ];



        // ✅ Ensure new times are within session range
        $sessionCheck = $this->dailySessionModel->isWithinSessionTime(
            $pb_duration->session_id,
            $pb_duration_data['start_time'],
            $pb_duration_data['end_time']
        );

        if (!$sessionCheck['status']) {
            return $this->response->setJSON(['success' => 'No', 'message' => [$sessionCheck['message']]]);
        }

        // ✅ Ensure updated time does not overlap with other durations
        $overlapCheck = $this->sessionPBDurationModel->hasOverlap(
            $pb_duration->session_id,
            $pb_duration_data['start_time'],
            $pb_duration_data['end_time'],
            $pb_duration_id // Pass ID to exclude itself in check
        );

        if (!$overlapCheck['status']) {
            return $this->response->setJSON(['success' => 'No', 'message' => [$overlapCheck['message']]]);
        }

        try {

            $this->sessionPBDurationModel->update($pb_duration_id, $pb_duration_data);
            if ($pb_record_id) {
                $this->pbRecordsModel->update($pb_record_id, $pb_record_data);
            } else {

                $this->pbRecordsModel->insert($pb_record_data);
            }

            $updated_record = $this->pbRecordsModel->getSingleCompleteRecordSet($pb_duration_id);
            return $this->response->setJSON([
                'success' => 'Yes',
                'message' => 'Record updated successfully.',
                'updated_record' => $updated_record
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => ['Update failed: ' . $e->getMessage()]
            ]);
        }
    }

    private function getBehaviorJson_new()
    {
        $behaviors = $this->request->getPost('a_behavior') ?? [];
        $intensities = $this->request->getPost('a_intensity') ?? [];

        $behaviorData = [];
        foreach ($behaviors as $behavior) {
            if (trim($behavior) == '') {
                continue;
            }
            $behaviorData[] = [
                'behavior' => $behavior,
                'intensity' => $intensities[$behavior] ?? null
            ];
        }

        return json_encode($behaviorData);
    }
    // Custom validation for behaviors
    private function validateBehaviors_new()
    {
        $behaviors = $this->request->getPost('a_behavior') ?? [];  // Predefined checkboxes and dynamic inputs
        $valid = false;

        foreach ($behaviors as $behavior) {
            if (trim($behavior) != '') { // Check if behavior is not empty
                $valid = true;
                break;
            }
        }

        return $valid; // Ensure at least one behavior is selected or entered
    }
    private function getBehaviorJson()
    {
        $behaviors = $this->request->getPost('behavior') ?? [];
        $intensities = $this->request->getPost('intensity') ?? [];

        $behaviorData = [];
        foreach ($behaviors as $behavior) {
            if (trim($behavior) == '') {
                continue;
            }
            $behaviorData[] = [
                'behavior' => $behavior,
                'intensity' => $intensities[$behavior] ?? null
            ];
        }

        return json_encode($behaviorData);
    }
    // Custom validation for behaviors
    private function validateBehaviors()
    {
        $behaviors = $this->request->getPost('behavior') ?? [];  // Predefined checkboxes and dynamic inputs
        $valid = false;

        foreach ($behaviors as $behavior) {
            if (trim($behavior) != '') { // Check if behavior is not empty
                $valid = true;
                break;
            }
        }

        return $valid; // Ensure at least one behavior is selected or entered
    }

    public function deletePBRecord()
    {
        $record_id = $this->request->getPost('record_id');
        $duration_id = $this->request->getPost('duration_id');

        $pb_duration = $this->sessionPBDurationModel->where('id', $duration_id)->first();
        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($pb_duration->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }

        try {


            if ($record_id) {
                $this->pbRecordsModel->where('id', $record_id)->delete();
            }
            $this->sessionPBDurationModel->where('id', $duration_id)->delete();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Deletion failed: ' . $e->getMessage()
            ]);
        }
    }
    /********************************************************************************************************************* */
    public function get_mands_form_manually($session_id)
    {
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        $client = $this->clientModel->find($sessionDetail->client_id);
        $topReinforcer = $this->mandsSessionDataModel->getTopReinforcerInputs($sessionDetail->client_id);
        //print_r($target); die;
        $data =  [
            'sessionDetail' => $sessionDetail,
            'client' =>  $client,
            'client_id' => $sessionDetail->client_id,
            'session_id' => $session_id,
            'topReinforcer' => $topReinforcer,
            'session_date' => $sessionDetail->session_date,
            'page_title' => 'Manually mands data entry form'
        ];
        return view('ClientSessionsReview/MandsReview/mands_entry', $data);
    }

    public function save_mands_form_manually()
    {
        // Retrieve data from the AJAX request
        $client_id = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');
        $reinforcer_input = $this->request->getPost('reinforcer_input');
        $utterance_input = $this->request->getPost('utterance_input');
        $is_peer_manding = (int) ($this->request->getPost('is_peer_manding') ?? 0);
        $is_eye_contact = (int) ($this->request->getPost('is_eye_contact') ?? 0);
        $prompt_level = $this->request->getPost('prompt_level');
        if ($prompt_level != null) $prompt_level = (int) $prompt_level;
        $mands_error = $this->request->getPost('mands_error');
        if ($mands_error != null) $mands_error = (int) $mands_error;
        $initial_attempt_input = $this->request->getPost('initial_attempt_input');
        $initial_attempt = $this->request->getPost('initial_attempt');
        if ($initial_attempt != null) $initial_attempt = (int) $initial_attempt;
        $prompt_delay_input = $this->request->getPost('prompt_delay_input');
        $prompt_delay = $this->request->getPost('prompt_delay');
        if ($prompt_delay != null) $prompt_delay = (int) $prompt_delay;
        $echoic_1_input = $this->request->getPost('echoic_1_input');
        $echoic_1 = $this->request->getPost('echoic_1');
        if ($echoic_1 != null) $echoic_1 = (int) $echoic_1;
        $echoic_2_input = $this->request->getPost('echoic_2_input');
        $echoic_2 = $this->request->getPost('echoic_2');
        if ($echoic_2 != null) $echoic_2 = (int) $echoic_2;
        $echoic_3_input = $this->request->getPost('echoic_3_input');
        $echoic_3 = $this->request->getPost('echoic_3');
        if ($echoic_3 != null) $echoic_3 = (int) $echoic_3;

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session not exist.',
            ]);
        }

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($sessionDetail->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }



        $session_date = $sessionDetail->session_date;
        // Perform any necessary validation or processing

        // Perform validations
        if (empty($reinforcer_input)) {
            $data = [
                'success' => 'No',
                'message' => 'Reinforcer can not be empty',
            ];
            return $this->respond($data);
        }

        if ($prompt_level == null) {
            $data = [
                'success' => 'No',
                'message' => 'Select Prompt Level',
            ];
            return $this->respond($data);
        }

        // Convert empty strings to null for specified inputs
        $utterance_input = ($utterance_input == '') ? null : $utterance_input;
        $initial_attempt_input = ($initial_attempt_input == '') ? null : $initial_attempt_input;
        $prompt_delay_input = ($prompt_delay_input == '') ? null : $prompt_delay_input;
        $echoic_1_input = ($echoic_1_input == '') ? null : $echoic_1_input;
        $echoic_2_input = ($echoic_2_input == '') ? null : $echoic_2_input;
        $echoic_3_input = ($echoic_3_input == '') ? null : $echoic_3_input;

        // Perform additional validations
        if ($initial_attempt_input == null && ($initial_attempt > 1 && $initial_attempt < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Initial attempt input field is required.',
            ];
            return $this->respond($data);
        }
        if ($initial_attempt_input != null && $initial_attempt == null) {
            $data = [
                'success' => 'No',
                'message' => 'Initial attempt probe is required.',
            ];
            return $this->respond($data);
        }

        if ($prompt_delay_input == null && ($prompt_delay > 1 && $prompt_delay < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Prompt Delay input field required',
            ];
            return $this->respond($data);
        }

        if ($prompt_delay_input != null && $prompt_delay == null) {
            $data = [
                'success' => 'No',
                'message' => 'Prompt Delay probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_1_input == null && ($echoic_1 > 1 && $echoic_1 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 1 input field required',
            ];
            return $this->respond($data);
        }

        if ($echoic_1_input != null && $echoic_1 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 1 probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_2_input == null && ($echoic_2 > 1 && $echoic_2 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 2 input field required',
            ];
            return $this->respond($data);
        }

        if ($echoic_2_input != null && $echoic_2 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 2 probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_3_input == null && ($echoic_3 > 1 && $echoic_3 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 3 input field required',
            ];
            return $this->respond($data);
        }
        if ($echoic_3_input != null && $echoic_3 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 3 probe is required',
            ];
            return $this->respond($data);
        }

        // Check echoic input values to ensure they are in sequence and have values
        if ($echoic_3 > 1 && $echoic_3 < 5) {
            if (empty($echoic_1_input) || empty($echoic_2_input)) {
                $data = [
                    'success' => 'No',
                    'message' => 'Echoic 1 and 2 must have input values',
                ];
                return $this->respond($data);
            }
        } else if ($echoic_2 > 1 && $echoic_2 < 5) {
            if (empty($echoic_1_input)) {
                $data = [
                    'success' => 'No',
                    'message' => 'Echoic 1 must have input values',
                ];
                return $this->respond($data);
            }
        }
        // Perform comparisons
        $comparison_prompt_delay = null;
        if ($initial_attempt > 1 && $prompt_delay > 1) {
            if ($prompt_delay == $initial_attempt) {
                $comparison_prompt_delay = 2; // Same
            } elseif ($prompt_delay > $initial_attempt) {
                $comparison_prompt_delay = 3; // Improved
            } elseif ($prompt_delay < $initial_attempt) {
                $comparison_prompt_delay = 1; // Declined
            }
        }
        /******************** */

        $comparison_echoic_trial = null;

        if ($initial_attempt != null) {
            // Pick latest available echoic probe
            if ($echoic_3 != null) {
                $echoicValue = $echoic_3;
            } elseif ($echoic_2 != null) {
                $echoicValue = $echoic_2;
            } elseif ($echoic_1 != null) {
                $echoicValue = $echoic_1;
            }

            if (isset($echoicValue)) {
                if ($echoicValue == $initial_attempt) {
                    $comparison_echoic_trial = 2; // Same
                } elseif ($echoicValue > $initial_attempt) {
                    $comparison_echoic_trial = 3; // Improved
                } elseif ($echoicValue < $initial_attempt) {
                    $comparison_echoic_trial = 1; // Declined
                }
            }
        } else {
            // No initial attempt; compare echoic 1 to 3 or 2 if available
            /*if ($echoic_1 != null && $echoic_3 != null) {
                if ($echoic_3 == $echoic_1) {
                    $comparison_echoic_trial = 2;
                } elseif ($echoic_3 > $echoic_1) {
                    $comparison_echoic_trial = 3;
                } elseif ($echoic_3 < $echoic_1) {
                    $comparison_echoic_trial = 1;
                }
            } elseif ($echoic_1 != null && $echoic_2 != null) {
                if ($echoic_2 == $echoic_1) {
                    $comparison_echoic_trial = 2;
                } elseif ($echoic_2 > $echoic_1) {
                    $comparison_echoic_trial = 3;
                } elseif ($echoic_2 < $echoic_1) {
                    $comparison_echoic_trial = 1;
                }
            }*/
        }
        // Prepare response data
        $reinforcer_input = normalize_reinforcer_input((string) $reinforcer_input);
        if ($reinforcer_input === '') {
            return $this->respond([
                'success' => 'No',
                'message' => 'Reinforcer can not be empty',
            ]);
        }

        $data = [
            'client_id' => $client_id,
            'session_date' => $session_date,
            'session_id' => $session_id,
            'reinforcer_input' => $reinforcer_input,
            'utterance_input' => $utterance_input,
            'is_peer_manding' => $is_peer_manding,
            'is_eye_contact' => $is_eye_contact,
            'prompt_level' => $prompt_level,
            'mands_error' => $mands_error,
            'initial_attempt_input' => $initial_attempt_input,
            'initial_attempt' => $initial_attempt,
            'prompt_delay_input' => $prompt_delay_input,
            'prompt_delay' => $prompt_delay,
            'echoic_1_input' => $echoic_1_input,
            'echoic_1' => $echoic_1,
            'echoic_2_input' => $echoic_2_input,
            'echoic_2' => $echoic_2,
            'echoic_3_input' => $echoic_3_input,
            'echoic_3' => $echoic_3,
            'comparison_prompt_delay' => $comparison_prompt_delay,
            'comparison_echoic_trial' =>  $comparison_echoic_trial,
            'created_by' => auth()->user()->id,
        ];

        try {
            $this->ensureMasterReinforcer($reinforcer_input);
            $this->ensureClientReinforcer((int) $client_id, $reinforcer_input, (string) $session_date);

            $mandsSessionData = new \App\Entities\Mands\MandsSessionData();
            $mandsSessionData->fill($data);
            $this->mandsSessionDataModel->save($mandsSessionData);



            return $this->respond(['success' => 'Yes', 'message' => 'Mands data saved successfully']);
        } catch (\Exception $e) {
            return $this->respond(['success' => 'No', 'message' => $e->getMessage()]);
        }
    }
    public function getMandsRecord()
    {

        $mands_id = $this->request->getPost('mands_id');
        $mandsRecord = $this->mandsSessionDataModel->where('id', $mands_id)->first();



        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'message' => 'Record fetched successfully',
            'data' => $mandsRecord
        ];

        return $this->response->setJSON($response);
    }

    public function updateMandsRecord()
    {
        // Retrieve data from the AJAX request
        $mands_id = $this->request->getPost('id');
        $client_id = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');
        $reinforcer_input = $this->request->getPost('reinforcer_input');
        $utterance_input = $this->request->getPost('utterance_input');
        $is_peer_manding = (int) ($this->request->getPost('is_peer_manding') ?? 0);
        $is_eye_contact = (int) ($this->request->getPost('is_eye_contact') ?? 0);
        $prompt_level = $this->request->getPost('prompt_level');
        if ($prompt_level != null) $prompt_level = (int) $prompt_level;
        $mands_error = $this->request->getPost('mands_error');
        if ($mands_error != null) $mands_error = (int) $mands_error;
        $initial_attempt_input = $this->request->getPost('initial_attempt_input');
        $initial_attempt = $this->request->getPost('initial_attempt');
        if ($initial_attempt != null) $initial_attempt = (int) $initial_attempt;
        $prompt_delay_input = $this->request->getPost('prompt_delay_input');
        $prompt_delay = $this->request->getPost('prompt_delay');
        if ($prompt_delay != null) $prompt_delay = (int) $prompt_delay;
        $echoic_1_input = $this->request->getPost('echoic_1_input');
        $echoic_1 = $this->request->getPost('echoic_1');
        if ($echoic_1 != null) $echoic_1 = (int) $echoic_1;
        $echoic_2_input = $this->request->getPost('echoic_2_input');
        $echoic_2 = $this->request->getPost('echoic_2');
        if ($echoic_2 != null) $echoic_2 = (int) $echoic_2;
        $echoic_3_input = $this->request->getPost('echoic_3_input');
        $echoic_3 = $this->request->getPost('echoic_3');
        if ($echoic_3 != null) $echoic_3 = (int) $echoic_3;

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session not exist.',
            ]);
        }

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($sessionDetail->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }


        // Perform validations
        if (empty($reinforcer_input)) {
            $data = [
                'success' => 'No',
                'message' => 'Reinforcer can not be empty',
            ];
            return $this->respond($data);
        }

        if ($prompt_level == null) {
            $data = [
                'success' => 'No',
                'message' => 'Select Prompt Level',
            ];
            return $this->respond($data);
        }

        // Convert empty strings to null for specified inputs
        $utterance_input = ($utterance_input == '') ? null : $utterance_input;
        $initial_attempt_input = ($initial_attempt_input == '') ? null : $initial_attempt_input;
        $prompt_delay_input = ($prompt_delay_input == '') ? null : $prompt_delay_input;
        $echoic_1_input = ($echoic_1_input == '') ? null : $echoic_1_input;
        $echoic_2_input = ($echoic_2_input == '') ? null : $echoic_2_input;
        $echoic_3_input = ($echoic_3_input == '') ? null : $echoic_3_input;

        // Perform additional validations
        if ($initial_attempt_input == null && ($initial_attempt > 1 && $initial_attempt < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Initial attempt input field is required.',
            ];
            return $this->respond($data);
        }
        if ($initial_attempt_input != null && $initial_attempt == null) {
            $data = [
                'success' => 'No',
                'message' => 'Initial attempt probe is required.',
            ];
            return $this->respond($data);
        }

        if ($prompt_delay_input == null && ($prompt_delay > 1 && $prompt_delay < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Prompt Delay input field required',
            ];
            return $this->respond($data);
        }

        if ($prompt_delay_input != null && $prompt_delay == null) {
            $data = [
                'success' => 'No',
                'message' => 'Prompt Delay probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_1_input == null && ($echoic_1 > 1 && $echoic_1 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 1 input field required',
            ];
            return $this->respond($data);
        }

        if ($echoic_1_input != null && $echoic_1 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 1 probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_2_input == null && ($echoic_2 > 1 && $echoic_2 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 2 input field required',
            ];
            return $this->respond($data);
        }

        if ($echoic_2_input != null && $echoic_2 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 2 probe is required',
            ];
            return $this->respond($data);
        }

        if ($echoic_3_input == null && ($echoic_3 > 1 && $echoic_3 < 5)) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 3 input field required',
            ];
            return $this->respond($data);
        }
        if ($echoic_3_input != null && $echoic_3 == null) {
            $data = [
                'success' => 'No',
                'message' => 'Echoic 3 probe is required',
            ];
            return $this->respond($data);
        }

        // Check echoic input values to ensure they are in sequence and have values
        if ($echoic_3 > 1 && $echoic_3 < 5) {
            if (empty($echoic_1_input) || empty($echoic_2_input)) {
                $data = [
                    'success' => 'No',
                    'message' => 'Echoic 1 and 2 must have input values',
                ];
                return $this->respond($data);
            }
        } else if ($echoic_2 > 1 && $echoic_2 < 5) {
            if (empty($echoic_1_input)) {
                $data = [
                    'success' => 'No',
                    'message' => 'Echoic 1 must have input values',
                ];
                return $this->respond($data);
            }
        }

        // Perform comparisons
        // Perform comparisons
        $comparison_prompt_delay = null;
        if ($initial_attempt > 1 && $prompt_delay > 1) {
            if ($prompt_delay == $initial_attempt) {
                $comparison_prompt_delay = 2; // Same
            } elseif ($prompt_delay > $initial_attempt) {
                $comparison_prompt_delay = 3; // Improved
            } elseif ($prompt_delay < $initial_attempt) {
                $comparison_prompt_delay = 1; // Declined
            }
        }
        /******************** */

        $comparison_echoic_trial = null;

        if ($initial_attempt != null) {
            // Pick latest available echoic probe
            if ($echoic_3 != null) {
                $echoicValue = $echoic_3;
            } elseif ($echoic_2 != null) {
                $echoicValue = $echoic_2;
            } elseif ($echoic_1 != null) {
                $echoicValue = $echoic_1;
            }

            if (isset($echoicValue)) {
                if ($echoicValue == $initial_attempt) {
                    $comparison_echoic_trial = 2; // Same
                } elseif ($echoicValue > $initial_attempt) {
                    $comparison_echoic_trial = 3; // Improved
                } elseif ($echoicValue < $initial_attempt) {
                    $comparison_echoic_trial = 1; // Declined
                }
            }
        } else {
            // No initial attempt; compare echoic 1 to 3 or 2 if available
            /* if ($echoic_1 != null && $echoic_3 != null) {
                if ($echoic_3 == $echoic_1) {
                    $comparison_echoic_trial = 2;
                } elseif ($echoic_3 > $echoic_1) {
                    $comparison_echoic_trial = 3;
                } elseif ($echoic_3 < $echoic_1) {
                    $comparison_echoic_trial = 1;
                }
            } elseif ($echoic_1 != null && $echoic_2 != null) {
                if ($echoic_2 == $echoic_1) {
                    $comparison_echoic_trial = 2;
                } elseif ($echoic_2 > $echoic_1) {
                    $comparison_echoic_trial = 3;
                } elseif ($echoic_2 < $echoic_1) {
                    $comparison_echoic_trial = 1;
                }
            }*/
        }

        // Prepare response data
        $reinforcer_input = normalize_reinforcer_input((string) $reinforcer_input);
        if ($reinforcer_input === '') {
            return $this->respond([
                'success' => 'No',
                'message' => 'Reinforcer can not be empty',
            ]);
        }

        $data = [
            'id' => $mands_id,
            'reinforcer_input' => $reinforcer_input,
            'utterance_input' => $utterance_input,
            'is_peer_manding' => $is_peer_manding,
            'is_eye_contact' => $is_eye_contact,
            'prompt_level' => $prompt_level,
            'mands_error' => $mands_error,
            'initial_attempt_input' => $initial_attempt_input,
            'initial_attempt' => $initial_attempt,
            'prompt_delay_input' => $prompt_delay_input,
            'prompt_delay' => $prompt_delay,
            'echoic_1_input' => $echoic_1_input,
            'echoic_1' => $echoic_1,
            'echoic_2_input' => $echoic_2_input,
            'echoic_2' => $echoic_2,
            'echoic_3_input' => $echoic_3_input,
            'echoic_3' => $echoic_3,
            'comparison_prompt_delay' => $comparison_prompt_delay,
            'comparison_echoic_trial' =>  $comparison_echoic_trial,
            'updated_by' => auth()->user()->id,
        ];
        // print_r($data); die;

        try {
            $this->ensureMasterReinforcer($reinforcer_input);
            $this->ensureClientReinforcer((int) $client_id, $reinforcer_input, (string) $sessionDetail->session_date);


            // $mandsSessionData = new \App\Entities\Mands\MandsSessionData();
            //$mandsSessionData->fill($data);
            $this->mandsSessionDataModel->save($data);
            $updatedRecord = $this->mandsSessionDataModel->find($mands_id);
            $rowHtml = view('ClientSessionsReview/MandsReview/_mands_table_row', ['mand' => $updatedRecord]);


            return $this->respond(['success' => 'Yes', 'message' => 'Mands data saved successfully', 'data' => $rowHtml]);
        } catch (\Exception $e) {
            return $this->respond(['success' => 'No', 'message' => $e->getMessage()]);
        }
    }

    private function ensureMasterReinforcer(string $reinforcerInput): void
    {
        $normalizedKey = strtolower($reinforcerInput);
        $db = \Config\Database::connect();

        $sql = "
            SELECT id
            FROM mands_reinforcer
            WHERE LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, '\t', ' '), '\n', ' '), '\r', ' '), '  ', ' '), '  ', ' '))) = ?
            LIMIT 1
        ";
        $row = $db->query($sql, [$normalizedKey])->getFirstRow();
        if ($row !== null) {
            return;
        }

        $mr = [
            'name' => $reinforcerInput,
            'created_by' => auth()->user()->id,
            'updated_by' => null,
            'updated_at' => null,
        ];

        $mandsReinforcer = new \App\Entities\Mands\MandsReinforcer();
        $mandsReinforcer->fill($mr);
        $this->mandsReinforcerModel->save($mandsReinforcer);
    }

    private function ensureClientReinforcer(int $clientId, string $reinforcerInput, string $introducedAt): void
    {
        if ($clientId <= 0 || $reinforcerInput === '' || $introducedAt === '') {
            return;
        }

        $normalizedKey = strtolower($reinforcerInput);
        $db = \Config\Database::connect();

        $sql = "
            SELECT id
            FROM client_mands_reinforcer
            WHERE client_id = ?
              AND LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(reinforcer_name, '\t', ' '), '\n', ' '), '\r', ' '), '  ', ' '), '  ', ' '))) = ?
            LIMIT 1
        ";
        $row = $db->query($sql, [$clientId, $normalizedKey])->getFirstRow();
        if ($row !== null) {
            return;
        }

        $this->clientMandsReinforcerModel->insert([
            'client_id' => $clientId,
            'reinforcer_name' => $reinforcerInput,
            'introduced_at' => $introducedAt,
            'created_by' => auth()->user()->id,
        ]);
    }

    public function deleteMandsRecord()
    {
        $mands_id = $this->request->getPost('mands_id');
        $mandsRecord = $this->mandsSessionDataModel->asObject()->find($mands_id);
        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($mandsRecord->session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session not exist.',
            ]);
        }

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($sessionDetail->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => 'No',
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }

        try {

            $this->mandsSessionDataModel->where('id', $mands_id)->delete();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Deletion failed: ' . $e->getMessage()
            ]);
        }
    }


    /********************************************************************************************************************************** */
    // Session Duration management (Teaching and Mands)

    public function createDuration()
    {
        $validationData = [
            'session_id' => $this->request->getPost('session_id'),
            'duration_type' => $this->request->getPost('duration_type'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        $validationRules = [
            'session_id' => [
                'label' => 'Session',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Session required.'
                ]
            ],
            'duration_type' => [
                'label' => 'Type',
                'rules' => 'required|in_list[teaching,mands]',
                'errors' => [
                    'required' => 'Type required.',
                    'in_list' => 'Type must be either teaching or mands.',
                ]
            ],
            'start_time' => [
                'label' => 'Start time',
                'rules' => 'required|valid_date[H:i:s]',
                'errors' => [
                    'required' => 'Start time required.',
                    'valid_date' => 'Enter valid start time [H:i:s].'
                ]
            ],
            'end_time' => [
                'label' => 'End Time',
                'rules' => 'required|valid_date[H:i:s]|compareTimes[start_time]',
                'errors' => [
                    'required' => 'End time is required.',
                    'valid_date' => 'Enter a valid end time in the format [H:i:s].',
                    'compareTimes' => 'End time must be greater than start time.'
                ]
            ],
        ];

        // Run validation
        if (!$this->validateData($validationData, $validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $this->validator->listErrors('custom_list'),
            ]);
        }

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($validationData['session_id']);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session not exist.',
            ]);
        }

        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($sessionDetail->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }

        // ✅ Check if duration is within session time
        $sessionCheck = $this->dailySessionModel->isWithinSessionTime(
            $validationData['session_id'],
            $validationData['start_time'],
            $validationData['end_time']
        );

        if (!$sessionCheck['status']) {
            return $this->response->setJSON(['success' => false, 'message' => $sessionCheck['message']]);
        }

        // ✅ Check if Mands Data Exists before allowing Mands Duration insertion
        if ($validationData['duration_type'] == 'mands') {
            $mandsDataExists = $this->mandsSessionDataModel->where('session_id', $validationData['session_id'])->countAllResults();

            if ($mandsDataExists == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot add Mands Duration because no Mands session data exists for this session.'
                ]);
            }
        }

        // ✅ Select correct model
        $model = ($validationData['duration_type'] == 'teaching')
            ? $this->sessionDurationModel
            : $this->sessionMandDurationModel;

        // ✅ Check for overlaps
        $overlapCheck = $model->hasOverlap(
            $validationData['session_id'],
            $validationData['start_time'],
            $validationData['end_time']
        );

        if (!$overlapCheck['status']) {
            return $this->response->setJSON(['success' => false, 'message' => $overlapCheck['message']]);
        }

        // ✅ Get session details
        $session = $this->dailySessionModel->getSessionByID($validationData['session_id']);

        $dataForInsertion = [
            'session_id' => $session->id,
            'session_date' => $session->session_date,
            'client_id' => $session->client_id,
            'start_time' => $validationData['start_time'],
            'end_time' => $validationData['end_time'],
        ];

        // ✅ Insert record
        $model->insert($dataForInsertion);
        $new_record = $model->where('id', $model->getInsertID())->first();

        if ($new_record) {
            return $this->response->setJSON(['success' => true, 'record' => $new_record, 'message' => 'Duration added successfully.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to add duration.']);
    }

    public function getDuration()
    {
        $id = $this->request->getPost('id');
        $duration_type = $this->request->getPost('duration_type');
        $record = null;

        if ($duration_type == 'teaching') {
            $record = $this->sessionDurationModel->where('id', $id)->first();
        } elseif ($duration_type == 'mands') {
            $record = $this->sessionMandDurationModel->where('id', $id)->first();
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid duration type.']);
        }

        if ($record) {
            return $this->response->setJSON(['success' => true, 'record' => $record]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Record not found.']);
    }


    public function updateDuration()
    {
        $validationData = [
            'id' => $this->request->getPost('id'),
            'duration_type' => $this->request->getPost('duration_type'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        $validationRules = [
            'id' => [
                'label' => 'Duration',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Duration ID required.'
                ]
            ],
            'duration_type' => [
                'label' => 'Type',
                'rules' => 'required|in_list[teaching,mands]',
                'errors' => [
                    'required' => 'Type required.',
                    'in_list' => 'Type must be either teaching or mands.',
                ]
            ],
            'start_time' => [
                'label' => 'Start time',
                'rules' => 'required|valid_date[H:i:s]',
                'errors' => [
                    'required' => 'Start time required.',
                    'valid_date' => 'Enter valid start time [H:i:s].'
                ]
            ],
            'end_time' => [
                'label' => 'End Time',
                'rules' => 'required|valid_date[H:i:s]|compareTimes[start_time]',
                'errors' => [
                    'required' => 'End time is required.',
                    'valid_date' => 'Enter a valid end time in the format [H:i:s].',
                    'compareTimes' => 'End time must be greater than start time.'
                ]
            ],
        ];

        // Run validation
        if (!$this->validateData($validationData, $validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $this->validator->listErrors('custom_list'),
            ]);
        }

        // ✅ Get correct model
        $model = ($validationData['duration_type'] == 'teaching')
            ? $this->sessionDurationModel
            : $this->sessionMandDurationModel;

        // ✅ Fetch existing duration record
        $existingDuration = $model->where('id', $validationData['id'])->first();

        if (!$existingDuration) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Duration record not found.',
            ]);
        }


        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($existingDuration->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }

        // ✅ Ensure new times are within session range
        $sessionCheck = $this->dailySessionModel->isWithinSessionTime(
            $existingDuration->session_id,
            $validationData['start_time'],
            $validationData['end_time']
        );

        if (!$sessionCheck['status']) {
            return $this->response->setJSON(['success' => false, 'message' => $sessionCheck['message']]);
        }

        // ✅ Check if Mands Data Exists before allowing Mands Duration updates
        if ($validationData['duration_type'] == 'mands') {
            $mandsDataExists = $this->mandsSessionDataModel
                ->where('session_id', $existingDuration->session_id)
                ->countAllResults();

            if ($mandsDataExists == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot update Mands Duration because no Mands session data exists for this session.'
                ]);
            }
        }

        // ✅ Ensure updated time does not overlap with other durations
        $overlapCheck = $model->hasOverlap(
            $existingDuration->session_id,
            $validationData['start_time'],
            $validationData['end_time'],
            $validationData['id'] // Pass ID to exclude itself in check
        );

        if (!$overlapCheck['status']) {
            return $this->response->setJSON(['success' => false, 'message' => $overlapCheck['message']]);
        }

        // ✅ Update record
        $dataForUpdate = [
            'id' => $validationData['id'],
            'start_time' => $validationData['start_time'],
            'end_time' => $validationData['end_time'],
        ];

        $model->save($dataForUpdate);
        $updated_record = $model->where('id', $validationData['id'])->first();

        if ($updated_record) {
            return $this->response->setJSON([
                'success' => true,
                'updated_record' => $updated_record,
                'message' => 'Duration updated successfully.'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update duration.']);
    }


    public function deleteDuration()
    {
        $validationData = [
            'id' => $this->request->getPost('id'),
            'duration_type' => $this->request->getPost('duration_type'),
        ];

        $validationRules = [
            'id' => [
                'label' => 'Duration',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Session required.'
                ]
            ],
            'duration_type' => [
                'label' => 'Type',
                'rules' => 'required|in_list[teaching,mands]',
                'errors' => [
                    'required' => 'Type required.',
                    'in_list' => 'Type must be either teaching or mands.',
                ]
            ]
        ];

        // Run validation
        if (!$this->validateData($validationData, $validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $this->validator->listErrors('custom_list'),
            ]);
        }

        $deleted = null;
        // ✅ Get correct model
        $model = ($validationData['duration_type'] == 'teaching')
            ? $this->sessionDurationModel
            : $this->sessionMandDurationModel;

        // ✅ Fetch existing duration record
        $existingDuration = $model->where('id', $validationData['id'])->first();

        if (!$existingDuration) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Duration record not found.',
            ]);
        }


        // Check User Permissions
        if (!auth()->user()->can('sessions.review.modification')) {
            // User does not have permission, so we check for past processed data

            // Get the number of days between session date and current date
            $daysDifference = getDaysDifference($existingDuration->session_date);

            // Check if days difference exceeds allowed limit
            if ($daysDifference > SESSION_PROCESSING_RESOLUTION_DAYS) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You are not authorized to enter data in the past while the session date exceeds the allowed ' . SESSION_PROCESSING_RESOLUTION_DAYS . ' days limit.',
                ]);
            }
        }
        $deleted = $model->where('id', $validationData['id'])->delete();


        if ($deleted) {
            return $this->response->setJSON(['success' => true, 'message' => 'Duration deleted successfully.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete duration.']);
    }

    /********************************************************************************************************************************** */
}
