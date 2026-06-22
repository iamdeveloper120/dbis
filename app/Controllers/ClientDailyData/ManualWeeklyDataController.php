<?php

namespace App\Controllers\ClientDailyData;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;


use App\Models\ClientSessions\ManualWeeklySessionModel;
use App\Models\Auth\UserModel;
use App\Models\ClientConfiguration\ClientModel;

use App\Entities\ClientSessions\DailySession as WeeklySession;

class ManualWeeklyDataController extends AdminController
{
    use ResponseTrait;

    protected $sessionModel;
    protected $clientModel;

    public function __construct()
    {

        $this->sessionModel = new ManualWeeklySessionModel();
        $this->clientModel = new ClientModel();
    }
    public function index()
    {

        $clients = $this->clientModel->get_active_client_list();
        $this->page_title = 'Clients Weekly Session Data';
        $userModel = model(UserModel::class);

        $supervisor_list = $userModel->getUsersByGroups(['supervisor']);

        return  view(
            'ClientDailyData/WeeklyData/index',
            [
                'clients' => $clients,
                'supervisor_list' => $supervisor_list,
                'page_title' => $this->page_title
            ]
        );
    }
    /******************************************************************** */
    public function list()
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
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->getErrors(),
                'data' => ''
            ];
        } else {

            if ($start_date == '' || $end_date == '') {
                $start_date = NULL;
                $end_date = NULL;
            } else {

                $start_date = stringToDate($start_date, "Y-m-d");
                $end_date = stringToDate($end_date, "Y-m-d");
            }

            $session_data = $this->sessionModel->list($client_id, $start_date, $end_date);

            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => 'List',
                'data' => $session_data
            ];
        }


        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function get_selected()
    {
        
        $response = [];
        $rules =    [
            'id' => [
                'label'  => 'Reocrd ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];
        $data = [
            'id'   => $this->request->getPost('id'),
        ];


        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $session = $this->sessionModel->find($data['id']);

            $session->week_date = stringToDate($session->week_date, CC_DATE_FORMAT);
            $response = [
                'status' => 'success',
                'statusText' => 'Success',
                'message' => '',
                'data' => $session
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function create()
    {

        $data = [
            'week_date' => $this->request->getPost('week_date'),
            'client_id' => $this->request->getPost('client_id'),
            'supervisor_id' => $this->request->getPost('supervisor_id'),
            'hours' => ($this->request->getPost('hours') != '') ? $this->request->getPost('hours') : NULL,
            'skills_retained' => $this->request->getPost('skills_retained'),
            'doi' => ($this->request->getPost('doi') != '') ? $this->request->getPost('doi') : NULL,

        ];

        $rules =    [
            'week_date' => [
                'label'  => 'Week Date',
                'rules'  => 'required|valid_date|is_weekly_session_date_exist[week_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                    //'is_weekly_session_date_exist' => '{field} already exist'
                ],
            ],
            'hours' => [
                'label'  => 'Hours',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => '{field} must be number',
                ],
            ],
            'doi' => [
                'label'  => 'Degrees of Independence',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => '{field} must be number',
                ],
            ],
            'skills_retained' => [
                'label'  => 'Skills Retained',
                'rules'  => 'required|numeric',
                'errors' => [
                    'required' => '{field} Required',
                    'numeric' => '{field} must be number',
                ],
            ],
            'client_id' => [
                'label'  => 'Client ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be number',
                ],
            ],
            'supervisor_id' => [
                'label'  => 'Supervisor',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be number',
                ],
            ],
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $week_date = stringToDate($this->request->getPost('week_date'), 'Y-m-d');
            $data['week_date'] = $week_date;
            $data['created_by'] = auth()->user()->id;

            $session = new WeeklySession();
            $session->fill($data);

            // Save basic details

            $this->sessionModel->save($session);

            $session  = $this->sessionModel->single($this->sessionModel->getInsertID());

            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record created successfully',
                'data' => $session
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function update()
    {

        $response = [];

        if ($this->request->getPost('no_session') == 0) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => 'Record is No Session. you can delete entry and add new one.',
                'data' => ''
            ];
        } else {
            $data = [
                'id' => $this->request->getPost('id'),
                'client_id' => $this->request->getPost('client_id'),
                'week_date' => $this->request->getPost('week_date'),
                'supervisor_id' => $this->request->getPost('supervisor_id'),
                'hours' => ($this->request->getPost('hours') != '') ? $this->request->getPost('hours') : NULL,
                'skills_retained' => $this->request->getPost('skills_retained'),
                'doi' => ($this->request->getPost('doi') != '') ? $this->request->getPost('doi') : NULL,

            ];

            $rules =    [
                'id' => [
                    'id'  => 'Session ID',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} Required',
                    ],
                ],
                'week_date' => [
                'label'  => 'Week Date',
                'rules'  => 'required|valid_date|is_weekly_session_date_exist[week_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                    //'is_weekly_session_date_exist' => '{field} already exist'
                ],
            ],
                'hours' => [
                    'label'  => 'Hours',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => '{field} must be number',
                    ],
                ],
                'doi' => [
                    'label'  => 'Degrees of Independence',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => '{field} must be number',
                    ],
                ],
                'skills_retained' => [
                    'label'  => 'Skills Retained ',
                    'rules'  => 'required|numeric',
                    'errors' => [
                        'required' => '{field} Required',
                        'numeric' => '{field} must be number',
                    ],
                ],
                'client_id' => [
                    'label'  => 'Client ID',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                        'integer' => '{field} must be number',
                    ],
                ],
                'supervisor_id' => [
                    'label'  => 'Supervisor',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                        'integer' => '{field} must be number',
                    ],
                ],
            ];


            if (!$this->validateData($data, $rules)) {
                $response = [
                    'status' => 'error',
                    'statusText' => 'Error',
                    'message' => $this->validator->listErrors('custom_list'),
                    'data' => ''
                ];
            } else {
                $week_date = stringToDate($this->request->getPost('week_date'), 'Y-m-d');
                $data['week_date'] = $week_date;
                $data['updated_by'] = auth()->user()->id;

                $session = new WeeklySession();
                $session->fill($data);

                // Save basic details

                $this->sessionModel->save($session);

                $session  = $this->sessionModel->single($data['id']);


                $response = [
                    'status' => 'success',
                    'statusText' => '',
                    'message' => 'Record updated successfully',
                    'data' => $session
                ];
            }
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function create_no_session()
    {

        $data = [
            'week_date' => $this->request->getPost('week_date'),
            'client_id' => $this->request->getPost('client_id'),

            'supervisor_id' => 0,
            'hours' => NULL,
            'skills_retained' => NULL,
            'doi' => NULL,
            'status' => 0
        ];

        $rules =    [
            'week_date' => [
                'label'  => 'Week Date',
                'rules'  => 'required|valid_date|is_weekly_session_date_exist[week_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                    //'is_weekly_session_date_exist' => '{field} already exist'
                ],
            ],
            'client_id' => [
                'label'  => 'Client ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {
            $week_date = stringToDate($this->request->getPost('week_date'), 'Y-m-d');
            $data['week_date'] = $week_date;
            $data['created_by'] = auth()->user()->id;

            $session = new WeeklySession();
            $session->fill($data);

            // Save basic details

            $this->sessionModel->save($session);

            $session  = $this->sessionModel->single($this->sessionModel->getInsertID());

            $response = [
                'status' => 'success',
                'statusText' => '',
                'message' => 'Record updated successfully',
                'data' => $session
            ];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function delete()
    {
        $data = [
            'id' => $this->request->getPost('id'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'Session ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {


            $result = $this->sessionModel->delete($data['id']);

            if ($result) {
                $response = [
                    'status' => 'success',
                    'statusText' => '',
                    'message' => 'Record deleted successfully',
                    'data' => ''
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'statusText' => '',
                    'message' => 'Contact system administrator',
                    'data' => ''
                ];
            }
        }

        return $this->response->setJSON($response);
    }
}
