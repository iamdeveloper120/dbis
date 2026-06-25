<?php

namespace App\Controllers\ClientProfile;

use App\Controllers\AdminController;
use App\Models\Auth\UserModel;

use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientSessions\DailySessionModel;
use App\Models\ClientProgram\ClientProgramModel;
use App\Models\Mands\ClientMandsDefaultReinforcerModel;
use App\Models\Mands\ClientMandsReinforcerModel;
use App\Models\Mands\ClientMandsReinforcerMediaModel;
use App\Services\Reports\DailyReportService;
use App\Services\Reports\DailyReportEmailService;
use App\Services\Reports\ProgressReportService;
use App\Libraries\Reports\ReportEmailStatus;
use App\Models\Reports\DailyReportQueryModel;
use App\Services\ClientProfileDashboardService;
use Throwable;
use App\Models\ClientProblemBehavior\ClientAbcItemModel;
use App\Models\ClientProblemBehavior\MasterAbcItemModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ClientGraphs\DailyDataGraphsModel;
use App\Models\ClientGraphs\CumulativeGraphsModel;
use App\Models\ClientGraphs\RateGraphsModel;
use App\Models\ClientGraphs\MandsGraphsModel;
use App\Models\ClientGraphs\StimulusResponseChainGraphsModel;
use App\Models\ClientGraphs\PhaseLineModel;
use App\Models\ClientGraphs\TargetMonthModel;
use App\Entities\ClientGraphs\PhaseLine;
use App\Entities\ClientGraphs\TargetMonth;


//use function PHPUnit\Framework\isNull;

class ClientProfileController extends AdminController
{
    use ResponseTrait;
    protected string $tabRoutePrefix = 'client-profile/dataSheet';
    private const IMAGE_TYPES_AVAILABLE = ['jpg', 'jpeg', 'png', 'webp'];
    private const VIDEO_TYPES_AVAILABLE = ['mp4', 'webm', 'mov'];
    private const DEFAULT_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'webp'];
    private const DEFAULT_VIDEO_TYPES = ['mp4', 'webm', 'mov'];
    private const DEFAULT_IMAGE_MAX_SIZE_MB = 5;
    private const DEFAULT_VIDEO_MAX_SIZE_MB = 25;
    private const DEFAULT_IMAGE_MAX_COUNT = 5;
    private const DEFAULT_VIDEO_MAX_COUNT = 5;
    private const IMAGE_MAX_SIZE_LIMIT_MB = 20;
    private const VIDEO_MAX_SIZE_LIMIT_MB = 500;
    private const IMAGE_MAX_COUNT_LIMIT = 100;
    private const VIDEO_MAX_COUNT_LIMIT = 100;
    private const MEDIA_ROOT = 'uploads/client-mands-reinforcer/';

    protected DailyReportService $dailyReportService;

    protected $clientModel;
    protected $clientProgramModel;

    protected $clientDataSheetModel;
    protected $targetPhaseModel;
    protected $targetProbeSetModel;
    protected $clientDomainModel;
    protected $pbRecordsModel;
    protected $collectionModel;
    protected $userModel;
    protected $clientMandsReinforcerModel;
    protected $clientMandsReinforcerMediaModel;



    public function __construct()
    {

        $this->clientModel = new ClientModel();
        $this->userModel = new UserModel();
        $this->clientProgramModel = new ClientProgramModel();
        $this->clientMandsReinforcerModel = new ClientMandsReinforcerModel();
        $this->clientMandsReinforcerMediaModel = new ClientMandsReinforcerMediaModel();
        $this->dailyReportService = new DailyReportService();
    }
    /******************************************************************** */
    // Common Client Data for profile
    /******************************************************************** */
    private function getCommonClientData(string $encodedClientId): array
    {
        $client_id = decodeValue($encodedClientId);

        $supervisor = $this->userModel->getClientDefaultSupervisor($client_id);
        $tutors = $this->userModel->getClientInstructors($client_id);
        $programProgress = $this->clientModel->getProgressByClient($client_id);

        return [
            'supervisor'      => $supervisor,
            'tutors'          => $tutors,
            'programProgress' => $programProgress,
        ];
    }

    private function getDashboardData(int $clientId, $client, $supervisor, array $tutors = [], array $dashboardWidgets = []): array
    {
        $service = new ClientProfileDashboardService();

        return $service->build($clientId, $client, $supervisor, $tutors, $dashboardWidgets);
    }

    private function getDashboardWidgetPermissions(): array
    {
        $user = auth()->user();

        return [
            'keyInformation' => $user->can('client-profile.dashboard.key-information.view'),
            'sessionQuality' => $user->can('client-profile.dashboard.session-quality.view'),
            'cumulativeGraph' => $user->can('client-profile.dashboard.cumulative-graph.view'),
            'activeTargets' => $user->can('client-profile.dashboard.active-targets.view'),
            'mandsGraphs' => $user->can('client-profile.dashboard.mands-graphs.view'),
            'behaviourReduction' => $user->can('client-profile.dashboard.behaviour-reduction.view'),
            'sessionOverview' => $user->can('client-profile.dashboard.session-overview.view'),
            'wowMoments' => $user->can('client-profile.dashboard.wow-moments.view'),
        ];
    }


    /******************************************************************** */
    // Clients for Daily Session. only assigned and active clients will display
    /******************************************************************** */
    public function index()
    {

        $clients = $this->clientModel->get_active_client_list();

        $this->page_title = 'Clients List';
        return  view(
            'ClientProfile/index',
            [
                'clients' => $clients,
                'page_title' => $this->page_title
            ]
        );
    }

    public function dashboard($encodedClientId)
    {
        $this->page_title = 'Client Profile | Dashboard';
        $mtab = 'dashboard';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);
        $commonClientData = $this->getCommonClientData($encodedClientId);
        $dashboardWidgets = $this->getDashboardWidgetPermissions();
        $dashboardData = $this->getDashboardData(
            $client_id,
            $client,
            $commonClientData['supervisor'] ?? null,
            $commonClientData['tutors'] ?? [],
            $dashboardWidgets
        );

        return view('ClientProfile/dashboard', array_merge(
            $commonClientData,
            $dashboardData,
            [
                'mtab'  => $mtab,
                'client' => $client,
                'dashboardWidgets' => $dashboardWidgets,
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function keyInformation($encodedClientId)
    {
        $this->page_title = 'Client Profile | Key Information';
        $mtab = 'keyInformation';
        $id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($id);
        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        // Related models

        $effectiveTeachingProcedureModel = model(\App\Models\ClientConfiguration\ClientEffectiveTeachingProcedureModel::class);

        $effectiveTeachingProcedure = $effectiveTeachingProcedureModel->where('client_id', $id)->first();




        return view('ClientProfile/keyInformation', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'effective_teaching_procedure' => $effectiveTeachingProcedure,
                'page_title'      => 'Client Details',
            ]
        ));
    }

    public function currentPrograms($encodedClientId)
    {
        $mtab = 'currentPrograms';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/currentPrograms', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
                'programData' => $this->clientProgramModel->getSelectedClientCurrentProgramSummary($client_id)
            ]
        ));
    }

    public function activeProgram($encodedClientId)
    {
        $mtab = 'activeProgram';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        $this->page_title = 'Client Profile | Current Program';

        return view('ClientProfile/activeProgram', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
                'activeProgramData' => $this->clientProgramModel->getSelectedClientActiveProgram($client_id),
            ]
        ));
    }

    /*  CLIENT BACKGROUND DETAIL VIEW  */
    public function background(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Client Details';
        $mtab = 'background';
        $id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($id);
        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        // Related models
        $infoModel       = model(\App\Models\ClientConfiguration\ClientInfoModel::class);
        $otherDiagModel  = model(\App\Models\ClientConfiguration\ClientOtherDiagnosisModel::class);
        $guardianModel   = model(\App\Models\ClientConfiguration\ClientGuardianModel::class);
        $householdModel  = model(\App\Models\ClientConfiguration\ClientHouseholdModel::class);
        $medicalModel    = model(\App\Models\ClientConfiguration\ClientMedicalModel::class);
        $medicationModel = model(\App\Models\ClientConfiguration\ClientMedicationModel::class);
        $educationModel  = model(\App\Models\ClientConfiguration\ClientEducationModel::class);
        $effectiveTeachingProcedureModel = model(\App\Models\ClientConfiguration\ClientEffectiveTeachingProcedureModel::class);

        $info       = $infoModel->where('client_id', $id)->first();
        $education  = $educationModel->where('client_id', $id)->first();
        $effectiveTeachingProcedure = $effectiveTeachingProcedureModel->where('client_id', $id)->first();
        $otherDiags = $otherDiagModel->where('client_id', $id)->findAll();



        return view('ClientProfile/background', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'info'            => $info,
                'otherDiagnoses'  => $otherDiags,
                'guardians'       => $guardianModel->where('client_id', $id)->findAll(),
                'household'       => $householdModel->where('client_id', $id)->findAll(),
                'medical'         => $medicalModel->where('client_id', $id)->first(),
                'medications'     => $medicationModel->where('client_id', $id)->findAll(),
                'education'       => $education,
                'effective_teaching_procedure' => $effectiveTeachingProcedure,
                'page_title'      => 'Client Details',
            ]
        ));
    }


    public function sessions($encodedClientId)
    {
        $this->page_title = 'Clients Profile | Sessions';
        $mtab = 'sessions';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        // 🟩 Load sessions for this client
        $sessionModel = new DailySessionModel();
        $sessions = $sessionModel->get_client_executed_sessions($client_id, NULL, NULL, NULL, NULL, NULL, NULL);
        $session_list = [];
        foreach ($sessions as $s) {
            $pb_seconds = strtotime($s['total_duration_of_problem_behavior'] ?? '00:00:00') - strtotime('TODAY');

            $session_list[] = [
                'client'              => $s['internal_mrn'],
                'instructor_name'     => trim($s['instructor_first_name'] . ' ' . $s['instructor_last_name']),
                'session_date'        => app_date($s['session_date']),
                'session_date_raw'    => $s['session_date'], // original Y-m-d
                'start_time'          => $s['start_time'],
                'end_time'            => $s['end_time'],
                'qr'                  => $s['session_rating'],
                'pb_duration_formatted' => $s['total_duration_of_problem_behavior'] ?? '00:00:00',
                'pb_duration_sec'     => $pb_seconds > 0 ? $pb_seconds : 0,
                'mands_total'         => $s['total_mands'] ?? 0,
                'mands_variety'       => $s['variety_of_mands'] ?? 0,
                'mands_freq'          => $s['frequency_of_mands_per_minute'] ?? 0,
                'mands_duration'      => $s['total_duration_of_mands'] ?? '',
                'instructor_comments' => $s['instructor_comments'] ?? '',
                'wow_moments'         => $s['comments'] ?? ''
            ];
        }

        return view('ClientProfile/sessions', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'sessions' => $session_list,
                'page_title' => $this->page_title,
            ]
        ));
    }



    public function programData($encodedClientId)
    {
        $mtab = 'programs';
        $client_id = decodeValue($encodedClientId);

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getInitialProgramData($client_id);

        $this->page_title = 'Clients Profile';

        return view('ClientProfile/programData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function dailyData($encodedClientId)
    {
        $mtab = 'dailyData';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        $this->page_title = 'Clients Profile | Daily Data';

        return view('ClientProfile/dailyData', array_merge(
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function weeklyData($encodedClientId)
    {
        $mtab = 'weeklyData';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);
        $this->page_title = 'Clients Profile | Weekly Data Manual';

        return view('ClientProfile/weeklyData', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function mandsData($encodedClientId)
    {

        $mtab = 'mandsTab';
        $client_id = decodeValue($encodedClientId);

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getMandsSummary($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/mandsData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function currentMandList($encodedClientId)
    {
        $mtab = 'currentMandListTab';
        $client_id = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getCurrentMandList($client_id);

        $this->page_title = 'Clients Profile | Current Mand List';

        return view('ClientProfile/currentMandListData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'client' => $client,
                'encodedClientId' => $encodedClientId,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function currentMandListList(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $data = $dataSheetService->getCurrentMandList($clientId);

        $response = $this->getResponseObject('success', 'Current Mand List', 'Listed successfully.', [], $data['currentMandListData'] ?? []);
        return $this->response->setJSON($response);
    }

    public function currentMandListCreate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $rules = [
            'reinforcer_name' => [
                'label' => 'Mand Target',
                'rules' => 'required|min_length[1]|max_length[255]',
                'errors' => [
                    'required' => '{field} is required.',
                    'max_length' => '{field} max length is 255.',
                ],
            ],
            'introduced_at' => [
                'label' => 'Date',
                'rules' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
                'errors' => [
                    'required' => '{field} is required.',
                    'regex_match' => '{field} must be in YYYY-MM-DD format.',
                ],
            ],
            'vocal_sign' => [
                'label' => 'Vocal/Sign',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'description' => [
                'label' => 'Description',
                'rules' => 'permit_empty|max_length[5000]',
            ],
        ];

        $name = trim((string) $this->request->getPost('reinforcer_name'));
        $introducedAt = trim((string) $this->request->getPost('introduced_at'));
        $vocalSign = trim((string) $this->request->getPost('vocal_sign'));
        $description = trim((string) $this->request->getPost('description'));

        $payload = [
            'reinforcer_name' => $name,
            'introduced_at' => $introducedAt,
            'vocal_sign' => $vocalSign,
            'description' => $description,
        ];

        if (!$this->validateData($payload, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        if ($this->clientReinforcerNameExists($clientId, $name, null)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['reinforcer_name' => 'Mand Target already exists for this client.'], []);
            return $this->response->setJSON($response);
        }

        $saveData = [
            'client_id' => $clientId,
            'reinforcer_name' => $name,
            'introduced_at' => $introducedAt,
            'vocal_sign' => $vocalSign !== '' ? $vocalSign : null,
            'description' => $description !== '' ? $description : null,
            'created_by' => auth()->user()->id,
            'updated_by' => null,
        ];

        if (!$this->clientMandsReinforcerModel->insert($saveData)) {
            $response = $this->getResponseObject('error', 'Error', 'Unable to create Current Mand List entry.', [], []);
            return $this->response->setJSON($response);
        }

        $newId = (int) $this->clientMandsReinforcerModel->getInsertID();
        $record = $this->buildCurrentMandListRecord($newId);

        $response = $this->getResponseObject('success', 'Current Mand List', 'Created successfully.', [], $record);
        return $this->response->setJSON($response);
    }

    public function currentMandListUpdate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => '{field} must be an integer.',
                ],
            ],
            'reinforcer_name' => [
                'label' => 'Mand Target',
                'rules' => 'required|min_length[1]|max_length[255]',
                'errors' => [
                    'required' => '{field} is required.',
                    'max_length' => '{field} max length is 255.',
                ],
            ],
            'introduced_at' => [
                'label' => 'Date',
                'rules' => 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]',
                'errors' => [
                    'required' => '{field} is required.',
                    'regex_match' => '{field} must be in YYYY-MM-DD format.',
                ],
            ],
            'vocal_sign' => [
                'label' => 'Vocal/Sign',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'description' => [
                'label' => 'Description',
                'rules' => 'permit_empty|max_length[5000]',
            ],
        ];

        $id = (int) $this->request->getPost('id');
        $name = trim((string) $this->request->getPost('reinforcer_name'));
        $introducedAt = trim((string) $this->request->getPost('introduced_at'));
        $vocalSign = trim((string) $this->request->getPost('vocal_sign'));
        $description = trim((string) $this->request->getPost('description'));

        $payload = [
            'id' => $id,
            'reinforcer_name' => $name,
            'introduced_at' => $introducedAt,
            'vocal_sign' => $vocalSign,
            'description' => $description,
        ];

        if (!$this->validateData($payload, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $record = $this->clientMandsReinforcerModel
            ->where('id', $id)
            ->where('client_id', $clientId)
            ->first();

        if (!$record) {
            $response = $this->getResponseObject('error', 'NotFound', 'Current Mand List entry not found.', [], []);
            return $this->response->setJSON($response);
        }

        if ($this->clientReinforcerNameExists($clientId, $name, $id)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['reinforcer_name' => 'Mand Target already exists for this client.'], []);
            return $this->response->setJSON($response);
        }

        $saveData = [
            'id' => $id,
            'reinforcer_name' => $name,
            'introduced_at' => $introducedAt,
            'vocal_sign' => $vocalSign !== '' ? $vocalSign : null,
            'description' => $description !== '' ? $description : null,
            'updated_by' => auth()->user()->id,
        ];

        if (!$this->clientMandsReinforcerModel->save($saveData)) {
            $response = $this->getResponseObject('error', 'Error', 'Unable to update Current Mand List entry.', [], []);
            return $this->response->setJSON($response);
        }

        $updated = $this->buildCurrentMandListRecord($id);
        $response = $this->getResponseObject('success', 'Current Mand List', 'Updated successfully.', [], $updated);
        return $this->response->setJSON($response);
    }

    public function currentMandListDelete(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => '{field} must be an integer.',
                ],
            ],
        ];

        $id = (int) $this->request->getPost('id');
        $payload = ['id' => $id];

        if (!$this->validateData($payload, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $record = $this->clientMandsReinforcerModel
            ->where('id', $id)
            ->where('client_id', $clientId)
            ->first();

        if (!$record) {
            $response = $this->getResponseObject('error', 'NotFound', 'Current Mand List entry not found.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaRows = $this->clientMandsReinforcerMediaModel
            ->where('client_reinforcer_id', $id)
            ->findAll();

        $mediaPaths = [];
        foreach ($mediaRows as $media) {
            $mediaPaths[] = (string) ($media->media_path ?? '');
        }

        if (!$this->clientMandsReinforcerModel->delete($id)) {
            $response = $this->getResponseObject('error', 'Error', 'Unable to delete Current Mand List entry.', [], []);
            return $this->response->setJSON($response);
        }

        foreach ($mediaPaths as $mediaPath) {
            $this->deleteCurrentMandMediaFile($mediaPath);
        }

        $response = $this->getResponseObject('success', 'Current Mand List', 'Deleted successfully.', [], []);
        return $this->response->setJSON($response);
    }

    public function currentMandListMediaUpload(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $reinforcerId = (int) $this->request->getPost('client_reinforcer_id');
        $mediaType = strtolower(trim((string) $this->request->getPost('media_type')));

        if ($reinforcerId <= 0 || !in_array($mediaType, ['image', 'video'], true)) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                'Validation Errors',
                ['client_reinforcer_id' => 'Valid reinforcer id and media_type are required.'],
                []
            );
            return $this->response->setJSON($response);
        }

        $reinforcer = $this->clientMandsReinforcerModel
            ->where('id', $reinforcerId)
            ->where('client_id', $clientId)
            ->first();

        if (!$reinforcer) {
            $response = $this->getResponseObject('error', 'NotFound', 'Current Mand List entry not found.', [], []);
            return $this->response->setJSON($response);
        }

        $file = $this->resolveCurrentMandUploadedFile($mediaType);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                'Validation Errors',
                ['file' => 'Valid media file is required.'],
                []
            );
            return $this->response->setJSON($response);
        }

        $settings = $this->loadCurrentMandMediaSettings();
        $existingImageCount = (int) $this->clientMandsReinforcerMediaModel
            ->where('client_reinforcer_id', $reinforcerId)
            ->where('media_type', 'image')
            ->countAllResults();
        $existingVideoCount = (int) $this->clientMandsReinforcerMediaModel
            ->where('client_reinforcer_id', $reinforcerId)
            ->where('media_type', 'video')
            ->countAllResults();

        $typeMaxCount = $mediaType === 'image'
            ? (int) ($settings['image_max_count'] ?? self::DEFAULT_IMAGE_MAX_COUNT)
            : (int) ($settings['video_max_count'] ?? self::DEFAULT_VIDEO_MAX_COUNT);
        $currentTypeCount = $mediaType === 'image' ? $existingImageCount : $existingVideoCount;
        if ($currentTypeCount >= $typeMaxCount) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                ucfirst($mediaType) . ' upload limit reached (' . $typeMaxCount . ').',
                ['file' => ucfirst($mediaType) . ' upload limit reached (' . $typeMaxCount . ').'],
                []
            );
            return $this->response->setJSON($response);
        }

        $uploadResult = $this->uploadCurrentMandMediaFile($file, $mediaType, $settings);
        if (!$uploadResult['success']) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                $uploadResult['message'],
                ['file' => $uploadResult['message']],
                []
            );
            return $this->response->setJSON($response);
        }

        $saveData = [
            'client_reinforcer_id' => $reinforcerId,
            'media_type' => $mediaType,
            'media_path' => $uploadResult['path'],
            'created_by' => auth()->user()->id,
        ];

        if (!$this->clientMandsReinforcerMediaModel->insert($saveData)) {
            $this->deleteCurrentMandMediaFile((string) $uploadResult['path']);
            $response = $this->getResponseObject('error', 'Error', 'Unable to save media.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaId = (int) $this->clientMandsReinforcerMediaModel->getInsertID();
        $media = $this->clientMandsReinforcerMediaModel->find($mediaId);

        $response = $this->getResponseObject(
            'success',
            'Current Mand List Media',
            'Media uploaded successfully.',
            [],
            [
                'id' => (int) ($media->id ?? 0),
                'client_reinforcer_id' => (int) ($media->client_reinforcer_id ?? 0),
                'media_type' => (string) ($media->media_type ?? ''),
                'media_path' => (string) ($media->media_path ?? ''),
                'view_url' => base_url('client-profile/dataSheet/currentMandList/media/view/' . $encodedClientId . '/' . (int) ($media->id ?? 0)),
            ]
        );

        return $this->response->setJSON($response);
    }

    public function currentMandListMediaDelete(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaId = (int) $this->request->getPost('media_id');
        if ($mediaId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['media_id' => 'Valid media id is required.'], []);
            return $this->response->setJSON($response);
        }

        $media = $this->clientMandsReinforcerMediaModel->find($mediaId);
        if (!$media) {
            $response = $this->getResponseObject('error', 'NotFound', 'Media item not found.', [], []);
            return $this->response->setJSON($response);
        }

        $reinforcer = $this->clientMandsReinforcerModel
            ->where('id', (int) ($media->client_reinforcer_id ?? 0))
            ->where('client_id', $clientId)
            ->first();

        if (!$reinforcer) {
            $response = $this->getResponseObject('error', 'NotFound', 'Media item not found for this client.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaPath = (string) ($media->media_path ?? '');
        if (!$this->clientMandsReinforcerMediaModel->delete($mediaId)) {
            $response = $this->getResponseObject('error', 'Error', 'Unable to delete media.', [], []);
            return $this->response->setJSON($response);
        }

        $this->deleteCurrentMandMediaFile($mediaPath);
        $response = $this->getResponseObject('success', 'Current Mand List Media', 'Media deleted successfully.', [], []);
        return $this->response->setJSON($response);
    }

    public function currentMandListMediaReplace(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaId = (int) $this->request->getPost('media_id');
        if ($mediaId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['media_id' => 'Valid media id is required.'], []);
            return $this->response->setJSON($response);
        }

        $media = $this->clientMandsReinforcerMediaModel->find($mediaId);
        if (!$media) {
            $response = $this->getResponseObject('error', 'NotFound', 'Media item not found.', [], []);
            return $this->response->setJSON($response);
        }

        $reinforcer = $this->clientMandsReinforcerModel
            ->where('id', (int) ($media->client_reinforcer_id ?? 0))
            ->where('client_id', $clientId)
            ->first();
        if (!$reinforcer) {
            $response = $this->getResponseObject('error', 'NotFound', 'Media item not found for this client.', [], []);
            return $this->response->setJSON($response);
        }

        $mediaType = strtolower(trim((string) ($media->media_type ?? '')));
        if (!in_array($mediaType, ['image', 'video'], true)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid media type for replacement.', [], []);
            return $this->response->setJSON($response);
        }

        $file = $this->resolveCurrentMandUploadedFile($mediaType);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                'Validation Errors',
                ['file' => 'Valid replacement media file is required.'],
                []
            );
            return $this->response->setJSON($response);
        }

        $settings = $this->loadCurrentMandMediaSettings();
        $uploadResult = $this->uploadCurrentMandMediaFile($file, $mediaType, $settings);
        if (!$uploadResult['success']) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                $uploadResult['message'],
                ['file' => $uploadResult['message']],
                []
            );
            return $this->response->setJSON($response);
        }

        $oldPath = (string) ($media->media_path ?? '');
        if (!$this->clientMandsReinforcerMediaModel->update($mediaId, ['media_path' => (string) $uploadResult['path']])) {
            $this->deleteCurrentMandMediaFile((string) $uploadResult['path']);
            $response = $this->getResponseObject('error', 'Error', 'Unable to replace media.', [], []);
            return $this->response->setJSON($response);
        }

        $this->deleteCurrentMandMediaFile($oldPath);
        $updatedMedia = $this->clientMandsReinforcerMediaModel->find($mediaId);
        $response = $this->getResponseObject(
            'success',
            'Current Mand List Media',
            'Media replaced successfully.',
            [],
            [
                'id' => (int) ($updatedMedia->id ?? $mediaId),
                'client_reinforcer_id' => (int) ($updatedMedia->client_reinforcer_id ?? 0),
                'media_type' => (string) ($updatedMedia->media_type ?? ''),
                'media_path' => (string) ($updatedMedia->media_path ?? ''),
                'view_url' => base_url('client-profile/dataSheet/currentMandList/media/view/' . $encodedClientId . '/' . (int) ($updatedMedia->id ?? $mediaId)),
            ]
        );
        return $this->response->setJSON($response);
    }

    public function currentMandListMediaView(string $encodedClientId, int $mediaId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        if ($clientId <= 0 || $mediaId <= 0) {
            throw new PageNotFoundException('Invalid client or media reference.');
        }

        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            throw new PageNotFoundException('Client not found.');
        }

        $media = $this->clientMandsReinforcerMediaModel->find($mediaId);
        if (!$media) {
            throw new PageNotFoundException('Media not found.');
        }

        $reinforcer = $this->clientMandsReinforcerModel
            ->where('id', (int) ($media->client_reinforcer_id ?? 0))
            ->where('client_id', $clientId)
            ->first();

        if (!$reinforcer) {
            throw new PageNotFoundException('Media not found for this client.');
        }

        $fullPath = $this->resolveCurrentMandMediaPath((string) ($media->media_path ?? ''));
        if ($fullPath === null || !is_file($fullPath)) {
            throw new PageNotFoundException('Media file not found.');
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read media file.');
        }

        $mime = $this->getCurrentMandMediaMimeType($fullPath, (string) ($media->media_type ?? ''));
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($fullPath) . '"')
            ->setBody($content);
    }

    public function currentMandListMediaSettings(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $settings = $this->loadCurrentMandMediaSettings();
        $response = $this->getResponseObject('success', 'Current Mand List Media Settings', 'Loaded successfully.', [], $settings);
        return $this->response->setJSON($response);
    }

    public function pbData($encodedClientId)
    {
        $mtab = 'pbTab';
        $client_id = decodeValue($encodedClientId);

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getPbData($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/pbData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function defaultReinforcerData($encodedClientId)
    {
        $mtab = 'defaultReinforcerTab';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        $defaultReinforcerModel = new ClientMandsDefaultReinforcerModel();
        $defaultReinforcers = $defaultReinforcerModel
            ->where('client_id', $client_id)
            ->orderBy('`order`', 'ASC', false)
            ->orderBy('id', 'ASC')
            ->findAll();

        $this->page_title = 'Clients Profile | Selected Reinforcers';

        return view('ClientProfile/defaultReinforcerData', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'client' => $client,
                'defaultReinforcers' => $defaultReinforcers,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function defaultAbcData($encodedClientId)
    {
        $mtab = 'defaultAbcTab';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        $clientAbcItemModel = new ClientAbcItemModel();
        $masterAbcItemModel = new MasterAbcItemModel();

        $resolved = [];
        foreach (['antecedent', 'behavior', 'consequence'] as $category) {
            $clientValues = $clientAbcItemModel->getClientValuesByCategory($client_id, $category);
            if (!empty($clientValues)) {
                $resolved[$category] = $clientValues;
                continue;
            }

            $masterRows = $masterAbcItemModel
                ->select('value')
                ->where('category', $category)
                ->orderBy('value', 'ASC')
                ->findAll();

            $resolved[$category] = array_values(array_filter(array_map(static function ($row) {
                return trim((string) ($row['value'] ?? ''));
            }, $masterRows), static fn($value) => $value !== ''));
        }

        $this->page_title = 'Clients Profile | Individualised ABC Data';

        return view('ClientProfile/defaultAbcData', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab' => $mtab,
                'client' => $client,
                'antecedents' => $resolved['antecedent'] ?? [],
                'behaviors' => $resolved['behavior'] ?? [],
                'consequences' => $resolved['consequence'] ?? [],
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function pcData($encodedClientId)
    {

        $mtab = 'pcTab';
        $client_id = decodeValue($encodedClientId);

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getFilterBaseData($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/pcData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function skillsData($encodedClientId)
    {
        $mtab = 'skillsTab';
        $client_id = decodeValue($encodedClientId);
        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getFilterBaseData($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/skillsData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function doiData($encodedClientId)
    {
        $mtab = 'doiTab';
        $client_id = decodeValue($encodedClientId);

        $dataSheetService = new \App\Services\ClientDataSheetService();
        $initialData = $dataSheetService->getFilterBaseData($client_id);


        $this->page_title = 'Clients Profile';

        return view('ClientProfile/doiData', array_merge(
            $initialData,
            $this->getCommonClientData($encodedClientId),
            [

                'mtab' => $mtab,
                'tabRoutePrefix' => 'client-profile/dataSheet',
                'page_title' => $this->page_title,
            ]
        ));
    }




    public function graphsDaily($encodedClientId)
    {
        $this->page_title = 'Client Profile | Daily Graphs';
        $mtab = 'graphs-daily';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/daily', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function graphsStimulusResponseChain($encodedClientId)
    {
        $this->page_title = 'Client Profile | Stimulus Response Chain Graphs';
        $mtab = 'graphs-stimulus-response-chain';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/stimulus-response-chain', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function graphsCumulative($encodedClientId)
    {
        $this->page_title = 'Client Profile | Cumulative Graphs';
        $mtab = 'graphs-cumulative';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Cumulative/index', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function graphsCumulativePhaseline($encodedClientId)
    {
        $this->page_title = 'Client Profile | Cumulative Graphs Phaseline';
        $mtab = 'graphs-cumulative';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Cumulative/phase_line', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function graphsCumulativeByDomainAndGoal($encodedClientId)
    {
        $this->page_title = 'Client Profile | Cumulative Graphs By Domain and Goal';
        $mtab = 'graphs-cumulative';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Cumulative/by-domain-and-goal-index', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }


    public function graphsRate($encodedClientId)
    {
        $this->page_title = 'Client Profile | Rate Graphs';
        $mtab = 'graphs-rate';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Rate/index', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function graphsRatePhaseline($encodedClientId)
    {
        $this->page_title = 'Client Profile | Rate Graphs Phaseline';
        $mtab = 'graphs-rate';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Rate/phase_line', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }
    public function graphsRateTargetMonths($encodedClientId)
    {
        $this->page_title = 'Client Profile | Rate Graphs';
        $mtab = 'graphs-rate';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/Rate/target_months', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function graphsMands($encodedClientId)
    {
        $this->page_title = 'Client Profile | Mand Graphs';
        $mtab = 'graphs-mands';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/mands', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function graphsProblemBehaviour($encodedClientId)
    {
        $this->page_title = 'Client Profile | Behaviour Reduction Graphs';
        $mtab = 'graphs-pb';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Graphs/behaviourReduction', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    /************************************************************************* Graph data endpoints */

    public function graphsDailyData(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');

        if ($start_date != '' || $end_date != '') {
            $rules = [
                'start_date' => ['label' => 'Start Date', 'rules' => 'required|valid_date', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date']],
                'end_date'   => ['label' => 'End Date', 'rules' => 'required|valid_date|compareDates[start_date,end_date,{$start_date,$end_date}]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date', 'compareDates' => '{field} must be greater then Start Date']],
            ];
            $data = ['start_date' => $start_date, 'end_date' => $end_date];
            if (!$this->validateData($data, $rules)) {
                return $this->response->setJSON(['status' => 'validation_error', 'statusText' => 'Error', 'message' => $this->validator->getErrors(), 'data' => '']);
            }
            $start_date = stringToDate($start_date, 'Y-m-d');
            $end_date   = stringToDate($end_date, 'Y-m-d');
        } else {
            $start_date = null;
            $end_date   = null;
        }

        $model = model(DailyDataGraphsModel::class);
        $graph_data = $model->get_client_session_data_for_graphs($client_id, $start_date, $end_date);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $graph_data]);
    }

    public function graphsMandsData(string $encodedClientId)
    {
        $client_id  = decodeValue($encodedClientId);
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');

        if ($start_date != '' || $end_date != '') {
            $rules = [
                'start_date' => ['label' => 'Start Date', 'rules' => 'required|valid_date', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date']],
                'end_date'   => ['label' => 'End Date', 'rules' => 'required|valid_date|compareDates[start_date,end_date,{$start_date,$end_date}]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date', 'compareDates' => '{field} must be greater then Start Date']],
            ];
            $data = ['start_date' => $start_date, 'end_date' => $end_date];
            if (!$this->validateData($data, $rules)) {
                return $this->response->setJSON(['status' => 'validation_error', 'statusText' => 'Error', 'message' => $this->validator->getErrors(), 'data' => '']);
            }
            $start_date = stringToDate($start_date, 'Y-m-d');
            $end_date   = stringToDate($end_date, 'Y-m-d');
        } else {
            $start_date = null;
            $end_date   = null;
        }

        $model      = model(MandsGraphsModel::class);
        $graph_data = $model->getMandsSummaryDataForGraphs($client_id, $start_date, $end_date);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $graph_data]);
    }

    public function graphsCumulativeData(string $encodedClientId)
    {
        $client_id  = decodeValue($encodedClientId);
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');

        if ($start_date != '' || $end_date != '') {
            $rules = [
                'start_date' => ['label' => 'Start Date', 'rules' => 'required|valid_date', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date']],
                'end_date'   => ['label' => 'End Date', 'rules' => 'required|valid_date|compareDates[start_date,end_date,{$start_date,$end_date}]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} not a valid date', 'compareDates' => '{field} must be greater then Start Date']],
            ];
            $data = ['start_date' => $start_date, 'end_date' => $end_date];
            if (!$this->validateData($data, $rules)) {
                return $this->response->setJSON(['status' => 'validation_error', 'statusText' => 'Error', 'message' => $this->validator->getErrors(), 'data' => '']);
            }
            $start_date = stringToDate($start_date, 'Y-m-d');
            $end_date   = stringToDate($end_date, 'Y-m-d');
        } else {
            $start_date = null;
            $end_date   = null;
        }

        $model                  = model(CumulativeGraphsModel::class);
        $cumulative_data        = $model->get_cumulative_data($client_id, $start_date, $end_date);
        $client                 = $this->clientModel->getClientById($client_id);
        $clientActiveProgram    = $this->clientModel->clientActiveProgram($client_id);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $cumulative_data, 'client' => $client, 'clientActiveProgram' => $clientActiveProgram]);
    }

    public function graphsCumulativePhaselineList(string $encodedClientId)
    {
        $client_id  = decodeValue($encodedClientId);
        $graph_type = $this->request->getPost('graph_type') ?? 'Cumulative';
        $model      = model(PhaseLineModel::class);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $model->list($client_id, $graph_type)]);
    }

    public function graphsCumulativePhaselineCreate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            'p_date'    => $this->request->getPost('p_date'),
            'client_id' => $client_id,
            'graph_type'=> 'Cumulative',
            'p_key'     => $this->request->getPost('p_key'),
        ];
        $rules = [
            'p_date'  => ['label' => 'Date', 'rules' => 'required|valid_date|is_phase_line_date_exist[p_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'p_key'   => ['label' => 'Phase Line Key', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['p_date']     = stringToDate($data['p_date'], 'Y-m-d');
        $data['created_by'] = auth()->user()->id;
        $model = model(PhaseLineModel::class);
        $entity = new PhaseLine();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record created successfully', 'data' => $model->single($model->getInsertID())]);
    }

    public function graphsCumulativePhaselineGetSelected(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model   = model(PhaseLineModel::class);
        $rowData = $model->find($id);
        $rowData->p_date = stringToDate($rowData->p_date, CC_DATE_FORMAT);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => '', 'data' => $rowData]);
    }

    public function graphsCumulativePhaselineUpdate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            'id'        => $this->request->getPost('id'),
            'p_date'    => $this->request->getPost('p_date'),
            'client_id' => $client_id,
            'graph_type'=> 'Cumulative',
            'p_key'     => $this->request->getPost('p_key'),
        ];
        $rules = [
            'id'     => ['label' => 'Record ID', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
            'p_date' => ['label' => 'Date', 'rules' => 'required|valid_date|is_phase_line_date_exist[p_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'p_key'  => ['label' => 'Phase Line Key', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['p_date']     = stringToDate($data['p_date'], 'Y-m-d');
        $data['updated_by'] = auth()->user()->id;
        $model  = model(PhaseLineModel::class);
        $entity = new PhaseLine();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record updated successfully', 'data' => $model->single($data['id'])]);
    }

    public function graphsCumulativePhaselineDelete(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model  = model(PhaseLineModel::class);
        $result = $model->delete($id);

        return $this->response->setJSON($result
            ? ['status' => 'success', 'statusText' => '', 'message' => 'Record deleted successfully', 'data' => '']
            : ['status' => 'error', 'statusText' => '', 'message' => 'Contact system administrator', 'data' => '']);
    }

    public function graphsCumulativeDomains(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $model     = model(CumulativeGraphsModel::class);

        return $this->response->setJSON($model->getDomains($client_id));
    }

    public function graphsCumulativeDomainGoals(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $domain_id = $this->request->getPost('domain_id');
        $model     = model(CumulativeGraphsModel::class);

        return $this->response->setJSON($model->getGoalsByDomain($client_id, $domain_id));
    }

    public function graphsCumulativeDomainAndGoalData(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $domain_id = $this->request->getPost('domain_id');
        $goal_id   = $this->request->getPost('goal_id');

        $model                  = model(CumulativeGraphsModel::class);
        $cumulative_data        = $model->get_cumulative_data_by_domain_and_goal($client_id, $domain_id, $goal_id);
        $client                 = $this->clientModel->getClientById($client_id);
        $clientActiveProgram    = $this->clientModel->clientActiveProgram($client_id);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $cumulative_data, 'client' => $client, 'clientActiveProgram' => $clientActiveProgram]);
    }

    public function graphsRateData(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $model     = model(RateGraphsModel::class);
        $skill_data = $model->get_target_rate_data($client_id, 'Skills');
        $doi_data   = $model->get_target_rate_data($client_id, 'DOI');

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => ['skill_data' => $skill_data, 'doi_data' => $doi_data]]);
    }

    public function graphsRatePhaselineList(string $encodedClientId)
    {
        $client_id  = decodeValue($encodedClientId);
        $graph_type = $this->request->getPost('graph_type') ?? 'Target_Rate';
        $model      = model(PhaseLineModel::class);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $model->list($client_id, $graph_type)]);
    }

    public function graphsRatePhaselineCreate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            'p_date'    => $this->request->getPost('p_date'),
            'client_id' => $client_id,
            'graph_type'=> 'Target_Rate',
            'p_key'     => $this->request->getPost('p_key'),
        ];
        $rules = [
            'p_date'  => ['label' => 'Date', 'rules' => 'required|valid_date|is_phase_line_date_exist[p_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'p_key'   => ['label' => 'Phase Line Key', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['p_date']     = stringToDate($data['p_date'], 'Y-m-d');
        $data['created_by'] = auth()->user()->id;
        $model  = model(PhaseLineModel::class);
        $entity = new PhaseLine();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record created successfully', 'data' => $model->single($model->getInsertID())]);
    }

    public function graphsRatePhaselineGetSelected(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model   = model(PhaseLineModel::class);
        $rowData = $model->find($id);
        $rowData->p_date = stringToDate($rowData->p_date, CC_DATE_FORMAT);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => '', 'data' => $rowData]);
    }

    public function graphsRatePhaselineUpdate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            'id'        => $this->request->getPost('id'),
            'p_date'    => $this->request->getPost('p_date'),
            'client_id' => $client_id,
            'graph_type'=> 'Target_Rate',
            'p_key'     => $this->request->getPost('p_key'),
        ];
        $rules = [
            'id'     => ['label' => 'Record ID', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
            'p_date' => ['label' => 'Date', 'rules' => 'required|valid_date|is_phase_line_date_exist[p_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'p_key'  => ['label' => 'Phase Line Key', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['p_date']     = stringToDate($data['p_date'], 'Y-m-d');
        $data['updated_by'] = auth()->user()->id;
        $model  = model(PhaseLineModel::class);
        $entity = new PhaseLine();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record updated successfully', 'data' => $model->single($data['id'])]);
    }

    public function graphsRatePhaselineDelete(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model  = model(PhaseLineModel::class);
        $result = $model->delete($id);

        return $this->response->setJSON($result
            ? ['status' => 'success', 'statusText' => '', 'message' => 'Record deleted successfully', 'data' => '']
            : ['status' => 'error', 'statusText' => '', 'message' => 'Contact system administrator', 'data' => '']);
    }

    public function graphsRateTargetMonthsList(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $model     = model(TargetMonthModel::class);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => 'List', 'data' => $model->list($client_id)]);
    }

    public function graphsRateTargetMonthsCreate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            't_date'    => $this->request->getPost('t_date'),
            'client_id' => $client_id,
            'graph_type'=> $this->request->getPost('graph_type'),
        ];
        $rules = [
            't_date'    => ['label' => 'Date', 'rules' => 'required|valid_date|is_target_date_exist[t_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'graph_type'=> ['label' => 'Graph Type', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['t_date']     = stringToDate($data['t_date'], 'Y-m-d');
        $data['created_by'] = auth()->user()->id;
        $model  = model(TargetMonthModel::class);
        $entity = new TargetMonth();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record created successfully', 'data' => $model->single($model->getInsertID())]);
    }

    public function graphsRateTargetMonthsGetSelected(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model   = model(TargetMonthModel::class);
        $rowData = $model->find($id);
        $t_date  = new \DateTime($rowData->t_date);
        $rowData->t_date = $t_date->format('M-Y');

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => '', 'data' => $rowData]);
    }

    public function graphsRateTargetMonthsUpdate(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $data = [
            'id'        => $this->request->getPost('id'),
            't_date'    => $this->request->getPost('t_date'),
            'client_id' => $client_id,
            'graph_type'=> $this->request->getPost('graph_type'),
        ];
        $rules = [
            'id'        => ['label' => 'Record ID', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
            't_date'    => ['label' => 'Date', 'rules' => 'required|valid_date|is_target_date_exist[t_date]', 'errors' => ['required' => '{field} Required', 'valid_date' => '{field} is not valid date']],
            'graph_type'=> ['label' => 'Graph Type', 'rules' => 'required', 'errors' => ['required' => '{field} Required']],
        ];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => $this->validator->listErrors('custom_list'), 'data' => '']);
        }
        $data['t_date']     = stringToDate($data['t_date'], 'Y-m-d');
        $data['updated_by'] = auth()->user()->id;
        $model  = model(TargetMonthModel::class);
        $entity = new TargetMonth();
        $entity->fill($data);
        $model->save($entity);

        return $this->response->setJSON(['status' => 'success', 'statusText' => '', 'message' => 'Record updated successfully', 'data' => $model->single($data['id'])]);
    }

    public function graphsRateTargetMonthsDelete(string $encodedClientId)
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'statusText' => 'Error', 'message' => 'Record ID Required', 'data' => '']);
        }
        $model  = model(TargetMonthModel::class);
        $result = $model->delete($id);

        return $this->response->setJSON($result
            ? ['status' => 'success', 'statusText' => '', 'message' => 'Record deleted successfully', 'data' => '']
            : ['status' => 'error', 'statusText' => '', 'message' => 'Contact system administrator', 'data' => '']);
    }

    public function graphsStimulusResponseChainData(string $encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $domain_id = $this->request->getPost('domain_id');
        $goal_id   = $this->request->getPost('goal_id');
        $target_id = $this->request->getPost('target_id');

        $rules = [
            'domain_id' => ['label' => 'Domain', 'rules' => 'required|integer', 'errors' => ['required' => '{field} Required', 'integer' => '{field} must be a valid selection']],
            'goal_id'   => ['label' => 'Goal', 'rules' => 'required|integer', 'errors' => ['required' => '{field} Required', 'integer' => '{field} must be a valid selection']],
            'target_id' => ['label' => 'Target', 'rules' => 'permit_empty|integer', 'errors' => ['integer' => '{field} must be a valid selection']],
        ];
        $data = ['domain_id' => $domain_id, 'goal_id' => $goal_id, 'target_id' => $target_id];
        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['status' => 'validation_error', 'statusText' => 'Error', 'message' => $this->validator->getErrors(), 'data' => '']);
        }

        $clientId  = (int) $client_id;
        $domainId  = (int) $domain_id;
        $goalId    = (int) $goal_id;
        $targetId  = ($target_id === '' || $target_id === null) ? null : (int) $target_id;

        $model              = model(StimulusResponseChainGraphsModel::class);
        $graphs             = $model->getGraphsData($clientId, $domainId, $goalId, $targetId);
        $client             = $this->clientModel->getClientById($clientId);
        $clientActiveProgram = $this->clientModel->clientActiveProgram($clientId);

        return $this->response->setJSON(['status' => 'success', 'statusText' => 'Success', 'message' => empty($graphs['targets']) ? 'No stimulus response chain graph data found.' : 'List', 'data' => $graphs, 'client' => $client, 'clientActiveProgram' => $clientActiveProgram]);
    }

    public function graphsStimulusResponseChainDomains(string $encodedClientId)
    {
        $client_id = (int) decodeValue($encodedClientId);
        if ($client_id <= 0) {
            return $this->response->setJSON([]);
        }
        $model = model(StimulusResponseChainGraphsModel::class);

        return $this->response->setJSON($model->getClientDomains($client_id));
    }

    public function graphsStimulusResponseChainDomainGoals(string $encodedClientId)
    {
        $client_id = (int) decodeValue($encodedClientId);
        $domain_id = (int) $this->request->getPost('domain_id');
        if ($client_id <= 0 || $domain_id <= 0) {
            return $this->response->setJSON([]);
        }
        $model = model(StimulusResponseChainGraphsModel::class);

        return $this->response->setJSON($model->getClientDomainGoals($client_id, $domain_id));
    }

    public function graphsStimulusResponseChainGoalTargets(string $encodedClientId)
    {
        $client_id = (int) decodeValue($encodedClientId);
        $goal_id   = (int) $this->request->getPost('goal_id');
        if ($client_id <= 0 || $goal_id <= 0) {
            return $this->response->setJSON([]);
        }
        $model = model(StimulusResponseChainGraphsModel::class);

        return $this->response->setJSON($model->getClientGoalTargets($client_id, $goal_id));
    }

    public function dailyReport($encodedClientId)
    {
        $this->page_title = 'Client Profile | Daily Report';
        $mtab = 'reports';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Reports/daily', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'           => $mtab,
                'client'         => $client,
                'encodedClientId' => $encodedClientId,
                'page_title'     => $this->page_title,
            ]
        ));
    }

    public function progressReport($encodedClientId)
    {
        $this->page_title = 'Client Profile | Progress Report';
        $mtab = 'reports-progress';
        $client_id = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        if (!$client) {
            return redirect()->to('/client-profile/list')->with('error', 'Client not found.');
        }

        return view('ClientProfile/Reports/progress', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'encodedClientId' => $encodedClientId,
                'page_title' => $this->page_title,
            ]
        ));
    }

    public function progressReportData(string $encodedClientId)
    {
        helper('custom');

        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $progressReportService = new ProgressReportService();
        $result = $progressReportService->listBySubject($clientId, null, null);
        if (!$result['success']) {
            $statusText = ($result['code'] ?? '') === 'DB_SETUP_REQUIRED' ? 'DbSetupRequired' : 'Progress Report';
            $response = $this->getResponseObject('error', $statusText, $result['message'] ?? 'Unable to load reports.', [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $rows = [];
        foreach (($result['data'] ?? []) as $row) {
            $latestStatus = strtoupper((string) ($row['latest_status'] ?? 'DRAFT'));

            $rows[] = [
                'report_id'          => (int) ($row['report_id'] ?? 0),
                'period_from'        => (string) ($row['period_start'] ?? ''),
                'period_to'          => (string) ($row['period_end'] ?? ''),
                'period_from_display' => !empty($row['period_start']) ? app_date($row['period_start']) : '',
                'period_to_display'  => !empty($row['period_end']) ? app_date($row['period_end']) : '',
                'latest_version_no'  => (int) ($row['latest_version_no'] ?? 0),
                'latest_version_id'  => isset($row['latest_version_id']) ? (int) $row['latest_version_id'] : null,
                'latest_status'      => $latestStatus,
                'created_at'         => $row['created_at'] ?? null,
                'created_at_display' => !empty($row['created_at']) ? app_date($row['created_at'], true) : '',
                'updated_at'         => $row['updated_at'] ?? null,
                'updated_at_display' => !empty($row['updated_at']) ? app_date($row['updated_at'], true) : '',
            ];
        }

        $response = $this->getResponseObject('success', 'Progress Reports', 'Listed successfully.', [], $rows);
        return $this->response->setJSON($response);
    }

    public function progressReportVersions(string $encodedClientId)
    {
        helper('custom');

        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $reportId = (int) $this->request->getPost('report_id');
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'report_id is required.', [], []);
            return $this->response->setJSON($response);
        }

        $db = \Config\Database::connect();
        $report = $db->table('report')
            ->select('id, subject_id')
            ->where('id', $reportId)
            ->where('report_type', 'PROGRESS')
            ->where('subject_type', 'LEARNER')
            ->get()
            ->getRowArray();

        if (!$report || (int) ($report['subject_id'] ?? 0) !== $clientId) {
            $response = $this->getResponseObject('error', 'NotFound', 'Progress report not found for this client.', [], []);
            return $this->response->setJSON($response);
        }

        $progressReportService = new ProgressReportService();
        $result = $progressReportService->listVersions($reportId);
        if (!$result['success']) {
            $statusText = ($result['code'] ?? '') === 'DB_SETUP_REQUIRED' ? 'DbSetupRequired' : 'Progress Report Versions';
            $response = $this->getResponseObject('error', $statusText, $result['message'] ?? 'Unable to load report versions.', [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $rows = [];
        foreach (($result['data'] ?? []) as $row) {
            $status = strtoupper((string) ($row['status'] ?? 'DRAFT'));
            if ($status !== 'FINAL') {
                continue;
            }

            $rows[] = [
                'version_id' => (int) ($row['version_id'] ?? 0),
                'version_no' => (int) ($row['version_no'] ?? 0),
                'status' => $status,
                'generated_at' => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name' => trim((string) ($row['generated_by_name'] ?? '')),
                'artifact_id' => isset($row['artifact_id']) ? (int) $row['artifact_id'] : null,
            ];
        }

        $response = $this->getResponseObject('success', 'Progress Report Versions', 'Listed successfully.', [], $rows);
        return $this->response->setJSON($response);
    }

    public function progressReportPdf(string $encodedClientId, int $versionId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        if ($clientId <= 0 || $versionId <= 0) {
            throw new PageNotFoundException('Invalid client or report version.');
        }

        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            throw new PageNotFoundException('Client not found.');
        }

        $progressReportService = new ProgressReportService();
        $version = $progressReportService->getVersionContext($versionId);
        if (!$version) {
            throw new PageNotFoundException('Progress report version not found.');
        }

        if ((int) ($version['subject_id'] ?? 0) !== $clientId) {
            throw new PageNotFoundException('Progress report version not found for this client.');
        }

        if (strtoupper((string) ($version['workflow_status'] ?? 'DRAFT')) !== 'FINAL') {
            throw new PageNotFoundException('Only final progress reports can be viewed.');
        }

        $artifact = $progressReportService->getLatestPdfArtifactByVersion($versionId);
        if (!$artifact) {
            throw new PageNotFoundException('Progress report PDF not found.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim((string) ($artifact['storage_path'] ?? ''), '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('Progress report PDF file not found.');
        }

        return $this->response->download($fullPath, null)->setFileName((string) ($artifact['file_name'] ?? 'progress-report.pdf'));
    }

    private function clientReinforcerNameExists(int $clientId, string $name, ?int $excludeId = null): bool
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT id
            FROM client_mands_reinforcer
            WHERE client_id = ?
              AND LOWER(TRIM(reinforcer_name)) = LOWER(TRIM(?))
        ";
        $params = [$clientId, $name];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id <> ? ";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1 ";
        $row = $db->query($sql, $params)->getFirstRow();
        return $row !== null;
    }

    private function buildCurrentMandListRecord(int $reinforcerId): array
    {
        $reinforcer = $this->clientMandsReinforcerModel->find($reinforcerId);
        if (!$reinforcer) {
            return [];
        }

        $mediaRows = $this->clientMandsReinforcerMediaModel
            ->where('client_reinforcer_id', $reinforcerId)
            ->orderBy('media_type', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $images = [];
        $videos = [];
        foreach ($mediaRows as $media) {
            $row = [
                'id' => (int) ($media->id ?? 0),
                'media_type' => (string) ($media->media_type ?? ''),
                'media_path' => (string) ($media->media_path ?? ''),
            ];

            if (($row['media_type'] ?? '') === 'video') {
                $videos[] = $row;
            } else {
                $images[] = $row;
            }
        }

        return [
            'id' => (int) ($reinforcer->id ?? 0),
            'client_id' => (int) ($reinforcer->client_id ?? 0),
            'reinforcer_name' => (string) ($reinforcer->reinforcer_name ?? ''),
            'introduced_at' => (string) ($reinforcer->introduced_at ?? ''),
            'introduced_at_display' => !empty($reinforcer->introduced_at) ? app_date((string) $reinforcer->introduced_at) : '',
            'vocal_sign' => (string) ($reinforcer->vocal_sign ?? ''),
            'description' => (string) ($reinforcer->description ?? ''),
            'images' => $images,
            'videos' => $videos,
            'image_count' => count($images),
            'video_count' => count($videos),
        ];
    }

    private function resolveCurrentMandUploadedFile(string $mediaType): ?UploadedFile
    {
        $primaryKey = $mediaType === 'image' ? 'image_file' : 'video_file';
        $file = $this->request->getFile($primaryKey);
        if ($file instanceof UploadedFile && ($file->isValid() || $file->getError() !== UPLOAD_ERR_NO_FILE)) {
            return $file;
        }

        $file = $this->request->getFile('file');
        if ($file instanceof UploadedFile && ($file->isValid() || $file->getError() !== UPLOAD_ERR_NO_FILE)) {
            return $file;
        }

        return null;
    }

    private function loadCurrentMandMediaSettings(): array
    {
        $imageTypes = $this->sanitizeCurrentMandTypesInput(
            setting('ClientMandReinforcer.imageAllowedTypes'),
            self::IMAGE_TYPES_AVAILABLE,
            self::DEFAULT_IMAGE_TYPES
        );
        $videoTypes = $this->sanitizeCurrentMandTypesInput(
            setting('ClientMandReinforcer.videoAllowedTypes'),
            self::VIDEO_TYPES_AVAILABLE,
            self::DEFAULT_VIDEO_TYPES
        );

        $imageMaxSizeMb = (int) (setting('ClientMandReinforcer.imageMaxSizeMb') ?? self::DEFAULT_IMAGE_MAX_SIZE_MB);
        $videoMaxSizeMb = (int) (setting('ClientMandReinforcer.videoMaxSizeMb') ?? self::DEFAULT_VIDEO_MAX_SIZE_MB);

        if ($imageMaxSizeMb < 1 || $imageMaxSizeMb > self::IMAGE_MAX_SIZE_LIMIT_MB) {
            $imageMaxSizeMb = self::DEFAULT_IMAGE_MAX_SIZE_MB;
        }
        if ($videoMaxSizeMb < 1 || $videoMaxSizeMb > self::VIDEO_MAX_SIZE_LIMIT_MB) {
            $videoMaxSizeMb = self::DEFAULT_VIDEO_MAX_SIZE_MB;
        }
        $imageMaxCount = (int) (setting('ClientMandReinforcer.imageMaxCount') ?? self::DEFAULT_IMAGE_MAX_COUNT);
        $videoMaxCount = (int) (setting('ClientMandReinforcer.videoMaxCount') ?? self::DEFAULT_VIDEO_MAX_COUNT);

        if ($imageMaxCount < 1 || $imageMaxCount > self::IMAGE_MAX_COUNT_LIMIT) {
            $imageMaxCount = self::DEFAULT_IMAGE_MAX_COUNT;
        }
        if ($videoMaxCount < 1 || $videoMaxCount > self::VIDEO_MAX_COUNT_LIMIT) {
            $videoMaxCount = self::DEFAULT_VIDEO_MAX_COUNT;
        }

        return [
            'image_types' => $imageTypes,
            'video_types' => $videoTypes,
            'image_max_size_mb' => $imageMaxSizeMb,
            'video_max_size_mb' => $videoMaxSizeMb,
            'image_max_count' => $imageMaxCount,
            'video_max_count' => $videoMaxCount,
        ];
    }

    private function sanitizeCurrentMandTypesInput($input, array $allowed, array $default): array
    {
        $normalized = [];

        if (is_string($input)) {
            $trimmed = trim($input);
            if ($trimmed !== '') {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                } else {
                    $input = explode(',', $trimmed);
                }
            } else {
                $input = [];
            }
        }

        if (!is_array($input)) {
            $input = [];
        }

        foreach ($input as $item) {
            $item = strtolower(trim((string) $item));
            if ($item !== '' && in_array($item, $allowed, true) && !in_array($item, $normalized, true)) {
                $normalized[] = $item;
            }
        }

        if (empty($normalized)) {
            return $default;
        }

        return $normalized;
    }

    private function uploadCurrentMandMediaFile(UploadedFile $file, string $mediaType, array $settings): array
    {
        if (!$file->isValid()) {
            return ['success' => false, 'path' => null, 'message' => 'Uploaded file is not valid.'];
        }

        $ext = strtolower((string) $file->getExtension());
        if ($ext === '') {
            $ext = strtolower((string) pathinfo((string) $file->getClientName(), PATHINFO_EXTENSION));
        }

        $isImage = $mediaType === 'image';
        $allowedTypes = $isImage ? ($settings['image_types'] ?? self::DEFAULT_IMAGE_TYPES) : ($settings['video_types'] ?? self::DEFAULT_VIDEO_TYPES);
        $maxSizeMb = $isImage ? (int) ($settings['image_max_size_mb'] ?? self::DEFAULT_IMAGE_MAX_SIZE_MB) : (int) ($settings['video_max_size_mb'] ?? self::DEFAULT_VIDEO_MAX_SIZE_MB);
        $maxSizeBytes = $maxSizeMb * 1024 * 1024;

        if ($ext === '' || !in_array($ext, $allowedTypes, true)) {
            return ['success' => false, 'path' => null, 'message' => ucfirst($mediaType) . ' type is not allowed.'];
        }

        if ($file->getSize() > $maxSizeBytes) {
            return ['success' => false, 'path' => null, 'message' => ucfirst($mediaType) . ' file size exceeds ' . $maxSizeMb . ' MB.'];
        }

        $subDirectory = $isImage ? 'images' : 'videos';
        $targetDirectory = WRITEPATH . 'uploads/client-mands-reinforcer/' . $subDirectory . '/';
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $newName = $mediaType . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($targetDirectory, $newName, true);

        $fullPath = $targetDirectory . $newName;
        if (!is_file($fullPath)) {
            return ['success' => false, 'path' => null, 'message' => 'Unable to store ' . $mediaType . ' file.'];
        }

        $relativePath = self::MEDIA_ROOT . $subDirectory . '/' . $newName;
        return ['success' => true, 'path' => $relativePath, 'message' => ''];
    }

    private function resolveCurrentMandMediaPath(string $relativePath): ?string
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            return null;
        }

        $normalized = str_replace(['\\', '..'], ['/', ''], ltrim($relativePath, '/'));
        if (!str_starts_with($normalized, self::MEDIA_ROOT)) {
            return null;
        }

        return WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
    }

    private function deleteCurrentMandMediaFile(string $relativePath): void
    {
        $fullPath = $this->resolveCurrentMandMediaPath($relativePath);
        if ($fullPath !== null && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function getCurrentMandMediaMimeType(string $fullPath, string $mediaType): string
    {
        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($mediaType === 'image') {
            $map = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ];
            return $map[$ext] ?? 'application/octet-stream';
        }

        $map = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }

    /*    private function getSelectedClientCurrentProgramSummary($client_id)
    {
    //Sample
        $programData = [
            "client_id" => 101,
            "client_name" => "Omar Al-Hassan",
            "program_summary" => [
                "program_start" => "2025-03-01",
                "program_age" => "240 days",   // Computed on backend
                "total_domains" => 3,
                "total_domains_mastered" => 3,
                "total_goals" => 9,
                "total_goals_mastered" => 9,
                "total_targets" => 30,
                "total_targets_mastered" => 30,
                "program_changes_alerts" => 16,
                "program_changes" => 6,
                "days" => "90 Days"
            ],
            "domains" => [
                [
                    "domain_id" => 1,
                    "domain_name" => "Cognitive Skills",
                    "introduced_on" => "2025-03-01",
                    "is_mastered" => false,
                    "total_goals" => 3,
                    "mastered_goals" => 1,
                    "total_targets" => 9,
                    "mastered_targets" => 6,
                    "goals" => [
                        [
                            "goal_id" => 11,
                            "goal_name" => "Problem Solving",
                            "average_mastery_days" => 22,
                            "is_mastered" => false,
                            "total_targets" => 3,
                            "mastered_targets" => 2,
                            "targets" => [
                                ["target_name" => "Solve 3-step tasks", "status" => "Mastered", "introduced_on" => "2025-03-15", "mastered_on" => "2025-04-07", "duration_days" => 23, "sessions_count" => 8],
                                ["target_name" => "Apply reasoning to puzzles", "status" => "Mastered", "introduced_on" => "2025-05-10", "mastered_on" => "2025-06-05", "duration_days" => 26, "sessions_count" => 9],
                                ["target_name" => "Use logical reasoning", "status" => "In Progress", "introduced_on" => "2025-08-01", "mastered_on" => null, "duration_days" => 78, "sessions_count" => 12],
                            ]
                        ],
                        [
                            "goal_id" => 12,
                            "goal_name" => "Memory Retention",
                            "average_mastery_days" => 28,
                            "is_mastered" => false,
                            "total_targets" => 3,
                            "mastered_targets" => 2,
                            "targets" => [
                                ["target_name" => "Recall 5 objects", "status" => "Mastered", "introduced_on" => "2025-04-01", "mastered_on" => "2025-04-25", "duration_days" => 24, "sessions_count" => 7],
                                ["target_name" => "Remember sequences", "status" => "In Progress", "introduced_on" => "2025-09-10", "mastered_on" => null, "duration_days" => 45, "sessions_count" => 6],
                                ["target_name" => "Identify missing objects", "status" => "Mastered", "introduced_on" => "2025-05-05", "mastered_on" => "2025-05-28", "duration_days" => 23, "sessions_count" => 8],
                            ]
                        ],
                        [
                            "goal_id" => 13,
                            "goal_name" => "Attention Control",
                            "average_mastery_days" => 25,
                            "is_mastered" => true,
                            "total_targets" => 3,
                            "mastered_targets" => 3,
                            "targets" => [
                                ["target_name" => "Sustain attention for 5 min", "status" => "Mastered", "introduced_on" => "2025-05-10", "mastered_on" => "2025-06-01", "duration_days" => 22, "sessions_count" => 7],
                                ["target_name" => "Avoid distractions", "status" => "Mastered", "introduced_on" => "2025-06-05", "mastered_on" => "2025-06-28", "duration_days" => 23, "sessions_count" => 8],
                                ["target_name" => "Shift attention between tasks", "status" => "Mastered", "introduced_on" => "2025-07-10", "mastered_on" => "2025-09-05", "duration_days" => 57, "sessions_count" => 10],
                            ]
                        ]
                    ]
                ],
                [
                    "domain_id" => 2,
                    "domain_name" => "Communication",
                    "introduced_on" => "2025-04-15",
                    "is_mastered" => false,
                    "total_goals" => 3,
                    "mastered_goals" => 1,
                    "total_targets" => 9,
                    "mastered_targets" => 6,
                    "goals" => [] // truncated for brevity
                ],
                [
                    "domain_id" => 3,
                    "domain_name" => "Social Interaction",
                    "introduced_on" => "2025-06-10",
                    "is_mastered" => false,
                    "total_goals" => 3,
                    "mastered_goals" => 0,
                    "total_targets" => 12,
                    "mastered_targets" => 9,
                    "goals" => [] // truncated for brevity
                ]
            ]
        ];

        return $programData;
    }    
   
    // Background Information → Diagnosis / Medical Info
    public function diagnosis(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Diagnosis / Medical Info';
        $mtab = 'diagnosis';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/diagnosis', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    // Background Information → Placement
    public function placement(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Placement';
        $mtab = 'placement';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/placement', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    // Background Information → Curriculum
    public function curriculum(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Curriculum';
        $mtab = 'curriculum';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/curriculum', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    // Background Information → Reinforcers
    public function reinforcers(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Reinforcers';
        $mtab = 'reinforcers';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/reinforcers', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    // Background Information → Items / Activities to Avoid
    public function avoidItems(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Items / Activities to Avoid';
        $mtab = 'avoid-items';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/avoid-items', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    }

    // Background Information → Team Communication
    public function messages(string $encodedClientId)
    {
        $this->page_title = 'Client Profile | Team Communication';
        $mtab = 'messages';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Background/messages', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'       => $mtab,
                'client'     => $client,
                'page_title' => $this->page_title,
            ]
        ));
    } */

    /******************************************************************** */
    // Phase 4: Daily Report Lifecycle (profile-owned)
    /******************************************************************** */

    public function dailyReportData(string $encodedClientId)
    {
        helper('custom');

        $clientId = (int) decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($clientId);
        if (!$client) {
            $response = $this->getResponseObject('error', 'NotFound', 'Client not found.', [], []);
            return $this->response->setJSON($response);
        }

        $queryModel = new DailyReportQueryModel();
        $rows = $queryModel->listBySubject($clientId, null, null);
        $data = [];
        foreach ($rows as $row) {
            $emailStatus = $row['email_status'] ?? ReportEmailStatus::NOT_SENT;
            $latestStatus = strtoupper(trim((string) ($row['latest_status'] ?? 'FINAL')));
            if ($latestStatus === '') {
                $latestStatus = 'FINAL';
            }

            $data[] = [
                'report_id'            => (int) ($row['report_id'] ?? 0),
                'subject_id'           => (int) ($row['subject_id'] ?? 0),
                'internal_mrn'         => $row['internal_mrn'] ?? '',
                'learner_name'         => trim((string) ($row['learner_name'] ?? '')),
                'report_date'          => $row['report_date'] ?? '',
                'report_date_display'  => !empty($row['report_date']) ? app_date($row['report_date']) : '',
                'latest_version_no'    => (int) ($row['latest_version_no'] ?? 0),
                'latest_version_id'    => isset($row['version_id']) ? (int) $row['version_id'] : null,
                'latest_artifact_id'   => isset($row['latest_artifact_id']) ? (int) $row['latest_artifact_id'] : null,
                'latest_status'        => $latestStatus,
                'generated_at'         => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name'    => trim((string) ($row['generated_by_name'] ?? '')),
                'email_status'         => $emailStatus,
                'email_status_label'   => ReportEmailStatus::label($emailStatus),
                'email_sent_at'        => $row['email_sent_at'] ?? null,
                'email_action_by_name' => trim((string) ($row['email_action_by_name'] ?? '')),
            ];
        }

        $response = $this->getResponseObject('success', 'DailyReports', 'Listed successfully', [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportCheckGenerate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $reportDate = trim((string) $this->request->getPost('report_date'));

        if ($clientId <= 0 || !$this->isValidYmd($reportDate)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Valid report_date is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->checkGenerateDraft($clientId, $reportDate);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NO_SESSION' => 'NoSession',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Validation_Error',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'GenerateCheck', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function dailyReportGenerate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $reportDate = trim((string) $this->request->getPost('report_date'));

        if ($clientId <= 0 || !$this->isValidYmd($reportDate)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Valid report_date is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->createDraft($clientId, $reportDate, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NO_SESSION' => 'NoSession',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'DailyReport',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $versionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $versionId > 0 ? base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $versionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'DailyReport', 'Draft generated successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportVersions(string $encodedClientId)
    {
        helper('custom');

        $reportId = (int) $this->request->getPost('report_id');
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'report_id is required.', [], []);
            return $this->response->setJSON($response);
        }

        $queryModel = new DailyReportQueryModel();
        $rows = $queryModel->listVersionsByReportId($reportId);
        $data = [];
        foreach ($rows as $row) {
            $emailStatus = $row['email_status'] ?? ReportEmailStatus::NOT_SENT;
            $status = strtoupper(trim((string) ($row['status'] ?? 'FINAL')));
            if ($status === '') {
                $status = 'FINAL';
            }

            $data[] = [
                'version_id'           => (int) ($row['version_id'] ?? 0),
                'version_no'           => (int) ($row['version_no'] ?? 0),
                'status'               => $status,
                'generated_at'         => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name'    => trim((string) ($row['generated_by_name'] ?? '')),
                'artifact_id'          => isset($row['artifact_id']) ? (int) $row['artifact_id'] : null,
                'file_name'            => (string) ($row['file_name'] ?? ''),
                'email_status'         => $emailStatus,
                'email_status_label'   => ReportEmailStatus::label($emailStatus),
                'email_sent_at'        => $row['email_sent_at'] ?? null,
                'email_action_by_name' => trim((string) ($row['email_action_by_name'] ?? '')),
            ];
        }

        $response = $this->getResponseObject('success', 'DailyReportVersions', 'Listed successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportDraft(string $encodedClientId, int $versionId)
    {
        helper('custom');

        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $version = $this->dailyReportService->getVersionContext($versionId);
        if (!$version) {
            throw new PageNotFoundException('Daily Report draft not found.');
        }

        $workflowStatus = strtoupper(trim((string) ($version['workflow_status'] ?? 'FINAL')));
        if ($workflowStatus === '' || $workflowStatus === 'FINAL') {
            return redirect()->to(base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $versionId . '/pdf'));
        }

        $manualData = $this->decodeJsonObjectCp((string) ($version['manual_json'] ?? '{}'));
        $snapshot = $this->decodeJsonObjectCp((string) ($version['snapshot_json'] ?? '{}'));
        $sectionStatus = $this->decodeJsonObjectCp((string) ($version['section_status_json'] ?? '{}'));
        $dailySectionData = [];
        if (isset($snapshot['sections']['daily_content']['data']) && is_array($snapshot['sections']['daily_content']['data'])) {
            $dailySectionData = $snapshot['sections']['daily_content']['data'];
        }

        $this->page_title = 'Client Profile | Daily Report Draft';

        return view('ClientProfile/Reports/Daily/draft', [
            'page_title'         => $this->page_title,
            'version'            => $version,
            'manual_data'        => $manualData,
            'draft_section_data' => $dailySectionData,
            'section_status'     => $sectionStatus,
            'daily_image_limits' => $this->dailyReportService->getDailyImageLimits(),
            'encodedClientId'    => $encodedClientId,
            'daily_list_url'     => base_url('client-profile/reports/daily/' . $encodedClientId),
        ]);
    }

    public function dailyReportSaveDraft(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $manualData = [];
        $manualJson = trim((string) $this->request->getPost('manual_json'));
        if ($manualJson !== '') {
            $decoded = json_decode($manualJson, true);
            if (!is_array($decoded)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'manual_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualData = $decoded;
        }

        $result = $this->dailyReportService->saveDraft($versionId, $manualData, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function dailyReportPullSection(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionKey = trim((string) $this->request->getPost('section_key'));
        if ($sectionKey === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_key is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->pullSectionData($versionId, $sectionKey, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function dailyReportImages(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->listDailyImages($versionId);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportUploadImages(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $files = $this->request->getFileMultiple('images');
        if (!is_array($files) || empty($files)) {
            $single = $this->request->getFile('images');
            $files = $single ? [$single] : [];
        }

        $result = $this->dailyReportService->uploadDailyImages($versionId, $files, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportDeleteImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteDailyImage($versionId, $artifactId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportReplaceImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $file = $this->request->getFile('image');
        if (!$file) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Image file is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->replaceDailyImage($versionId, $artifactId, $file, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportViewImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            throw new PageNotFoundException('Invalid image reference.');
        }

        $artifact = $this->dailyReportService->getDailyImageArtifact($versionId, $artifactId);
        if (!$artifact) {
            throw new PageNotFoundException('Daily image not found.');
        }

        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $mimeType = trim((string) ($artifact['mime_type'] ?? 'application/octet-stream'));
        if ($storagePath === '') {
            throw new PageNotFoundException('Daily image path missing.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('Daily image file not found.');
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read daily image file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($content);
    }

    public function dailyReportFinalize(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->finalizeDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'TEMPLATE_FILE_MISSING' => 'TemplateFileMissing',
                'DRAFT_LOCKED' => 'DraftLocked',
                'FINALIZE_VALIDATION_ERROR' => 'DailyReport',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['pdf_url'] = base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $versionId . '/pdf');
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportRegenerate(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->regenerateDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'NOT_FINAL' => 'NotFinal',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $newVersionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $newVersionId > 0 ? base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $newVersionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function dailyReportDeleteVersion(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteLatestVersion($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NOT_FOUND' => 'NotFound',
                'NOT_LATEST' => 'NotLatestVersion',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function dailyReportDeleteAll(string $encodedClientId, int $reportId)
    {
        $reportId = (int) $reportId;
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid report id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteAllVersions($reportId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function dailyReportPdf(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $queryModel = new DailyReportQueryModel();
        $artifact = $queryModel->getLatestPdfArtifactByVersion($versionId);
        if (!$artifact) {
            throw new PageNotFoundException('PDF artifact not found.');
        }

        $fullPath = $this->dailyReportService->resolvePdfPathForDownload($versionId, $artifact);
        if ($fullPath === null || !is_file($fullPath)) {
            throw new PageNotFoundException('PDF file not found.');
        }

        return $this->response->download($fullPath, null)->setFileName((string) ($artifact['file_name'] ?? 'daily-report.pdf'));
    }

    public function dailyReportSend(string $encodedClientId)
    {
        $versionId = (int) $this->request->getPost('version_id');
        $toEmail = trim((string) $this->request->getPost('to_email'));
        $ccEmail = trim((string) $this->request->getPost('cc_email'));

        if ($versionId <= 0 || $toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'version_id and valid to_email are required.', [], []);
            return $this->response->setJSON($response);
        }

        $service = new DailyReportEmailService();
        $result = $service->send($versionId, $toEmail, $ccEmail, auth()->user()->id ?? null);
        if (!$result['success']) {
            $response = $this->getResponseObject('error', 'DailyReportEmail', $result['message'], [], []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReportEmail', 'Email request logged successfully.', [], $result['data']);
        return $this->response->setJSON($response);
    }

    /******************************************************************** */
    // Phase 4: Progress Report Lifecycle (profile-owned, additional)
    /******************************************************************** */

    public function progressReportCheckGenerate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $periodFrom = trim((string) $this->request->getPost('period_from'));
        $periodTo = trim((string) $this->request->getPost('period_to'));

        if ($clientId <= 0 || !$this->isValidYmd($periodFrom) || !$this->isValidYmd($periodTo)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'period_from and period_to are required.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->checkGenerateDraft($clientId, $periodFrom, $periodTo);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'EXACT_PERIOD_EXISTS' => 'ExactPeriodExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Validation_Error',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'GenerateCheck', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function progressReportGenerate(string $encodedClientId)
    {
        $clientId = (int) decodeValue($encodedClientId);
        $periodFrom = trim((string) $this->request->getPost('period_from'));
        $periodTo = trim((string) $this->request->getPost('period_to'));

        if ($clientId <= 0 || !$this->isValidYmd($periodFrom) || !$this->isValidYmd($periodTo)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'period_from and period_to are required.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->createDraft($clientId, $periodFrom, $periodTo, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'EXACT_PERIOD_EXISTS' => 'ExactPeriodExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $versionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $versionId > 0 ? base_url('client-profile/reports/progress/' . $encodedClientId . '/version/' . $versionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'Progress Report', 'Draft generated successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportDraft(string $encodedClientId, int $versionId)
    {
        helper('custom');

        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $progressService = new ProgressReportService();
        $version = $progressService->getVersionContext($versionId);
        if (!$version) {
            throw new PageNotFoundException('Progress Report draft not found.');
        }

        $workflowStatus = strtoupper((string) ($version['workflow_status'] ?? 'DRAFT'));
        if ($workflowStatus === 'FINAL') {
            return redirect()->to(base_url('client-profile/reports/progress/version/' . $encodedClientId . '/' . $versionId . '/pdf'));
        }

        $this->page_title = 'Client Profile | Progress Report Draft';

        return view('ClientProfile/Reports/Progress/draft', [
            'page_title'                  => $this->page_title,
            'version'                     => $version,
            'manual_data'                 => $this->decodeJsonObjectCp((string) ($version['manual_json'] ?? '{}')),
            'instructional_image_limits'  => $progressService->getInstructionalImageLimits(),
            'encodedClientId'             => $encodedClientId,
            'progress_list_url'           => base_url('client-profile/reports/progress/' . $encodedClientId),
        ]);
    }

    public function progressReportSaveDraft(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $manualData = [
            'approved_by' => trim((string) $this->request->getPost('approved_by')),
            'draft_notes' => trim((string) $this->request->getPost('draft_notes')),
        ];

        $manualJson = trim((string) $this->request->getPost('manual_json'));
        if ($manualJson !== '') {
            $decoded = json_decode($manualJson, true);
            if (!is_array($decoded)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'manual_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualData = array_merge($manualData, $decoded);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->saveDraft($versionId, $manualData, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function progressReportPullSection(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionKey = trim((string) $this->request->getPost('section_key'));
        if ($sectionKey === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_key is required.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->pullSectionData($versionId, $sectionKey, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function progressReportUpdateSectionState(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionKey = trim((string) $this->request->getPost('section_key'));
        if ($sectionKey === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_key is required.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionDataJson = trim((string) $this->request->getPost('section_data_json'));
        if ($sectionDataJson === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_data_json is required.', [], []);
            return $this->response->setJSON($response);
        }
        $sectionData = json_decode($sectionDataJson, true);
        if (!is_array($sectionData)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_data_json must be valid JSON object.', [], []);
            return $this->response->setJSON($response);
        }

        $pulledAt = trim((string) $this->request->getPost('pulled_at'));
        $manualPatch = [];

        $instructionalDomainCommentsJson = trim((string) $this->request->getPost('instructional_domain_comments_json'));
        if ($instructionalDomainCommentsJson !== '') {
            $instructionalDomainComments = json_decode($instructionalDomainCommentsJson, true);
            if (!is_array($instructionalDomainComments)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'instructional_domain_comments_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualPatch['instructional_programmes_domain_comments'] = $instructionalDomainComments;
        }

        $progressService = new ProgressReportService();
        $result = $progressService->updateSectionState(
            $versionId,
            $sectionKey,
            $sectionData,
            $pulledAt,
            $manualPatch,
            auth()->user()->id ?? null
        );
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function progressReportInstructionalImages(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->listInstructionalImages($versionId);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportUploadInstructionalImages(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $files = $this->request->getFileMultiple('images');
        if (!is_array($files) || empty($files)) {
            $single = $this->request->getFile('images');
            $files = $single ? [$single] : [];
        }

        $progressService = new ProgressReportService();
        $result = $progressService->uploadInstructionalImages($versionId, $files, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportDeleteInstructionalImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->deleteInstructionalImage($versionId, $artifactId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportReplaceInstructionalImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $file = $this->request->getFile('image');
        if (!$file) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Image file is required.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->replaceInstructionalImage($versionId, $artifactId, $file, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImagesProfile($encodedClientId, $versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportViewInstructionalImage(string $encodedClientId, int $versionId, int $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            throw new PageNotFoundException('Invalid image reference.');
        }

        $progressService = new ProgressReportService();
        $artifact = $progressService->getInstructionalImageArtifact($versionId, $artifactId);
        if (!$artifact) {
            throw new PageNotFoundException('Instructional image not found.');
        }

        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $mimeType = trim((string) ($artifact['mime_type'] ?? 'application/octet-stream'));
        if ($storagePath === '') {
            throw new PageNotFoundException('Instructional image path missing.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('Instructional image file not found.');
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read instructional image file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($content);
    }

    public function progressReportFinalize(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->finalizeDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'TEMPLATE_FILE_MISSING' => 'TemplateFileMissing',
                'DRAFT_LOCKED' => 'DraftLocked',
                'FINALIZE_VALIDATION_ERROR' => 'Progress Report',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['pdf_url'] = base_url('client-profile/reports/progress/version/' . $encodedClientId . '/' . $versionId . '/pdf');
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportRegenerate(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->regenerateDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'NOT_FINAL' => 'NotFinal',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $newVersionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $newVersionId > 0 ? base_url('client-profile/reports/progress/' . $encodedClientId . '/version/' . $newVersionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function progressReportDeleteVersion(string $encodedClientId, int $versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->deleteLatestVersion($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'NOT_LATEST' => 'NotLatestVersion',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function progressReportDeleteAll(string $encodedClientId, int $reportId)
    {
        $reportId = (int) $reportId;
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid report id.', [], []);
            return $this->response->setJSON($response);
        }

        $progressService = new ProgressReportService();
        $result = $progressService->deleteAllVersions($reportId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    /******************************************************************** */
    // Phase 4: Private helpers
    /******************************************************************** */

    private function isValidYmd(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    private function decodeJsonObjectCp(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function decorateDailyImagesProfile(string $encodedClientId, int $versionId, array $images): array
    {
        foreach ($images as &$image) {
            $artifactId = (int) ($image['artifact_id'] ?? 0);
            if ($artifactId <= 0) {
                continue;
            }
            $cacheVersion = trim((string) ($image['file_name'] ?? ''));
            if ($cacheVersion === '') {
                $cacheVersion = 'artifact-' . $artifactId;
            }
            $image['view_url'] = base_url('client-profile/reports/daily/' . $encodedClientId . '/version/' . $versionId . '/images/' . $artifactId . '/view')
                . '?v=' . rawurlencode($cacheVersion);
        }
        unset($image);
        return $images;
    }

    private function decorateInstructionalImagesProfile(string $encodedClientId, int $versionId, array $images): array
    {
        $decorated = [];
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }
            $artifactId = (int) ($image['artifact_id'] ?? 0);
            if ($artifactId <= 0) {
                continue;
            }
            $image['view_url'] = base_url('client-profile/reports/progress/' . $encodedClientId . '/version/' . $versionId . '/instructional-images/' . $artifactId . '/view');
            $decorated[] = $image;
        }
        return $decorated;
    }
}
