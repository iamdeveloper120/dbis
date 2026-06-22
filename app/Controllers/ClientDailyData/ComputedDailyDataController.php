<?php

namespace App\Controllers\ClientDailyData;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientDataSheet\ClientLiveDailyDataModel;
use App\Models\Auth\UserModel;
//use function PHPUnit\Framework\isNull;

class ComputedDailyDataController extends AdminController
{
    use ResponseTrait;

    protected $clientLiveDailyDataModel;
    protected $clientModel;

    public function __construct()
    {
        // Load your model in the constructor

        $this->clientModel = new ClientModel();
        $this->clientLiveDailyDataModel = new ClientLiveDailyDataModel();
    }
    /******************************************************************** */
    // Clients for Daily Session. only assigned and active clients will display
    /******************************************************************** */
    public function index()
    {
        $clients = $this->clientModel->get_active_client_list();
        $this->page_title = 'Clients Daily Session Data';
        $userModel = model(UserModel::class);
        $instructor_list = null;
        if (auth()->user()->inGroup('externalinstructor')) {
            $instructor_list = [auth()->user()];
        } else {
            $instructor_list = $userModel->getUsersByGroups(['instructor', 'externalinstructor']);
        }

        $supervisor_list = $userModel->getUsersByGroups(['supervisor']);
        return  view(
            'ClientDailyData/ComputedData/index',
            [
                'clients' => $clients,
                'page_title' => $this->page_title,
                'instructor_list' => $instructor_list,
                'supervisor_list' => $supervisor_list,
            ]
        );
    }
    public function list()
    {
        $response = [];
        $rules = [];
        $client_id = $this->request->getPost('client_id');
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');

        // Validation Rules
        if ($start_date != '' || $end_date != '') {
            $rules = [
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
                        'compareDates' => '{field} must be greater than Start Date',
                    ],
                ],
            ];
        } else {
            $rules = [
                'client_id' => [
                    'label'  => 'Client',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} Required',
                    ],
                ],
            ];
        }

        // Validate Data
        $data = [
            'client_id'   => $client_id,
            'start_date'   => $start_date,
            'end_date'   => $end_date,
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON([
                'status' => 'validation_error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => ''
            ]);
        }

        // Prepare Dates for Query
        if ($start_date == '' || $end_date == '') {
            $start_date = NULL;
            $end_date = NULL;
        } else {
            $start_date = stringToDate($start_date, 'Y-m-d');
            $end_date = stringToDate($end_date, 'Y-m-d');
        }

        $result = $this->clientLiveDailyDataModel->getClientLiveDailyData($client_id, $start_date, $end_date);

        $response = [
            'status' => 'success',
            'statusText' => 'Success',
            'message' => 'List',
            'data' => $result
        ];

        return $this->response->setJSON($response);
    }
}
