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
use App\Services\Reports\ProgressReportService;
use App\Services\ClientProfileDashboardService;
use App\Models\ClientProblemBehavior\ClientAbcItemModel;
use App\Models\ClientProblemBehavior\MasterAbcItemModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Exceptions\PageNotFoundException;


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

    public function dailyReport($encodedClientId)
    {
        $this->page_title = 'Client Profile | Daily Report';
        $mtab = 'reports';
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->getClientById($client_id);

        return view('ClientProfile/Reports/daily', array_merge(
            $this->getCommonClientData($encodedClientId),
            [
                'mtab'  => $mtab,
                'client' => $client,
                'page_title' => $this->page_title,
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
            if ($latestStatus !== 'FINAL') {
                continue;
            }

            $rows[] = [
                'report_id' => (int) ($row['report_id'] ?? 0),
                'period_from' => (string) ($row['period_start'] ?? ''),
                'period_to' => (string) ($row['period_end'] ?? ''),
                'period_from_display' => !empty($row['period_start']) ? app_date($row['period_start']) : '',
                'period_to_display' => !empty($row['period_end']) ? app_date($row['period_end']) : '',
                'latest_version_no' => (int) ($row['latest_version_no'] ?? 0),
                'latest_version_id' => isset($row['latest_version_id']) ? (int) $row['latest_version_id'] : null,
                'latest_status' => $latestStatus,
                'created_at' => $row['created_at'] ?? null,
                'created_at_display' => !empty($row['created_at']) ? app_date($row['created_at'], true) : '',
                'updated_at' => $row['updated_at'] ?? null,
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
}
