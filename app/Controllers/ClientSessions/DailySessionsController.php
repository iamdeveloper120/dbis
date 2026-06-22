<?php

namespace App\Controllers\ClientSessions;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\AdminController;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\Auth\UserModel;

use App\Models\ClientSessions\DailySessionModel;
use App\Entities\ClientSessions\DailySession;

use App\Models\ClientSessions\SessionDurationModel;

use App\Models\ClientSessions\SessionPBDurationModel;
use App\Models\ClientProblemBehavior\DailySessionsPbRecordsModel;

use App\Models\Mands\MandsSessionDataModel;
use App\Models\ClientSessions\SessionMandDurationModel;

use App\Models\ClientSessions\DailySessionDataCollectionModel;


class DailySessionsController extends AdminController
{
    use ResponseTrait;
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new DailySessionModel();
    }
    public function index()
    {

        $this->page_title = 'Clients';
        $ClientModel = new ClientModel();
        $clients = $ClientModel->get_active_client_list();


        $userModel = model(UserModel::class);

        $instructor_list = $userModel->getUsersByGroups(['instructor', 'externalInstructor']);
        $supervisor_list = $userModel->getUsersByGroups(['supervisor']);

        $this->page_title = 'Clients Completed Sessions';
        return  view(
            'ClientSessions/index',
            [
                'clients' => $clients,
                'instructor_list' => $instructor_list,
                'supervisor_list' => $supervisor_list,
                'page_title' => $this->page_title
            ]
        );
    }
    /******************************************************************** */
    public function list()
    {

        $rules = [];
        $client_id = $this->request->getPost('client_id');
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $supervisor_id = $this->request->getPost('supervisor_id');
        $instructor_id = $this->request->getPost('instructor_id');
        $status = $this->request->getPost('status');
        $data = [
            'client_id'   => $client_id,
            'start_date'   => $start_date,
            'instructor_id'   => $instructor_id,
            'supervisor_id'   => $supervisor_id,
            'end_date'   => $end_date,

        ];
        if ($start_date != '' || $end_date != '') {
            $rules =    [
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

            if (!$this->validateData($data, $rules)) {

                $response =  $this->getResponseObject('validation_error', 'Validation_Error', $this->validator->getErrors(), [],  []);
                return $this->response->setJSON($response);
            }
        }


        if ($start_date == '' || $end_date == '') {
            $start_date = NULL;
            $end_date = NULL;
        } else {
            $start_date = stringToDate($start_date, 'Y-m-d');
            $end_date = stringToDate($end_date, 'Y-m-d');
        }
        if ($client_id == '') {
            $client_id = NULL;
        }

        if ($supervisor_id == '') {
            $supervisor_id = NULL;
        }
        if ($instructor_id == '') {
            $instructor_id = NULL;
        }
        if ($status == '') {
            $status = NULL;
        }

        $clients_to_user = model(ClientModel::class)->get_user_clients_ids(auth()->user()->id);
        $sessions = $this->model->get_client_executed_sessions($client_id, $start_date, $end_date, $supervisor_id, $instructor_id, $status, $clients_to_user);


        $response =  $this->getResponseObject('success', 'Success', 'Record created Successfully', [],  $sessions);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function single()
    {
        $id = $this->request->getPost('id');
        $rowData =  $this->model->getSessionByID($id);
        $session_date = stringToDate($rowData->session_date, CC_DATE_FORMAT);
        $rowData->session_date = $session_date;
        $response =  $this->getResponseObject('success', '', '', [],  [$rowData]);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function create()
    {
        $userModel = model(UserModel::class);

        $data = [
            'session_date' => $this->request->getPost('session_date'),
            'client_id' => $this->request->getPost('client_id'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'supervisor_id' => $this->request->getPost('supervisor_id'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        $rules =    [
            'session_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                ],
            ],

            'start_time' => [
                'label'  => 'Start Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'end_time' => [
                'label'  => 'End Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
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
                'label'  => 'Instructor',
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

        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Error', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        // Verify the user is an instructor
        if (!$userModel->isClientBelongsToInstructor($data['client_id'], $data['instructor_id'])) {
            $response =  $this->getResponseObject('error', 'Validation Error', 'Client is not assigned to instructor', [],  []);
            return $this->response->setJSON($response);
        }

        // Verify the client belongs to instructor
        if (!$userModel->isClientBelongsToSupervisor($data['client_id'], $data['supervisor_id'])) {
            $response =  $this->getResponseObject('error', 'Validation Error', 'Selected supervisor is not assigned as default instructor', [],  []);
            return $this->response->setJSON($response);
        }

        $session_date = stringToDate($this->request->getPost('session_date'), "Y-m-d");
        $data['session_date'] = $session_date;
        $data['status'] = 2;
        $data['flag'] = 1;

        $data['created_by'] = auth()->user()->id;

        $conflict_validation = $this->model->addOrUpdateManuallySessionValidation($data);

        if (!$conflict_validation['success']) {
            $response =  $this->getResponseObject('error', 'Validation Error', $conflict_validation['message'], [],  []);
            return $this->response->setJSON($response);
        }

        $executedSession = new DailySession();

        $executedSession->fill($data);
        $this->model->save($executedSession);
        $new_id = $this->model->getInsertID();
        $rowData  = $this->model->get_client_executed_session($new_id);



        $response =  $this->getResponseObject('success', 'Success', 'Session Created Successfully', [],  $rowData);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function update()
    {

        $data = [
            'id' => $this->request->getPost('id'),
            'client_id' => $this->request->getPost('client_id'),
            'session_date' => $this->request->getPost('session_date'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'supervisor_id' => $this->request->getPost('supervisor_id'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'Session',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'session_date' => [
                'label'  => 'Date',
                'rules'  => 'required|valid_date',
                'errors' => [
                    'required' => '{field} Required',
                    'valid_date' => '{field} is not valid date',
                ],
            ],
            'start_time' => [
                'label'  => 'Start Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'end_time' => [
                'label'  => 'End Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
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
                'label'  => 'Instructor',
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

        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Error', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        $session_date = stringToDate($this->request->getPost('session_date'), "Y-m-d");
        $data['session_date'] = $session_date;
        $data['status'] = 2;
        $data['flag'] = 1;
        $data['updated_at'] =  currentDate('Y-m-d H:i:s');
        $data['updated_by'] = auth()->user()->id;


        $existingData  = $this->model->getSessionByID($data['id']);
        if ($existingData->flag == 0) {
            $response =  $this->getResponseObject('error', 'Validation Error', 'Only manually entered session can be edit.', [],  []);
            return $this->response->setJSON($response);
        }


        $conflict_validation = $this->model->addOrUpdateManuallySessionValidation($data);

        if (!$conflict_validation['success']) {
            $response =  $this->getResponseObject('error', 'Validation Error', $conflict_validation['message'], [],  []);
            return $this->response->setJSON($response);
        }

        if (!$this->model->isSessionDateSame($data['id'], $data['session_date'])) {
            if ($this->model->isSessionDataExistsInOtherTables($data['id'])) {
                $response =  $this->getResponseObject('error', 'Validation Error', 'You cannot modify this session date as it contains existing data.', [],  []);
                return $this->response->setJSON($response);
            }
        }




        $executedSession = new DailySession();

        $executedSession->fill($data);
        $this->model->save($executedSession);
        $rowData  = $this->model->get_client_executed_session($data['id']);

        $response =  $this->getResponseObject('success', 'Success', 'Session Created Successfully', [],  $rowData);
        return $this->response->setJSON($response);
    }
    /******************************************************************** */
    public function endSessionManually()
    {

        $data = [
            'id' => $this->request->getPost('id'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'ID',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'start_time' => [
                'label'  => 'Start Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],

            'end_time' => [
                'label'  => 'End Time',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],

        ];

        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Error', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }
        if ($data['start_time'] >= $data['end_time']) {
            $response =  $this->getResponseObject('error', 'Error', 'Start time must be less than end time.', [],  []);
            return $this->response->setJSON($response);
        }

        $rowDataBeforeUpdate  = $this->model->getSessionByID($data['id']);
        $dataBeforeUpdate = [
            'id' => $rowDataBeforeUpdate->id,
            'session_date' => $rowDataBeforeUpdate->session_date,
            'client_id' => $rowDataBeforeUpdate->client_id,
            'instructor_id' => $rowDataBeforeUpdate->instructor_id,
            'supervisor_id' => $rowDataBeforeUpdate->supervisor_id,
            'start_time' => $rowDataBeforeUpdate->start_time,
            'end_time' => $this->request->getPost('end_time'),
        ];



        /**  Check for conflict */
        $conflict_validation = $this->model->addOrUpdateManuallySessionValidation($dataBeforeUpdate);

        if (!$conflict_validation['success']) {
            $response =  $this->getResponseObject('error', 'Validation Error', $conflict_validation['message'], [],  []);
            return $this->response->setJSON($response);
        }


        $executedSession = new DailySession();
        $object = [
            'id' => $this->request->getPost('id'),
            'end_time' => $this->request->getPost('end_time'),
            'status' =>  2,
            'updated_by' =>  auth()->user()->id,
            'updated_at' =>  currentDate('Y-m-d H:i:s')
        ];
        $executedSession->fill($object);
        $this->model->save($executedSession);
        $rowData  = $this->model->get_client_executed_session($object['id']);

        $sessionDurationModel = new SessionDurationModel();

        $sessionTimerData = [
            'session_id' =>  $rowData->id,
            'end_time' => null,
        ];
        $sessionDurationModel->where($sessionTimerData)->set(['end_time' =>  $object['end_time']])->update();


        $response =  $this->getResponseObject('success', 'Success', 'Record updated Successfully', [],  $rowData);
        return $this->response->setJSON($response);
    }

    public function deleteSession()
    {

        $data = [
            'id' => $this->request->getPost('id'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'Session',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],


        ];

        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Error', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }
        $rowData  = $this->model->getSessionByID($data['id']);

        if ($rowData->status != 1 && $rowData->status != 2) {
            $response =  $this->getResponseObject('error', 'Error', 'Session can be deleted only if its in progress or in review.', [],  []);
            return $this->response->setJSON($response);
        }

        // Connect to the database
        $db = \Config\Database::connect();
        $sessionId = $data['id'];
        try {
            // Start transaction with exception handling enabled
            $db->transException(true)->transStart();

            // 1. Delete collected data
            (new DailySessionDataCollectionModel())->where('session_id', $sessionId)->delete();

            // 2. Delete all durations
            (new SessionDurationModel())->where('session_id', $sessionId)->delete();
            (new DailySessionsPbRecordsModel())->where('session_id', $sessionId)->delete();
            (new SessionPBDurationModel())->where('session_id', $sessionId)->delete();
            (new SessionMandDurationModel())->where('session_id', $sessionId)->delete();

            // 3. Delete related Mands session data
            (new MandsSessionDataModel())->where('session_id', $sessionId)->delete();

            // 4. Delete main session
            (new DailySessionModel())->where('id', $sessionId)->delete();

            // Complete the transaction
            $db->transComplete();

            // Check transaction status
            if ($db->transStatus() === false) {
                log_message('error', 'Session Deletion Error: Database transaction failed. ');
                $response = ['success' => false, 'message' => 'Database transaction failed.'];
            }

            $response =  ['success' => true, 'message' => 'Record Deleted Successfully.'];
        } catch (\Exception $e) {
            log_message('error', 'Session Deletion Error: ' . $e->getMessage());
            $response =  ['success' => false, 'message' => 'An error occurred while deleting session data.'];
        }

        return $this->response->setJSON($response);
    }
    /******************************************************************** */
}
