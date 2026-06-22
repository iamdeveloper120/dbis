<?php

namespace App\Controllers\ClientGraphs;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ClientGraphs\MandsGraphsModel; 
use App\Models\ClientConfiguration\ClientModel;

class MandsGraphsController extends AdminController
{
    use ResponseTrait;
    protected $model;
    protected $clientModel;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new MandsGraphsModel();
        $this->clientModel = new ClientModel();
    }
    /************************************************************************* */
    public function index()
    {
        $this->page_title = 'Mand Graphs';
        $clients = $this->clientModel->get_active_client_list();
        return  view('ClientGraphs/Mands/index', ['clients' => $clients, 'page_title' => $this->page_title]);
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

            $graph_data = $this->model->getMandsSummaryDataForGraphs($client_id, $start_date, $end_date);

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
}
