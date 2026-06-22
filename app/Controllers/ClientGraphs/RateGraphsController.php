<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientGraphs\RateGraphsModel;

class RateGraphsController extends AdminController
{
    use ResponseTrait;
    protected $model;
    protected $clientModel;
    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }
    /************************************************************************* */
    public function index()
    {
        $this->page_title = 'Rate Graphs';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Rate/index', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    /************************************************************************* */
    public function graphs_data()
    {
        $response = [];
        $client_id = $this->request->getPost('client_id');
        $rules =    [
            'client_id' => [
                'label'  => 'Client',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];
        $data = [
            'client_id'   => $client_id

        ];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'validation_error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => ''
            ];
        } else {
            $model = model(RateGraphsModel::class);
            $skill_data = $model->get_target_rate_data($client_id, 'Skills');
            $doi_data = $model->get_target_rate_data($client_id, 'DOI');
            $graph_data = ['skill_data' => $skill_data, 'doi_data' => $doi_data];
            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => 'List',
                'data' => $graph_data
            ];
        }


        return $this->response->setJSON($response);
    }
    /************************************************************************* */
    public function index_phase_line()
    {
        $this->page_title = 'Rate Graphs Phase Line';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Rate/phase_line', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    /************************************************************************* */
    public function index_target_months()
    {
        $this->page_title = 'Rate Graphs Phase Line';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Rate/target_months', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    /************************************************************************* */
}
