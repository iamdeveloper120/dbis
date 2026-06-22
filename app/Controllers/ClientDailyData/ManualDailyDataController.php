<?php

namespace App\Controllers\ClientDailyData;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;

use App\Entities\ClientSessions\DailySession;
use App\Models\ClientSessions\ManualDailySessionModel;

class ManualDailyDataController extends AdminController
{
    use ResponseTrait;

    protected $manualSessionModel;
    protected $clientModel;

    public function __construct()
    {

        $this->manualSessionModel = new ManualDailySessionModel();
    }
    /******************************************************************** */
    /******************************************************************** */
    public function get_selected_manual_session()
    {

        $response = [];

        $rules =    [
            'id' => [
                'label'  => 'Session ID',
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
                'message' => $this->validator->getErrors(),
                'data' => ''
            ];
        } else {
            $session =  $this->manualSessionModel->find($data['id']);
            $week_date = stringToDate($session->week_date, CC_DATE_FORMAT);
            $session->week_date = $week_date;
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
    public function create_manual_session()
    {

        $data = [
            'week_date' => $this->request->getPost('week_date'),
            'client_id' => $this->request->getPost('client_id'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'supervisor_id' => $this->request->getPost('supervisor_id'),
            'hours' => ($this->request->getPost('hours') != '') ? $this->request->getPost('hours') : NULL,
            'skills_retained' => $this->request->getPost('skills_retained'),
            'doi' => ($this->request->getPost('doi') != '') ? $this->request->getPost('doi') : NULL,
            'total_mands' => $this->request->getPost('total_mands'),
            'variety_of_mands' => ($this->request->getPost('variety_of_mands') != '') ? $this->request->getPost('variety_of_mands') : NULL,
            'frequency_of_problem_behavior' => $this->request->getPost('frequency_of_problem_behavior'),
            'total_duration_of_problem_behavior' => ($this->request->getPost('total_duration_of_problem_behavior') != '') ? $this->request->getPost('total_duration_of_problem_behavior') : NULL,
            'session_quality_rating' => ($this->request->getPost('session_quality_rating') != '') ? $this->request->getPost('session_quality_rating') : NULL,
            'program_change_made' => ($this->request->getPost('program_change_made') != '') ? $this->request->getPost('program_change_made') : 0,
            'comments' => ($this->request->getPost('comments') != '') ? $this->request->getPost('comments') : NULL,
        ];

        $rules =    [
            'week_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_manual_daily_session_date_exist[week_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                    //'is_manual_daily_session_date_exist' => '{field} already exists or there is a conflict with existing data'
                ],
            ],

            'hours' => [
                'label'  => 'Hours',
                'rules'  => 'required|numeric',
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
            'doi' => [
                'label'  => 'Degrees of Independence',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => '{field} must be number',
                ],
            ],
            'total_mands' => [
                'label'  => 'Total Mands',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'required' => '{field} Required',
                    'numeric' => '{field} must be number',
                ],
            ],
            'variety_of_mands' => [
                'label'  => 'Variety of mands',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => '{field} must be number',
                ],
            ],
            'frequency_of_problem_behavior' => [
                'label'  => 'Frequency of problem behavior',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'required' => '{field} Required',
                    'numeric' => '{field} must be number',
                ],
            ],
            'total_duration_of_problem_behavior' => [
                'label'  => 'Total duration of problem behavior',
                'rules'  => 'permit_empty|valid_time_format',
                'errors' => [
                    'valid_time_format' => '{field} must be in HH:MM:SS format',
                ],
            ],
            'session_quality_rating' => [
                'label'  => 'Session quality rating',
                'rules'  => 'permit_empty|integer|in_list[1,2,3]',
                'errors' => [
                    'integer' => '{field} must be integer',
                    'in_list' => '{field} must be between 1 to 5',
                ],
            ],

            'client_id' => [
                'client_id'  => 'Client ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be integer',
                ],
            ],
            'instructor_id' => [
                'label'  => 'Therapist',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be integer',
                ],
            ],
            'supervisor_id' => [
                'label'  => 'Supervisor',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                    'integer' => '{field} must be integer',
                ],
            ],
        ];
        // Additional validation rule for the 'comments' field
        if ($data['session_quality_rating'] == 1 || $data['program_change_made'] == 1) {
            $rules['comments'] = [
                'label'  => 'Comments',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Comments are required when session quality rating is poor',
                ],
            ];
        }

        $response = [];
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => $this->validator->listErrors('custom_list'),
                'data' => ''
            ];
        } else {

            $week_date = stringToDate($this->request->getPost('week_date'), "Y-m-d");
            $data['week_date'] = $week_date;
            $data['created_by'] = auth()->user()->id;

            $session = new DailySession();
            $session->fill($data);
            $this->manualSessionModel->save($session);
            $new_id = $this->manualSessionModel->getInsertID();
            $session  = $this->manualSessionModel->getSelectedRow($new_id);

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
    public function update_manual_session()
    {

        $response = [];

        if ($this->request->getPost('no_session') == 0) {
            $response = [
                'status' => 'error',
                'statusText' => 'Error',
                'message' => 'No Session can not update. you can delete and add a new record',
                'data' => ''
            ];
        } else {
            $data = [
                'id' => $this->request->getPost('id'),
                'week_date' => $this->request->getPost('week_date'),
                'client_id' => $this->request->getPost('client_id'),
                'instructor_id' => $this->request->getPost('instructor_id'),
                'supervisor_id' => $this->request->getPost('supervisor_id'),
                'hours' => ($this->request->getPost('hours') != '') ? $this->request->getPost('hours') : NULL,
                'skills_retained' => $this->request->getPost('skills_retained'),
                'doi' => ($this->request->getPost('doi') != '') ? $this->request->getPost('doi') : NULL,
                'total_mands' => $this->request->getPost('total_mands'),
                'variety_of_mands' => ($this->request->getPost('variety_of_mands') != '') ? $this->request->getPost('variety_of_mands') : NULL,
                'frequency_of_problem_behavior' => $this->request->getPost('frequency_of_problem_behavior'),
                'total_duration_of_problem_behavior' => ($this->request->getPost('total_duration_of_problem_behavior') != '') ? $this->request->getPost('total_duration_of_problem_behavior') : NULL,
                'session_quality_rating' => ($this->request->getPost('session_quality_rating') != '') ? $this->request->getPost('session_quality_rating') : NULL,
                'program_change_made' => ($this->request->getPost('program_change_made') != '') ? $this->request->getPost('program_change_made') : 0,
                'comments' => ($this->request->getPost('comments') != '') ? $this->request->getPost('comments') : NULL,
            ];

            $rules =    [
                'id' => [
                    'id'  => 'Session ID',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                    ],
                ],
                'week_date' => [
                    'label'  => 'Date',
                    'rules'  => 'required|valid_date|is_manual_daily_session_date_exist[week_date]',
                    'errors' => [
                        'required' => '{field} Required',
                        'valid_date' => '{field} is not valid date',
                        //'is_manual_daily_session_date_exist' => '{field} already exists or there is a conflict with existing data'
                    ],
                ],
                'hours' => [
                    'label'  => 'Hours',
                    'rules'  => 'required|numeric',
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
                'doi' => [
                    'label'  => 'Degrees of Independence',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => '{field} must be number',
                    ],
                ],
                'total_mands' => [
                    'label'  => 'Total Mands',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'required' => '{field} Required',
                        'numeric' => '{field} must be number',
                    ],
                ],
                'variety_of_mands' => [
                    'label'  => 'Variety of mands',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => '{field} must be number',
                    ],
                ],
                'frequency_of_problem_behavior' => [
                    'label'  => 'Frequency of problem behavior',
                    'rules'  => 'permit_empty|numeric',
                    'errors' => [
                        'required' => '{field} Required',
                        'numeric' => '{field} must be number',
                    ],
                ],
                'total_duration_of_problem_behavior' => [
                    'label'  => 'Total duration of problem behavior',
                    'rules'  => 'permit_empty|valid_time_format',
                    'errors' => [
                        'valid_time_format' => '{field} must be in HH:MM:SS format',
                    ],
                ],
                'session_quality_rating' => [
                    'label'  => 'Session quality rating',
                    'rules'  => 'permit_empty|integer|in_list[1,2,3,4,5]',
                    'errors' => [
                        'integer' => '{field} must be integer',
                        'in_list' => '{field} must be betweeen 1 to 5',
                    ],
                ],

                'client_id' => [
                    'client_id'  => 'Client ID',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                        'integer' => '{field} must be integer',
                    ],
                ],
                'instructor_id' => [
                    'instructor_id'  => 'Therapist',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                        'integer' => '{field} must be integer',
                    ],
                ],
                'supervisor_id' => [
                    'supervisor_id'  => 'Supervisor',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} Required',
                        'integer' => '{field} must be integer',
                    ],
                ],
            ];

            // Additional validation rule for the 'comments' field
            if ($data['session_quality_rating'] == 1 || $data['program_change_made'] == 1) {
                $rules['comments'] = [
                    'label'  => 'Comments',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => 'Comments are required when session quality rating is poor',
                    ],
                ];
            }
            if (!$this->validateData($data, $rules)) {
                $response = [
                    'status' => 'error',
                    'statusText' => 'Error',
                    'message' => $this->validator->listErrors('custom_list'),
                    'data' => ''
                ];
            } else {
                $week_date = stringToDate($this->request->getPost('week_date'), "Y-m-d");
                $data['week_date'] = $week_date;
                $data['updated_by'] = auth()->user()->id;

                $session = new DailySession();
                $session->fill($data);
                $this->manualSessionModel->save($session);

                $session  = $this->manualSessionModel->getSelectedRow($data['id']);

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
    public function create_manual_no_session()
    {


        $data = [
            'week_date' => $this->request->getPost('week_date'),
            'client_id' => $this->request->getPost('client_id'),
            'instructor_id' => 0,
            'supervisor_id' => 0,
            'hours' => NULL,
            'skills_retained' => NULL,
            'doi' => NULL,
            'total_mands' => NULL,
            'variety_of_mands' => NULL,
            'frequency_of_problem_behavior' => NULL,
            'total_duration_of_problem_behavior' => NULL,
            'session_quality_rating' => NULL,
            'status' => 0
        ];

        $rules =    [
            'week_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date|is_manual_daily_session_date_exist[week_date]',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                    //'is_manual_daily_session_date_exist' => '{field} already exists or there is a conflict with existing data'
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
            $week_date = stringToDate($this->request->getPost('week_date'), "Y-m-d");
            $data['week_date'] = $week_date;
            $data['created_by'] = auth()->user()->id;

            $session = new DailySession();
            $session->fill($data);
            $this->manualSessionModel->save($session);

            $session  = $this->manualSessionModel->getSelectedRow($this->manualSessionModel->getInsertID());

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
    public function delete_manual_session()
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


            $result = $this->manualSessionModel->delete($data['id']);

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
