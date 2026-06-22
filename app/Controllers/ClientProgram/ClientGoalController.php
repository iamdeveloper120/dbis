<?php

namespace App\Controllers\ClientProgram;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientProgram\ClientDomainModel;
use App\Models\ClientProgram\ClientGoalModel;
use App\Entities\ClientProgram\ClientGoal;


class ClientGoalController extends AdminController
{
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new ClientGoalModel();
    }
    /*********************************************************************** */

    public function index($encodedClientId)
    {
        $this->page_title = 'Client Program';
        $client_id = decodeValue($encodedClientId);
        $domainModel = new ClientDomainModel();
        $domains = $domainModel->listAll($client_id);
        $ClientModel = new ClientModel();
        $client = $ClientModel->find($client_id);
        return  view('ClientProgram/goals', ['domains' => $domains,  'client' => $client, 'encodedClientId' => $encodedClientId, 'page_title' => $this->page_title]);
    }
    /*********************************************************************** */
    public function list()
    {
        $domain_id = $this->request->getPost('domain_id');
        $client_id = $this->request->getPost('client_id');
        $goals = $this->model->listAll($client_id, $domain_id);
        $response =  $this->getResponseObject('success', 'Goals', 'Listed successfully', [],  $goals);
        return $this->response->setJSON($response);
    }
    /*********************************************************************** */
    public function single()
    {
        $id = $this->request->getPost('id');
        $goal =  $this->model->single($id);
        $response =  $this->getResponseObject('success', '', '', [],  $goal);
        return $this->response->setJSON($response);
    }
    /*********************************************************************** */
    public function create()
    {
        $rules =    [
            'goal_code' => [
                'label'  => 'Goal Code',
                'rules'  => 'required|min_length[1]|is_client_goal_code_unique[client_program_goals.goal_code,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_goal_code_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[3]|is_client_goal_name_unique[client_program_goals.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_goal_name_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 3',
                ],
            ],
            'description' => [
                'label'  => 'Description',
                'rules'  => 'permit_empty|min_length[3]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} min length is 3',

                ],
            ],
            'domain_id' => [
                'label'  => 'Domain',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'client_id' => [
                'label'  => 'Client',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];

        $data = [
            'goal_code' => $this->request->getPost('goal_code'),
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'domain_id' => $this->request->getPost('domain_id'),
            'client_id' => $this->request->getPost('client_id'),
            'created_by'   => auth()->user()->id,
            'updated_by'   => NULL,
            'updated_at'   => NULL,
        ];

        /**   Validation Check */
        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        /** Crate Goal Logic */
        $ClientGoal = new ClientGoal();
        $ClientGoal->fill($data);
        $this->model->save($ClientGoal);
        $goal_id = $this->model->getInsertID();

        $goal =  $this->model->single($goal_id);
        $response =  $this->getResponseObject('success', 'Goal', 'Created successfully', [],  $goal);
        return $this->response->setJSON($response);
    }

    /*********************************************************************** */
    public function update()
    {
        $rules =    [
            'id' => [
                'label'  => 'ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'goal_code' => [
                'label'  => 'Goal Code',
                'rules'  => 'required|min_length[1]|is_client_goal_code_unique[client_program_goals.goal_code,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_goal_code_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[3]|is_client_goal_name_unique[client_program_goals.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_goal_name_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 3',
                ],
            ],
            'description' => [
                'label'  => 'Description',
                'rules'  => 'permit_empty|min_length[3]',
                'errors' => [
                    'required' => '{field} Required',
                    'min_length' => '{field} min length is 3',

                ],
            ],
            'domain_id' => [
                'label'  => 'Domain',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'client_id' => [
                'label'  => 'Client',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
        ];

        $data = [
            'id'   => $this->request->getPost('id'),
            'goal_code' => $this->request->getPost('goal_code'),
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'domain_id' => $this->request->getPost('domain_id'),
            'client_id' => $this->request->getPost('client_id'),
            'updated_by'   =>  auth()->user()->id,
        ];
        /**  Check if in use */
        $isUsed = $this->model->isUsed($data['id']);
        if ($isUsed && !auth()->user()->can('client-program.edit-used-items')) {
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this goal', [], []);
            return $this->response->setJSON($response);
        }
        /**   Validation Check */
        if (!$this->validateData($data, $rules)) {
            $response = [
                'status' => 'error',
                'statusText' => 'Validation_Error',
                'message' => 'Validation Error',
                'validationErrors' => $this->validator->getErrors(),
                'data' => []
            ];
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }
        /**   Update logic */
        $ClientGoal = new ClientGoal();
        $ClientGoal->fill($data);
        $this->model->save($ClientGoal);
        $goal  =  $this->model->single($data['id']);

        $response =  $this->getResponseObject('success', 'Goal', 'Updated successfully', [],  $goal);
        return $this->response->setJSON($response);
    }
    /*********************************************************************** */
    public function delete()
    {
        $data = [
            'id' => $this->request->getPost('id'),
        ];

        $rules =    [
            'id' => [
                'label'  => 'ID',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        /**  Check if in use */
        $isUsed = $this->model->isUsed($data['id']);
        if ($isUsed) {
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this goal', [], []);
            return $this->response->setJSON($response);
        }

        /**  Validation */
        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        /**  Delete and response logic */
        $response = [];
        try {
            $this->model->delete($data['id']);
            $response =  $this->getResponseObject('success', 'Goal', 'deleted successfully', [], []);
        } catch (\Exception $e) {
            $response =  $this->getResponseObject('error', 'Error', 'System Error. Contact system administrator', [], []);
        }

        return $this->response->setJSON($response);
    }
}
