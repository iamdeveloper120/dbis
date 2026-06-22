<?php

namespace App\Controllers\Shared;

use App\Controllers\BaseController;
use App\Services\ClientDataSheetService;
use CodeIgniter\API\ResponseTrait;

class SharedDataSheetController extends BaseController
{
    use ResponseTrait;

    protected ClientDataSheetService $dataSheetService;

    public function __construct()
    {
        $this->dataSheetService = new ClientDataSheetService();
    }

    // Program Filtered Data
    public function filterProgramData()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probeSet = $this->request->getPost('probeSet');

        $data = $this->dataSheetService->getFilteredProgramData($client_id, $probeSet, $domain_id, $goal_id);

        return view('Shared/DataSheet/programData/programsDataTable', $data);
    }

    // Goal Dropdown for Domain Change
    public function getGoalsByDomain()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');

        $goals = $this->dataSheetService->fetchGoalsForDomain($client_id, $domain_id);
        return $this->response->setJSON($goals);
    }

    // Transitions for Collection
    public function transitionList()
    {
        $collection_id = $this->request->getPost('collection_id');
        $data = $this->dataSheetService->getTransitionList($collection_id);

        return $this->response->setJSON([
            'html' => view('Shared/DataSheet/programData/transitionList', $data)
        ]);
    }

    // Stimulus Step Matrix
    public function stimulusTargetStepsDetail()
    {
        $target_id = $this->request->getPost('target_id');
        $data = $this->dataSheetService->getStimulusTargetStepMatrix($target_id);

        return $this->response->setJSON([
            'html' => view('Shared/DataSheet/programData/stimulusTargetStepsDetail', $data)
        ]);
    }

    // Mands Daily
    public function mandsDailyData()
    {
        $client_id = $this->request->getPost('client_id');
        $session_date = $this->request->getPost('session_date');

        $data = $this->dataSheetService->getMandsDaily($client_id, $session_date);

        return view('Shared/DataSheet/mands/mandsDailyData', $data);
    }

    // Filter Skills Retained
    public function filterSkillsRetained()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filtered = $this->dataSheetService->filterSkillsRetained($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filtered);
    }

    // Filter DOI
    public function filterDOITargets()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filtered = $this->dataSheetService->filterDOITargets($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filtered);
    }

    // Filter Program Change
    public function filterProgramChange()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $probe_set_id = $this->request->getPost('probe_set_id');

        $filtered = $this->dataSheetService->filterProgramChange($client_id, $domain_id, $goal_id, $probe_set_id);
        return $this->response->setJSON($filtered);
    }
}
