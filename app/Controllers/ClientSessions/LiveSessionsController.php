<?php

namespace App\Controllers\ClientSessions;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\Auth\UserModel;

use App\Models\ClientSessions\DailySessionModel;


use App\Models\ClientSessions\SessionDurationModel;
use App\Models\ClientSessions\SessionPBDurationModel;
use App\Models\ClientSessions\SessionMandDurationModel;

use App\Models\ClientProgram\ClientDomainModel;
use App\Models\ClientProgram\ClientGoalModel;
use App\Models\ClientProgram\ClientTargetModel;
use App\Models\ClientProgram\ClientProbeSetModel;

use App\Models\ClientProgram\ClientProgramModel;

use App\Models\ClientDataSheet\ClientDataSheetModel;

use App\Models\ClientSessions\DailySessionDataCollectionModel;
use App\Models\ClientSessions\DailySessionDataProcessedModel;

use App\Models\ClientProblemBehavior\DailySessionsPbRecordsModel;
use App\Models\ClientProblemBehavior\DropdownItemModel;
use App\Models\ClientProblemBehavior\ClientAbcItemModel;

use App\Models\Mands\MandsReinforcerModel;
use App\Models\Mands\MandsSessionDataModel;
use App\Models\Mands\ClientMandsReinforcerModel;

//use function PHPUnit\Framework\isNull;

class LiveSessionsController extends AdminController
{
    use ResponseTrait;

    protected $dailySessionModel;

    protected $clientModel;
    protected $userModel;

    protected $sessionDurationModel;
    protected $sessionPBDurationModel;

    protected $clientDomainModel;
    protected $clientGoalModel;
    protected $clientTargetModel;
    protected $clientProbeSetModel;

    protected $clientProgramModel;
    protected $clientDataSheetModel;

    protected $collectionModel;
    protected $processModel;

    protected $pbRecordsModel;
    protected $dropdownItemModel;
    protected $clientAbcItemModel;

    protected $mandsReinforcerModel;
    protected $mandsSessionDataModel;
    protected $clientMandsReinforcerModel;
    protected $sessionMandDurationModel;


    public function __construct()
    {
        // Load your model in the constructor

        $this->dailySessionModel = new DailySessionModel();

        $this->clientModel = new ClientModel();
        $this->userModel = new UserModel();

        $this->sessionDurationModel = new SessionDurationModel();
        $this->sessionPBDurationModel = new SessionPBDurationModel();
        $this->sessionMandDurationModel = new SessionMandDurationModel();

        $this->clientDomainModel = new ClientDomainModel();
        $this->clientGoalModel = new ClientGoalModel();
        $this->clientTargetModel = new ClientTargetModel();
        $this->clientProbeSetModel = new ClientProbeSetModel();

        $this->clientProgramModel = new ClientProgramModel();
        $this->clientDataSheetModel = new ClientDataSheetModel();

        $this->collectionModel = new DailySessionDataCollectionModel();
        $this->processModel = new DailySessionDataProcessedModel();

        $this->pbRecordsModel = new DailySessionsPbRecordsModel();
        $this->dropdownItemModel = new DropdownItemModel();
        $this->clientAbcItemModel = new ClientAbcItemModel();

        $this->mandsReinforcerModel = new MandsReinforcerModel();
        $this->mandsSessionDataModel = new MandsSessionDataModel();
        $this->clientMandsReinforcerModel = new ClientMandsReinforcerModel();
    }
    /******************************************************************** */
    // Clients for Daily Session. only assigned and active clients will display
    /******************************************************************** */
    public function index()
    {

        $clients = $this->clientModel->get_active_client_list();
        $this->page_title = 'Clients for daily sessions';
        return  view(
            'ClientSessionsLive/index',
            [
                'clients' => $clients,
                'page_title' => $this->page_title
            ]
        );
    }

    /******************************************************************** */
    // Target Processing History (placeholder — final view will be wired next)
    /******************************************************************** */
    public function viewTargetHistory()
    {
        $clientId   = $this->request->getPost('client_id');
        $domainId   = $this->request->getPost('domain_id');
        $goalId     = $this->request->getPost('goal_id');
        $targetId   = $this->request->getPost('target_id');
        $probeSetId = $this->request->getPost('client_probe_set_id');

        if (!$clientId || !$targetId) {
            return $this->response->setJSON(['success' => false, 'html' => 'Target data not found.']);
        }

        // Fetch the necessary data
        $clientProgramData = $this->clientDataSheetModel->getSingleTargetDataSheetInformation($clientId, $probeSetId, $domainId, $goalId, $targetId);
        $phaseArray        = $this->clientDataSheetModel->getTargetPhasesArray();

        // ✅ Return the view as an HTML response
        return $this->response->setJSON([
            'success' => true,
            'html'    => view(
                'ClientSessionsLive/targetDetail',
                [
                    'existingData'      => null,
                    'phases'            => $phaseArray,
                    'clientProgramData' => $clientProgramData,
                ]
            ),
        ]);
    }
    /******************************************************************** */
    // Session Data for selected client
    /******************************************************************** */
    public function liveSession($encodedClientId)
    {

        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        // Verify the user is an instructor
        if (!$this->userModel->isInstructor(auth()->user()->id)) {
            // Redirect to the client list if no previous URL exists
            return redirect()->to(base_url('sessions/live'))->with('instructor', 'Only instructors can take sessions. Check with the administrator.');
        }

        // Verify the client belongs to instructor
        if (!$this->userModel->isClientBelongsToInstructor($client_id, auth()->user()->id)) {
            // Redirect to the client list if no previous URL exists
            return redirect()->to(base_url('sessions/live'))->with('instructor', 'Client is not assigned to you. Check with the administrator.');
        }

        // Verify the client has a supervisor
        $clientDefaultSupervisor = $this->userModel->getClientDefaultSupervisor($client_id);
        if (!$clientDefaultSupervisor) {
            // Redirect to the client list if no previous URL exists
            return redirect()->to(base_url('sessions/live'))->with('supervisor', 'No supervisor assigned to <strong>' . $client->first_name . ' ' . $client->last_name . '</strong>');
        }

        $activeSession = $this->dailySessionModel->getClientActiveSessionForGivenDate(currentDate(), $client_id);

        if ($this->request->getMethod() == 'POST') {
            // Handle POST request (continue or start new session)
            $action = $this->request->getPost('action');

            if ($action == 'continue') {
                return $this->activeLiveSession($client_id);
            } elseif ($action == 'new') {
                $currentTime = currentDate('H:i:s');
                $isClientHasConflict = $this->dailySessionModel->hasClientConflict($activeSession->id, $activeSession->client_id, $activeSession->session_date, $activeSession->start_time, $currentTime);
                if ($isClientHasConflict) {
                    // Redirect to the client list if no previous URL exists
                    return redirect()->to(base_url('sessions/live'))->with('error', 'Client <strong>' . $client->first_name . ' ' . $client->last_name . '</strong> has time conflict. visit completed session and end manually.');
                }
                $isInstructorHasConflict = $this->dailySessionModel->hasInstructorConflict($activeSession->id, $activeSession->instructor_id, $activeSession->session_date, $activeSession->start_time, $currentTime);
                if ($isInstructorHasConflict) {
                    // Redirect to the client list if no previous URL exists
                    return redirect()->to(base_url('sessions/live'))->with('instructor', 'Instructor has time conflict. visit completed session section and end manually.');
                }
                $result = $this->endSessionManually($activeSession->id);
                if ($result['success']) {
                    return $this->newLiveSession($client_id);
                } else {
                    return redirect()->to(base_url('sessions/live'))->with('error', $result['message']);
                }
            }
        }
        // Handle GET request (initial or refresh)
        if ($activeSession) {
            // Show choice screen for active session
            $this->page_title = 'Client Active Session Detail';
            return view('ClientSessionsLive/active_session_detail', [
                'activeSession' => $activeSession,
                'page_title' => $this->page_title
            ]);
        }

        // No active session, start a new session
        return $this->newLiveSession($client_id);
    }

    // This method is responsible to show live session screen either it is active or not
    private function activeLiveSession($client_id)
    {
        $client = $this->clientModel->find($client_id);
        $instructor = null;
        $clientDefaultSupervisor = null;
        // Get active session detail for client for given date, by therapist
        $sessionDetail = $this->dailySessionModel->getClientActiveSessionForGivenDate(currentDate(), $client_id);
        if (!isset($sessionDetail)) {
            return redirect()->to(base_url('sessions/live'))->with('error', 'There is no active session exist for selected client.');
        }

        if (isset($sessionDetail)) {
            $users = auth()->getProvider();
            $instructor = $users->findById($sessionDetail->instructor_id);
            $clientDefaultSupervisor = $users->findById($sessionDetail->supervisor_id);
        }

        $sessionTimer = null;
        if (isset($sessionDetail)) {
            $sessionTimer = $this->sessionDurationModel->where(['session_id' => $sessionDetail->id, 'end_time' => NULL])->find();
        }

        $teachingDuration = '00:00:00';
        if (isset($sessionDetail)) {
            $teachingDuration = $this->sessionDurationModel->getTeachingDuration($sessionDetail->id);
        }

        $pbTimer = null;
        if (isset($sessionDetail)) {
            $pbTimer = $this->sessionPBDurationModel->where(['session_id' => $sessionDetail->id, 'end_time' => NULL])->find();
        }


        $pbDuration = '00:00:00';
        if (isset($sessionDetail)) {
            $pbDuration = $this->sessionPBDurationModel->getPBDuration($sessionDetail->id);
        }

        $isMandActive = false;
        if (isset($sessionDetail)) {
            $isMandActive = $this->sessionMandDurationModel->hasEmptyEndTime($sessionDetail->id);
        }

        // Get domain and goals for given client for session
        $program = $this->clientProgramModel->getClientProgramForLiveSession($client_id);

        $this->page_title = 'Daily Sessions Target List';
        $mandsCount = $this->mandsSessionDataModel
            ->where('client_id', $client_id)
            ->where('session_id', $sessionDetail->id)
            ->countAllResults();

        return  view(
            'ClientSessionsLive/live_session',
            [
                'session' => $sessionDetail,
                'isSession' => isset($sessionDetail) ? true : false,
                'sessionTimer' => $sessionTimer,
                'isSessionTimer' => (isset($sessionTimer) && !empty($sessionTimer)) ? true : false,
                'teachingDuration' => $teachingDuration,
                'pbTimer' => $pbTimer,
                'isPBTimer' => (isset($pbTimer) && !empty($pbTimer)) ? true : false,
                'pbDuration' => $pbDuration,
                'isMandActive' => $isMandActive,
                'client' =>  $client,
                'instructor' => $instructor,
                'supervisor' =>   $clientDefaultSupervisor,
                'program' =>  $program,
                'mandsCount' => $mandsCount,
                'page_title' => $this->page_title
            ]
        );
    }
    private function newLiveSession($client_id)
    {
        $client = $this->clientModel->find($client_id);
        $instructor = auth()->user();

        if (!$this->userModel->isInstructor(auth()->user()->id)) {
            // Redirect to the client list if no previous URL exists
            return redirect()->to(base_url('sessions/live'))->with('instructor', 'Only instructors can take sessions. Check with the administrator.');
        }

        // supervisor list linked with client
        $clientDefaultSupervisor = $this->userModel->getClientDefaultSupervisor($client_id);
        if (!$clientDefaultSupervisor) {
            return redirect()->to(base_url('sessions/live'))->with('supervisor', 'No supervisor assigned to <strong>' . $client->first_name . ' ' . $client->last_name . '</strong>');
        }

        // Get active session detail for client for given date, by therapist
        $sessionDetail = $this->dailySessionModel->getClientActiveSessionForGivenDate(currentDate(), $client_id);

        // Get active session instructor detail if session is already active
        if (isset($sessionDetail)) {
            return redirect()->to(base_url('sessions/live'))->with('error', 'There is an active session exist for <strong>' . $client->first_name . ' ' . $client->last_name . '</strong>. End active session and continue for new session ');
        }

        $sessionTimer = null;
        $teachingDuration = '00:00:00';
        $pbTimer = null;
        $pbDuration = '00:00:00';
        $isMandActive = false;

        // Get domain and goals for given client for session
        $program = $this->clientProgramModel->getClientProgramForLiveSession($client_id);

        $this->page_title = 'Daily Sessions Target List';

        return  view(
            'ClientSessionsLive/live_session',
            [
                'session' => $sessionDetail,
                'isSession' => false,
                'sessionTimer' => $sessionTimer,
                'isSessionTimer' => false,
                'teachingDuration' => $teachingDuration,
                'pbTimer' => $pbTimer,
                'isPBTimer' => false,
                'pbDuration' => $pbDuration,
                'isMandActive' => $isMandActive,
                'client' =>  $client,
                'instructor' => $instructor,
                'supervisor' =>   $clientDefaultSupervisor,
                'program' =>  $program,
                'page_title' => $this->page_title
            ]
        );
    }
    /******************************************************************** */
    public function startSession()
    {
        $clientId = $this->request->getPost('client_id');

        /*************************** */
        if (!$this->userModel->isInstructor(auth()->user()->id)) {
            // Redirect to the client list if no previous URL exists
            $response =  $this->getResponseObject('error', 'Error', 'Only instructors can take sessions. Check with the administrator.', [],  []);
            return $this->response->setJSON($response);
        }

        // Verify the client belongs to instructor
        if (!$this->userModel->isClientBelongsToInstructor($clientId, auth()->user()->id)) {
            $response =  $this->getResponseObject('error', 'Error', 'Client is not assigned to you. Check with the administrator', [],  []);
            return $this->response->setJSON($response);
        }

        /*************************** */
        $clientDefaultSupervisor = $this->userModel->getClientDefaultSupervisor($clientId);

        if (!isset($clientDefaultSupervisor)) {
            $response =  $this->getResponseObject('error', 'Error', 'The client is currently without a supervisor. The Program Director should be contacted to assign a supervisor to the client', [],  []);
            return $this->response->setJSON($response);
        }

        /*************************** */
        // Check if session already activated.
        $sessionDetail = $this->dailySessionModel->getClientActiveSessionForGivenDate(currentDate(), $clientId);
        if (isset($sessionDetail)) {
            $response =  $this->getResponseObject('error', 'Error', 'Session already started. Refresh your screen. if you are facing this issue continuously then contact system administrator ', [],  []);
            return $this->response->setJSON($response);
        }

        // Save Session 
        $currentTime = currentDate('H:i:s');
        $currentDate = currentDate();

        // Check time Conflict for client and instructor.
        $isClientHasConflict = $this->dailySessionModel->hasClientStartTimeConflict($clientId, $currentDate,  $currentTime);
        if ($isClientHasConflict) {
            $response =  $this->getResponseObject('error', 'Error', 'Client has time conflict. visit completed session section.', [],  []);
            return $this->response->setJSON($response);
        }
        $isInstructorHasConflict = $this->dailySessionModel->hasInstructorStartTimeConflict(auth()->user()->id, $currentDate,  $currentTime);
        if ($isInstructorHasConflict) {
            $response =  $this->getResponseObject('error', 'Error', 'Instructor has time conflict. visit completed session section.', [],  []);
            return $this->response->setJSON($response);
        }



        $sessionData = [
            'client_id' =>  $clientId,
            'instructor_id' =>   auth()->user()->id,
            'supervisor_id' =>  $clientDefaultSupervisor->id,
            'session_date' =>  $currentDate,
            'start_time' =>  $currentTime,
            'status' =>  1,
            'created_by' =>  auth()->user()->id,
        ];
        $this->dailySessionModel->save($sessionData);
        $sessionId = $this->dailySessionModel->getInsertID();

        $sessionTimerData = [
            'session_id' =>  $sessionId,
            'session_date' =>  $currentDate,
            'client_id' =>  $clientId,
            'start_time' =>  $currentTime,
        ];
        $this->sessionDurationModel->save($sessionTimerData);

        $response =  $this->getResponseObject('success', 'Success', 'Record created Successfully', [],  $sessionTimerData);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function endSession()
    {
        $clientId = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');

        // Check if session activated.
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (isset($sessionDetail)) {

            $currentTime = currentDate('H:i:s');
            $isClientHasConflict = $this->dailySessionModel->hasClientConflict($sessionDetail->id, $sessionDetail->client_id, $sessionDetail->session_date, $sessionDetail->start_time, $currentTime);
            if ($isClientHasConflict) {
                $response =  $this->getResponseObject('error', 'Error', 'Client has time conflict. visit completed session section and end manually. ', [],  []);
                return $this->response->setJSON($response);
            }
            $isInstructorHasConflict = $this->dailySessionModel->hasInstructorConflict($sessionDetail->id, $sessionDetail->instructor_id, $sessionDetail->session_date, $sessionDetail->start_time, $currentTime);
            if ($isInstructorHasConflict) {
                $response =  $this->getResponseObject('error', 'Error', 'Instructor has time conflict. visit completed session section and end manually. ', [],  []);
                return $this->response->setJSON($response);
            }

            $sessionData = [
                'id' =>  $sessionDetail->id,
                'end_time' =>  $currentTime,
                'status' =>  2,
                'updated_by' =>  auth()->user()->id,
                'updated_at' =>  currentDate('Y-m-d H:i:s'),
            ];
            $this->dailySessionModel->save($sessionData);
            $sessionTimerData = [
                'session_id' =>  $sessionDetail->id,
                'end_time' =>  null,
            ];
            $this->sessionDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            //$this->sessionPBDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            //$this->sessionMandDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();

            $response =  $this->getResponseObject('success', 'Success', 'Session closed successfully. ', [],  []);
            return $this->response->setJSON($response);
        }

        $response =  $this->getResponseObject('error', 'Error', 'Session is not started yet or already closed. Refresh your screen. if you are facing this issue continuously then contact system administrator ', [],  []);
        return $this->response->setJSON($response);
    }
    private function endSessionManually($session_id)
    {
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        $currentTime = currentDate('H:i:s');
        if (isset($sessionDetail)) {

            $isClientHasConflict = $this->dailySessionModel->hasClientConflict($sessionDetail->id, $sessionDetail->client_id, $sessionDetail->session_date, $sessionDetail->start_time, $currentTime);
            if ($isClientHasConflict) {
                return [
                    'success' => false,
                    'message' => 'Client has time conflict. visit completed session section and end manually'
                ];
            }
            $isInstructorHasConflict = $this->dailySessionModel->hasInstructorConflict($sessionDetail->id, $sessionDetail->instructor_id, $sessionDetail->session_date, $sessionDetail->start_time, $currentTime);
            if ($isInstructorHasConflict) {
                return [
                    'success' => false,
                    'message' => 'Instructor has time conflict. visit completed session section and end manually'
                ];
            }

            $sessionData = [
                'id' =>  $sessionDetail->id,
                'end_time' =>  $currentTime,
                'status' =>  2,
                'updated_by' =>  auth()->user()->id,
                'updated_at' =>  currentDate('Y-m-d H:i:s'),
            ];

            $this->dailySessionModel->save($sessionData);
            /* $sessionTimerData = [
                'session_id' =>  $sessionDetail->id,
                'end_time' =>  null,
            ];
            $this->sessionDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            $this->sessionPBDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            $this->sessionMandDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();*/

            return [
                'success' => true,
                'message' => ''
            ];
        }

        return [
            'success' => false,
            'message' => 'Session not founded. contact administrator'
        ];
    }
    /******************************************************************** */
    public function updateDuration()
    {
        $clientId = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');
        $action = $this->request->getPost('action');

        // Check if session activated.
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (isset($sessionDetail)) {
            $currentTime = currentDate('H:i:s');
            if ($action == 'start') {
                $sessionTimerData = [
                    'session_id' =>  $sessionDetail->id,
                    'session_date' => $sessionDetail->session_date,
                    'client_id' =>  $clientId,
                    'start_time' =>  $currentTime,

                ];
                $this->sessionDurationModel->save($sessionTimerData);
            }
            if ($action == 'stop') {
                $sessionTimerData = [
                    'session_id' =>  $sessionDetail->id,
                    'end_time' =>  null,
                ];
                $this->sessionDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            }


            $response =  $this->getResponseObject('success', 'Session', 'Stopped successfully. ', [],  []);
            return $this->response->setJSON($response);
        }

        $response =  $this->getResponseObject('info', '', 'Session not started. Start the session first to proceed.', [],  []);
        return $this->response->setJSON($response);
    }
    public function updatePBDuration()
    {
        $clientId = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');
        $action = $this->request->getPost('action');

        // Check if session activated.
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (isset($sessionDetail)) {
            $currentTime = currentDate('H:i:s');
            if ($action == 'start') {
                $sessionTimerData = [
                    'session_id' =>  $sessionDetail->id,
                    'session_date' => $sessionDetail->session_date,
                    'client_id' =>  $clientId,
                    'start_time' =>  $currentTime,
                ];
                $this->sessionPBDurationModel->save($sessionTimerData);
                $pb_timer_id = $this->sessionPBDurationModel->getInsertID(); // Capture the newly inserted ID

                $response = [
                    'status' => 'success',
                    'message' => 'Problem behavior started successfully.',
                    'pb_timer_id' => $pb_timer_id, // Return the new pb_timer_id
                ];
                return $this->response->setJSON($response);
            }
            if ($action == 'stop') {
                $sessionTimerData = [
                    'session_id' =>  $sessionDetail->id,
                    'end_time' =>  null,
                ];
                $this->sessionPBDurationModel->where($sessionTimerData)->set(['end_time' => $currentTime])->update();
            }


            $response =  $this->getResponseObject('success', 'Session', 'Stopped successfully. ', [],  []);
            return $this->response->setJSON($response);
        }

        $response =  $this->getResponseObject('info', '', 'Session not started. Start the session first to proceed. ', [],  []);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
 
    public function get_probe_sets()
    {
        $client_id = $this->request->getPost('client_id');
        $goal_id = $this->request->getPost('goal_id');

        // Fetch probe sets linked to the client and goal
        $probeSets = $this->clientProgramModel->get_probe_sets($client_id, $goal_id);

        return $this->response->setJSON($probeSets);
    }
 
    public function get_target_list()
    {
        $client_id   = $this->request->getPost('client_id');
        $domain_id   = $this->request->getPost('domain_id');
        $goal_id     = $this->request->getPost('goal_id');
        $session_id  = $this->request->getPost('session_id');

        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return 'Session is not active.';
        }

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
        return view('ClientSessionsLive/target_list', $data);
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
            $target['existingEntry'] = $this->collectionModel->getExistingEntry($client_id, $target['target_id'], $active_probe_set->probe_set_id, $sessionDetail->id);
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
        return view('ClientSessionsLive/target_list_percentage_probe_yes_no', $data);
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
        return view('ClientSessionsLive/target_list_stimulus_probe', $data);
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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
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
            'created_at' => currentDate('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
        ];

        // Save Probe Data using the model method, passing session date along
        if ($this->collectionModel->checkForDuplicateEntry($client_id, $target_id, $client_probe_set_id, $sessionDate)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Duplication: Target data already collected for today sessions.',
            ]);
        } else {
            $this->collectionModel->insert($rowData);
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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
        }

        // Check if input transition exist

        /* if ($transition == '') {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Transition input required.',
            ]);
        }*/


        $answer = strtoupper($probeData[0]['result']);


        $existingEntry = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $sessionDetail->id);

        if ($existingEntry) {
            // Entry exists — update
            $updatedCollectedData = $this->updateCollectedDataJsonWithNewEntry(
                $existingEntry->collected_data,
                $transition,
                $answer // assuming 1 result per save
            );

            $this->collectionModel->update($existingEntry->id, [
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
            $this->collectionModel->insert($rowData);

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
    /** End of Target Rules applied on saving targets */
    /***************************************************************************************************** */
    public function get_mands_form()
    {

        $client_id = $this->request->getPost('client_id');
        $isMandActive = $this->request->getPost('isMandActive');
        $session_id = $this->request->getPost('session_id');


        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return 'Session is not active.';
        }


        $topReinforcer = $this->mandsSessionDataModel->getTopReinforcerInputs($client_id);
        //print_r($target); die;
        $data =  [
            'client_id' => $client_id,
            'session_id' => $session_id,
            'topReinforcer' => $topReinforcer,
            'isMandActive' => $isMandActive,
            'session_date' => currentDate(),
        ];
        return view('ClientSessionsLive/mands', $data);
    }

    public function get_mands_session_list()
    {
        $client_id = (int) $this->request->getPost('client_id');
        $session_id = (int) $this->request->getPost('session_id');

        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail) || (int) $sessionDetail->client_id !== $client_id) {
            return 'Session is not active.';
        }

        $mandsData = $this->mandsSessionDataModel->getDailyDataBySession($client_id, $session_id);
        return view('ClientSessionsLive/mands_list_rows', ['mandsData' => $mandsData]);
    }

    /*************************************************************** */
    public function save_mands_form()
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
                'message' => 'Session is not started yet or completed.',
            ]);
        }



        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
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
            // if no intial attempts do not compare
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

            $mandsCount = $this->mandsSessionDataModel
                ->where('client_id', $client_id)
                ->where('session_id', $session_id)
                ->countAllResults();

            return $this->respond(['success' => 'Yes', 'mandsCount' => $mandsCount, 'message' => 'Mands data saved successfully']);
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

    public function updateMandsDuration()
    {
        $clientId = $this->request->getPost('client_id');
        $action = $this->request->getPost('action');
        $session_id = $this->request->getPost('session_id');

        // Check if session activated.
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (isset($sessionDetail)) {
            $mandsCount = $this->mandsSessionDataModel
                ->where('client_id', $clientId)
                ->where('session_id', $session_id)
                ->countAllResults();
            $currentTime = currentDate('H:i:s');
            if ($action == 'start') {
                $MandsDurationData = [
                    'session_id' =>  $sessionDetail->id,
                    'session_date' => $sessionDetail->session_date,
                    'client_id' =>  $clientId,
                    'start_time' =>  $currentTime,
                ];
                $this->sessionMandDurationModel->save($MandsDurationData);
                //$newId = $this->sessionMandDurationModel->getInsertID(); // Capture the newly inserted ID

                $response = [
                    'status' => 'success',
                    'mandsCount' => $mandsCount,
                    'message' => 'Mands started successfully.',
                ];
                return $this->response->setJSON($response);
            }
            if ($action == 'stop') {
                $MandsDurationData = [
                    'session_id' =>  $sessionDetail->id,
                    'end_time' =>  null,
                ];
                $this->sessionMandDurationModel->where($MandsDurationData)->set(['end_time' => $currentTime])->update();
                $response = [
                    'status' => 'success',
                    'mandsCount' => $mandsCount,
                    'message' => 'Mands stopped successfully.',
                ];
                return $this->response->setJSON($response);
            }
        }

        $response =  $this->getResponseObject('info', '', 'Session not started. Start the session first to proceed.. ', [],  []);
        return $this->response->setJSON($response);
    }


    /***************************************************************************************************** */
    /** Problem Behavior Management */
    /***************************************************************************************************** */

    // Method to get the list of problem behaviors
    public function getPbRecordList()
    {
        $session_id = $this->request->getPost('session_id');
        $client_id = $this->request->getPost('client_id');

        if ($session_id == '') {
            return 'Session is not started yet or completed.';
        }
        $pb_records = $this->sessionPBDurationModel->where('session_id', $session_id)
            ->where('client_id', $client_id)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('ClientSessionsLive/pb_record_list', [
            'pb_records' => $pb_records
        ]);
    }

    // Method to load the PB form for adding/editing a record
    public function getPbRecordForm()
    {
        $pb_timer_id = $this->request->getPost('pb_timer_id');
        $session_id = $this->request->getPost('session_id');
        $client_id = $this->request->getPost('client_id');

        // Fetch PB duration data
        $pb_duration = $this->sessionPBDurationModel->find($pb_timer_id);

        if (!$pb_duration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior duration not found.'
            ]);
        }

        // Resolve dropdown options: client-specific first, then master fallback
        $antecedents = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $client_id, 'antecedent'));
        $behaviors = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $client_id, 'behavior'));
        $consequences = array_map(static fn($v) => ['value' => $v], $this->clientAbcItemModel->getResolvedValues((int) $client_id, 'consequence'));

        // Check if a PB record exists for this pb_timer_id
        $pb_record = $this->pbRecordsModel->where('pb_timer_id', $pb_timer_id)->first();

        return view('ClientSessionsLive/pb_record_form', [
            'pb_record' => $pb_record,
            'pb_duration' => $pb_duration,
            'session_id' => $session_id,
            'client_id' => $client_id,
            'pb_timer_id' => $pb_timer_id,
            'antecedents' => $antecedents,
            'behaviors' => $behaviors,
            'consequences' => $consequences
        ]);
    }

    // Method to save the problem behavior record
    public function saveProblemBehaviorRecord()
    {
        $client_id = $this->request->getPost('client_id');
        $session_id = $this->request->getPost('session_id');

        // Check if session is active
        $sessionDetail = $this->dailySessionModel->getSessionByID($session_id);
        if (!isset($sessionDetail)) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session is not active.',
            ]);
        }

        // Check if session is active for another therapist
        /*$isSessionActiveForAnotherTherapist = $this->dailySessionModel->isSessionActiveForAnotherTherapist(currentDate(), $client_id, auth()->user()->id);
        if ($isSessionActiveForAnotherTherapist) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Session is in progress with another Instructor.',
            ]);
        }*/
        // Validation Rules
        $validationRules = [
            'pb_timer_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The problem behavior timer ID is required.'
                ]
            ],
            'client_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The client ID is required.'
                ]
            ],
            'session_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The session ID is required.'
                ]
            ],
            'antecedent' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The antecedent field is required.'
                ]
            ],
            'consequence' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The consequence field is required.'
                ]
            ],
            'behavior' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'At least one behavior must be selected.'
                ]
            ],
            'antecedent_other' => [
                'rules' => $this->request->getPost('antecedent') == 'Other' ? 'required' : 'permit_empty',
                'errors' => [
                    'required' => 'Please specify the "Other" antecedent.'
                ]
            ],
            'consequence_other' => [
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


        $data = [
            'pb_timer_id' => $this->request->getPost('pb_timer_id'),
            'client_id' => $this->request->getPost('client_id'),
            'session_id' => $this->request->getPost('session_id'),
            'antecedent' => $this->request->getPost('antecedent') == 'Other' ? trim($this->request->getPost('antecedent_other')) : trim($this->request->getPost('antecedent')),
            'consequence' => $this->request->getPost('consequence') == 'Other' ? trim($this->request->getPost('consequence_other')) : trim($this->request->getPost('consequence')),
            'behavior' => $this->getBehaviorJson(),
            'abc_comments' => $this->request->getPost('abc_comments'),
        ];

        // Save or update the record
        $existingRecord = $this->pbRecordsModel->where('pb_timer_id', $data['pb_timer_id'])->first();
        if ($existingRecord) {
            $this->pbRecordsModel->update($existingRecord['id'], $data);
        } else {
            $this->pbRecordsModel->insert($data);
        }

        return $this->response->setJSON([
            'success' => 'Yes',
            'message' => 'Problem behavior record saved successfully.'
        ]);
    }

    // Helper function to convert behaviors and intensities into JSON format
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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
        }
        // ✅ 2. Ensure collection row exists or create

        $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);


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
            $this->collectionModel->insert([
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
            $collection =  $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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

        $this->collectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at'     => currentDate('Y-m-d H:i:s'),
            'updated_by'     => auth()->user()->id,
        ]);

        return $this->respond([
            'success' => 'Yes',
            'message' => 'Baseline data saved.',
            'updated_result' => $baselineSummary
        ]);
    }
    /*private function compileBaselineSummary($client_id, $target_id, $session_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $all = $stepSessionModel->where([
            'client_id' => $client_id,
            'target_id' => $target_id,
            'session_id' => $session_id,
            'phase_id'  => 1,
            'method'    => 'baseline'
        ])->findAll();

        $stepAttempts = [];
        foreach ($all as $entry) {
            $step_id = $entry['step_id'];
            if (!isset($stepAttempts[$step_id])) $stepAttempts[$step_id] = [];
            $stepAttempts[$step_id][] = $entry['input_result'];
        }

        $mastered = 0;
        $total    = count($stepAttempts);
        $stepStats = [];

        foreach ($stepAttempts as $step_id => $results) {
            $indCount = count(array_filter($results, fn($r) => $r == 'IND'));
            $isMastered = (count($results) == 3 && $indCount == 3);

            if ($isMastered) $mastered++;

            $stepStats[] = [
                'step_id' => $step_id,
                'attempts' => count($results),
                'ind_count' => $indCount,
                'is_mastered' => $isMastered,
            ];
        }

        return [
            'statistics' => [
                //'step_summary' => $stepStats,
                'total_steps' => $total,
                'mastered_steps' => $mastered,
            ],
            'result' => $total > 0 ? round(($mastered / $total) * 100, 2) : 0
        ];
    }*/
    private function compileBaselineSummary($client_id, $target_id, $session_id, $sessionDate, $collection_id)
    {
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $masteryModel     = new \App\Models\ClientProgram\ClientStimulusStepMasteryModel();

        $all = $stepSessionModel->where([
            'client_id'  => $client_id,
            'target_id'  => $target_id,
            'session_id' => $session_id,
            'phase_id'   => 1,
            'method'     => 'baseline'
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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
        }

        $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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
            $this->collectionModel->insert([
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
            $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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

        $this->collectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
        }

        // ✅ 2. Ensure collection row exists or create
        $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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
            $this->collectionModel->insert([
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
            $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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

        $this->collectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

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


        // Check if session teaching time is active
        $sessionTimerData = [
            'session_id' =>  $sessionDetail->id,
            'end_time' =>  null,
        ];

        $activeTeachingDuration = $this->sessionDurationModel->where($sessionTimerData)->first();
        if (!$activeTeachingDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Teaching session timer is not active. Start the teaching timer to proceed.',
            ]);
        }

        $activePBDuration = $this->sessionPBDurationModel->where($sessionTimerData)->first();
        if ($activePBDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Problem behavior time is active. Stop timer to proceed.',
            ]);
        }

        $activeMandsDuration = $this->sessionMandDurationModel->where($sessionTimerData)->first();
        if ($activeMandsDuration) {
            return $this->response->setJSON([
                'success' => 'No',
                'message' => 'Mands Session time is active. Stop timer to proceed.',
            ]);
        }

        // ✅ 2. Ensure collection row exists or create
        $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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
            $this->collectionModel->insert([
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
            $collection = $this->collectionModel->getExistingEntry($client_id, $target_id, $client_probe_set_id, $session_id);
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

        $this->collectionModel->update($collection_id, [
            'collected_data' => json_encode($updatedCollected),
            'updated_at' => currentDate('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id,
        ]);

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
}
