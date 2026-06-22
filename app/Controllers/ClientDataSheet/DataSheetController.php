<?php

namespace App\Controllers\ClientDataSheet;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientDataSheet\ClientDataSheetModel;

use App\Models\MasterProgram\TargetPhaseModel;
use App\Models\MasterProgram\TargetProbeSetModel;

use App\Models\ClientProgram\ClientDomainModel;

use App\Models\Mands\MandsSessionDataModel;

use App\Models\ClientProblemBehavior\DailySessionsPbRecordsModel;

use App\Models\ClientSessions\DailySessionDataCollectionModel;



//use function PHPUnit\Framework\isNull;

class DataSheetController extends AdminController
{
    use ResponseTrait;

    protected $clientModel;

    protected $clientDataSheetModel;
    protected $targetPhaseModel;
    protected $targetProbeSetModel;
    protected $clientDomainModel;
    protected $mandsSessionDataModel;
    protected $pbRecordsModel;
    protected $collectionModel;



    public function __construct()
    {

        $this->clientModel = new ClientModel();
        $this->clientDomainModel = new ClientDomainModel();
        $this->targetPhaseModel = new TargetPhaseModel();
        $this->targetProbeSetModel = new TargetProbeSetModel();
        $this->clientDataSheetModel = new ClientDataSheetModel();
        $this->mandsSessionDataModel = new MandsSessionDataModel();
        $this->pbRecordsModel = new DailySessionsPbRecordsModel();
        $this->collectionModel = new DailySessionDataCollectionModel();
    }
    /******************************************************************** */
    // Clients for Daily Session. only assigned and active clients will display
    /******************************************************************** */
    public function index()
    {

        $clients = $this->clientModel->get_active_client_list();

        $this->page_title = 'Clients for data sheet';
        return  view(
            'ClientDataSheet/index',
            [
                'clients' => $clients,
                'page_title' => $this->page_title
            ]
        );
    }
    public function programData($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);
        $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, null);
        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        $domains = $this->clientDataSheetModel->getDomains($client_id);

        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/programsData',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }
    public function filterProgramData()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probeSet = $this->request->getPost('probeSet');

        $client = $this->clientModel->find($client_id);

        $clientProbeSetIds = null;
        if ($probeSet !== '') {
            $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, $probeSet);
        }

        if ($domain_id == '') {
            $domain_id = null;
        }

        if ($goal_id == '') {
            $goal_id = null;
        }
        $clientProgramData = []; // Or an appropriate response for empty results
        if ($clientProbeSetIds !== []) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds, $domain_id, $goal_id);
        }


        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/programsDataTable',
            [
                'client' => $client,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
            ]
        );
    }
    public function transitionListYesNoPercentageProbe()
    {
        $collection_id = $this->request->getPost('collection_id');
        $sessionData = $this->collectionModel->getSingle($collection_id);
        $collectedData = json_decode($sessionData->collected_data, true);
        $transitions = $collectedData['transitions'] ?? [];
        $html =  view(
            'ClientDataSheet/transitionList',
            [
                'transitions' => $transitions,

            ]
        );
        return $this->response->setJSON(['html' => $html]);
    }
    public function stimulusTargetStepsDetail()
    {
        $target_id = $this->request->getPost('target_id');

        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();
        $stepModel = new \App\Models\ClientProgram\ClientStimulusStepModel();

        // 1. Get all steps
        $steps = $stepModel->where('target_id', $target_id)->orderBy('step_number')->findAll();

        // 2. Get all processed collections for this target
        $collections = $this->collectionModel
            ->where('target_id', $target_id)
            ->where('is_processed', 1)
            ->orderBy('session_date', 'ASC')
            ->findAll();

        // 3. Build session map: group collections by date
        $sessionDates = [];  // ['2024-05-01' => [id1, id2]]
        foreach ($collections as $col) {
            $date = $col->session_date;
            if (!isset($sessionDates[$date])) $sessionDates[$date] = [];
            $sessionDates[$date][] = $col->id;
        }

        // 4. Get all step session inputs (safe from empty IN clause)
        $collectionIds = array_merge(...array_values($sessionDates));
        $stepInputs = [];

        if (!empty($collectionIds)) {
            $stepInputs = $stepSessionModel
                ->where('target_id', $target_id)
                ->whereIn('collection_id', $collectionIds)
                ->findAll();
        }

        // 5. Build matrix: [step_id][date] => value(s)
        $matrix = [];

        foreach ($stepInputs as $input) {
            $stepId = $input['step_id'];
            $date = $input['session_date'];
            $val = $input['input_result'] ?? '';

            if (!isset($matrix[$stepId])) $matrix[$stepId] = [];
            if (!isset($matrix[$stepId][$date])) $matrix[$stepId][$date] = [];

            $matrix[$stepId][$date][] = $val;
        }
        $chainModel = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $chain = $chainModel->where('target_id', $target_id)->first();

        $chainLabel = null;
        if ($chain) {
            $labelText = match ($chain->method) {
                'backward' => 'Backward Chain',
                'forward' => 'Forward Chain',
                'total_task' => 'Total Task Chain',
                default => ucfirst($chain->method) . ' Chain'
            };

            $badgeClass = match ($chain->method) {
                'backward' => 'info',
                'forward' => 'info',
                'total_task' => 'info',
                default => 'secondary'
            };

            $chainLabel = "<span class='badge bg-$badgeClass'>$labelText</span>";
        }

        $html = view('ClientDataSheet/stimulusTargetStepsDetail', [
            'steps' => $steps,
            'matrix' => $matrix,
            'sessionDates' => array_keys($sessionDates),
            'chainLabel' => $chainLabel,
        ]);
        return $this->response->setJSON(['html' => $html]);
    }




    /*public function yesNoProbeDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'yes_no');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, null);
        }

        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        // For Yes/No probes
        $domains = $this->clientDataSheetModel->getFilteredDomainsByProbeType($client_id, 'yes_no');


        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/programsData',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }
    public function filterYesNoDataSheet()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'yes_no');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds, $domain_id, $goal_id);
        }



        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();



        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/yesNoProbeTable',
            [
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
            ]
        );
    }

    public function promptLevelProbeDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);


        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'prompt_level');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds);
        }

        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        // For Count probes
        $domains = $this->clientDataSheetModel->getFilteredDomainsByProbeType($client_id, 'prompt_level');

        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/promptLevelProbe',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }

    public function trafficLightProbeDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'traffic_light');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds);
        }

        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        // For Count probes
        $domains = $this->clientDataSheetModel->getFilteredDomainsByProbeType($client_id, 'traffic_light');



        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/trafficLightProbe',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }

    public function durationProbeDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'duration');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds);
        }

        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        // For Count probes
        $domains = $this->clientDataSheetModel->getFilteredDomainsByProbeType($client_id, 'duration');


        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/durationProbe',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }

    public function countProbeDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, 'count');

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds);
        }
        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();
        // For Count probes
        $domains = $this->clientDataSheetModel->getFilteredDomainsByProbeType($client_id, 'count');

        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/countProbe',
            [
                'client' => $client,
                'domains' => $domains,
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
                'page_title' => $this->page_title
            ]
        );
    }

    public function filterDataSheet()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probeType = $this->request->getPost('probe_type');

        $clientProbeSetIds = $this->clientDataSheetModel->getClientProbeSets($client_id, $probeType);

        $clientProgramData = [];
        if ($clientProbeSetIds) {
            $clientProgramData = $this->clientDataSheetModel->getDataSheetInformation($client_id, $clientProbeSetIds, $domain_id, $goal_id);
        }

        $phaseArray = $this->clientDataSheetModel->getTargetPhasesArray();



        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/generalProbeTable',
            [
                'phases' => $phaseArray,
                'clientProgramData' => $clientProgramData,
            ]
        );
    }*/

    public function mandsDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $mandsSummaryData = $this->mandsSessionDataModel->getSummaryData($client_id);



        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/mandsSummaryData',
            [
                'client' => $client,
                'mandsSummaryData' => $mandsSummaryData,
                'page_title' => $this->page_title
            ]
        );
    }



    public function mandsDailyData()
    {
        $client_id = $this->request->getPost('client_id');
        $session_date = $this->request->getPost('session_date');
        $mandsData = $this->mandsSessionDataModel->getDailyData($client_id, $session_date);

        // Pass data to the view
        $data = [
            'mandsData' => $mandsData,
        ];

        return view('ClientDataSheet/mandsDailyData', $data);
    }

    public function pbDataSheet($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);

        $pbDailyData = $this->pbRecordsModel->getCompleteRecordSet($client_id);


        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/pbDailyData',
            [
                'client' => $client,
                'pbDailyData' => $pbDailyData,
                'page_title' => $this->page_title
            ]
        );
    }


    public function getGoalsByDomain()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');

        // Fetch the goals that belong to the selected domain and have the specified probe type
        $goals = $this->clientDataSheetModel->getGoalsByDomain($client_id, $domain_id);

        // Return the goals as a JSON response
        return $this->response->setJSON($goals);
    }

    public function getSkillsRetained($encodedClientId)
    {
        $client_id = decodeValue($encodedClientId);
        $client = $this->clientModel->find($client_id);




        $this->page_title = 'Clients data sheet';
        return  view(
            'ClientDataSheet/skillsRetained',
            [
                'client' => $client,
                'domains' => $this->clientDataSheetModel->getDomains($client_id),
                'probeSets' => $this->targetProbeSetModel->findAll(),
                'page_title' => $this->page_title
            ]
        );
    }
    public function filterSkillsRetained()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filteredSkills = $this->clientDataSheetModel->getSkillsRetained($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filteredSkills);
    }
    public function getDOITargets($encodedClientId)
    {
        // Decode the client ID
        $client_id = decodeValue($encodedClientId);

        // Retrieve the client details
        $client = $this->clientModel->find($client_id);


        // Set the page title and load the view
        $this->page_title = 'Clients DOI Targets';
        return view('ClientDataSheet/doiTargets', [
            'client' => $client,
            'domains' => $this->clientDataSheetModel->getDomains($client_id),
            'probeSets' => $this->targetProbeSetModel->findAll(),
            'page_title' => $this->page_title
        ]);
    }
    public function filterDOI()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filteredDoi = $this->clientDataSheetModel->getDOITargets($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filteredDoi);
    }
    public function getClientGoalsForFilter()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');

        // Fetch the goals that belong to the selected domain and have the specified probe type
        $goals = $this->clientDataSheetModel->getGoalsByDomain($client_id, $domain_id);

        // Return the goals as a JSON response
        return $this->response->setJSON($goals);
    }

    public function getProgramChange($encodedClientId)
    {
        // Decode the client ID
        $client_id = decodeValue($encodedClientId);

        // Retrieve the client details
        $client = $this->clientModel->find($client_id);


        // Set the page title and load the view
        $this->page_title = 'Clients Program Change';
        return view('ClientDataSheet/programChange', [
            'client' => $client,
            'domains' => $this->clientDataSheetModel->getDomains($client_id),
            'probeSets' => $this->targetProbeSetModel->findAll(),
            'page_title' => $this->page_title
        ]);
    }
    public function filterProgramChange()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filteredData = $this->clientDataSheetModel->getProgramChangeData($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filteredData);
    }
}
