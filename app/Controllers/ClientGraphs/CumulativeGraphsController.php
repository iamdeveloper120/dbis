<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientGraphs\CumulativeGraphsModel;


class CumulativeGraphsController extends AdminController
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
        $this->page_title = 'Cumulative Graph';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Cumulative/index', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    /************************************************************************* */
    public function graphs_data()
    {
        $response = [];
        $rules = [];
        $client_id = $this->request->getPost('client_id');
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        if ($start_date != '' || $end_date != '') {
            $rules =    [
                'client_id' => [
                    'label'  => 'Client',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} Required',
                    ],
                ],
                'start_date' => [
                    'label'  => 'Start Date',
                    'rules'  => 'required|valid_date',
                    'errors' => [
                        'required' => '{field} Required',
                        'valid_date' => '{field} not a valid date',
                    ],
                ],
                'end_date' => [
                    'label'  => 'End Date',
                    'rules'  => 'required|valid_date|compareDates[start_date,end_date,{$start_date,$end_date}]',
                    'errors' => [
                        'required' => '{field} Required',
                        'valid_date' => '{field} not a valid date',
                        'compareDates' => '{field} must be greater then Start Date',
                    ],
                ],
            ];
        } else {
            $rules =    [
                'client_id' => [
                    'label'  => 'Client',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} Required',
                    ],
                ],
            ];
        }


        $data = [
            'client_id'   => $client_id,
            'start_date'   => $start_date,
            'end_date'   => $end_date,

        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'validation_error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => ''
            ];
        } else {

            if ($start_date == '' || $end_date == '') {
                $start_date = NULL;
                $end_date = NULL;
            } else {

                $start_date = stringToDate($start_date, 'Y-m-d');
                $end_date = stringToDate($end_date, 'Y-m-d');
            }


            $model = model(CumulativeGraphsModel::class);
            $cumulative_data = $model->get_cumulative_data($client_id, $start_date, $end_date);

            $client = $this->clientModel->find($client_id);
            $clientActiveProgram = $this->clientModel->clientActiveProgram($client_id);

            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => 'List',
                'data' => $cumulative_data,
                'client' => $client,
                'clientActiveProgram' => $clientActiveProgram
            ];
        }


        return $this->response->setJSON($response);
    }
    /************************************************************************* */
    public function index_phase_line()
    {
        $this->page_title = 'Cumulative Graph Phase Line';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Cumulative/phase_line', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    /************************************************************************* */
    public function cumulative_graph_by_domain_and_goal_index()
    {
        $this->page_title = 'Cumulative graphs by domains and goals';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Cumulative/by-domain-and-goal-index', ['clients' => $clients, 'page_title' => $this->page_title]);
    }
    public function cumulative_graph_by_domain_and_goal_data()
    {
        $response = [];
        $rules = [];
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        $rules =    [
            'client_id' => [
                'label'  => 'Client',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];


        $data = [
            'client_id'   => $client_id,
            'start_date'   => $domain_id,
            'end_date'   => $goal_id,

        ];

        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'validation_error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => ''
            ];
        } else {

            $model = model(CumulativeGraphsModel::class);
            $cumulative_data = $model->get_cumulative_data_by_domain_and_goal($client_id, $domain_id, $goal_id);

            $client = $this->clientModel->find($client_id);
            $clientActiveProgram = $this->clientModel->clientActiveProgram($client_id);
            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => 'List',
                'data' => $cumulative_data,
                'client' => $client,
                'clientActiveProgram' => $clientActiveProgram,
            ];
        }

        return $this->response->setJSON($response);
    }
    public function getClientDomains()
    {
        $client_id = $this->request->getPost('client_id');
        // Fetch the goals that belong to the selected domain and have the specified probe type
        $model = model(CumulativeGraphsModel::class);
        $goals = $model->getDomains($client_id);

        // Return the goals as a JSON response
        return $this->response->setJSON($goals);
    }
    public function getClientDomainGoals()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');

        // Fetch the goals that belong to the selected domain and have the specified probe type
        $model = model(CumulativeGraphsModel::class);
        $goals = $model->getGoalsByDomain($client_id, $domain_id);

        // Return the goals as a JSON response
        return $this->response->setJSON($goals);
    }

    /************************************************************************* cumulative_graph_by_domain_and_goal_index*/
}
