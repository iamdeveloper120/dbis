<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientGraphs\StimulusResponseChainGraphsModel;
use CodeIgniter\API\ResponseTrait;

class StimulusResponseChainGraphsController extends AdminController
{
    use ResponseTrait;

    protected ClientModel $clientModel;
    protected StimulusResponseChainGraphsModel $model;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->model = new StimulusResponseChainGraphsModel();
    }

    public function index()
    {
        $this->page_title = 'Stimulus Response Chain Graphs';
        $clients = $this->clientModel->get_active_client_list();

        return view('ClientGraphs/StimulusResponseChain/index', [
            'clients' => $clients,
            'page_title' => $this->page_title,
        ]);
    }

    public function graphs_data()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $target_id = $this->request->getPost('target_id');

        $rules = [
            'client_id' => [
                'label'  => 'Client',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be a valid selection',
                ],
            ],
            'domain_id' => [
                'label'  => 'Domain',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be a valid selection',
                ],
            ],
            'goal_id' => [
                'label'  => 'Goal',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be a valid selection',
                ],
            ],
            'target_id' => [
                'label'  => 'Target',
                'rules'  => 'permit_empty|integer',
                'errors' => [
                    'integer' => '{field} must be a valid selection',
                ],
            ],
        ];

        $data = [
            'client_id' => $client_id,
            'domain_id' => $domain_id,
            'goal_id' => $goal_id,
            'target_id' => $target_id,
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON([
                'status' => 'validation_error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => '',
            ]);
        }

        $clientId = (int) $client_id;
        $domainId = (int) $domain_id;
        $goalId = (int) $goal_id;
        $targetId = ($target_id === '' || $target_id === null) ? null : (int) $target_id;

        $graphs = $this->model->getGraphsData($clientId, $domainId, $goalId, $targetId);
        $client = $this->clientModel->find($clientId);
        $clientActiveProgram = $this->clientModel->clientActiveProgram($clientId);

        return $this->response->setJSON([
            'status' => 'success',
            'statusText' => 'Success',
            'message' => empty($graphs['targets']) ? 'No stimulus response chain graph data found.' : 'List',
            'data' => $graphs,
            'client' => $client,
            'clientActiveProgram' => $clientActiveProgram,
        ]);
    }

    public function getClientDomains()
    {
        $client_id = (int) $this->request->getPost('client_id');
        if ($client_id <= 0) {
            return $this->response->setJSON([]);
        }

        return $this->response->setJSON($this->model->getClientDomains($client_id));
    }

    public function getClientDomainGoals()
    {
        $client_id = (int) $this->request->getPost('client_id');
        $domain_id = (int) $this->request->getPost('domain_id');
        if ($client_id <= 0 || $domain_id <= 0) {
            return $this->response->setJSON([]);
        }

        return $this->response->setJSON($this->model->getClientDomainGoals($client_id, $domain_id));
    }

    public function getClientGoalTargets()
    {
        $client_id = (int) $this->request->getPost('client_id');
        $goal_id = (int) $this->request->getPost('goal_id');
        if ($client_id <= 0 || $goal_id <= 0) {
            return $this->response->setJSON([]);
        }

        return $this->response->setJSON($this->model->getClientGoalTargets($client_id, $goal_id));
    }
}

