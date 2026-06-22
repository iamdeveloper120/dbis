<?php

namespace App\Controllers\ClientProgram;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientProgram\ClientDomainModel;
use App\Models\ClientProgram\ClientGoalModel;
use App\Models\ClientProgram\ClientTargetModel;
use App\Models\ClientProgram\ClientTargetsRetainedModel;

use App\Entities\ClientProgram\ClientTarget;


class ClientTargetController extends AdminController
{
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new ClientTargetModel();
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
        return  view('ClientProgram/targets', ['domains' => $domains,  'client' => $client, 'encodedClientId' => $encodedClientId, 'page_title' => $this->page_title]);
    }
    /*********************************************************************** */
    public function list()
    {
        $goal_id = $this->request->getPost('goal_id');
        $client_id = $this->request->getPost('client_id');
        $goals = $this->model->listAll($client_id, $goal_id);
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
        /**   Validation Check */

        $rules =    [
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[1]|is_client_target_name_unique[client_program_targets.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_target_name_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
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
            'goal_id' => [
                'label'  => 'Goal',
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
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'goal_id' => $this->request->getPost('goal_id'),
            'client_id' => $this->request->getPost('client_id'),
            'created_by'   => auth()->user()->id,
            'updated_by'   => NULL,
            'updated_at'   => NULL,
        ];

        if (!$this->validateData($data, $rules)) {

            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        /** Response Logic */
        $ClientTarget = new ClientTarget();
        $ClientTarget->fill($data);
        $this->model->save($ClientTarget);
        $target =  $this->model->single($this->model->getInsertID());

        $response =  $this->getResponseObject('success', 'Target', 'Created successfully', [],  $target);
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
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[1]|is_client_target_name_unique[client_program_targets.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_client_target_name_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
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
            'goal_id' => [
                'label'  => 'Goal',
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
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'goal_id' => $this->request->getPost('goal_id'),
            'client_id' => $this->request->getPost('client_id'),
            'updated_by'   =>  auth()->user()->id,
        ];

        /**  Check if in use */
        $isUsed = $this->model->isUsed($data['id']);
        if ($isUsed && !auth()->user()->can('client-program.edit-used-items')) {
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this target', [], []);
            return $this->response->setJSON($response);
        }
        /**  Validation check */
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

        /** update logic */
        $ClientTarget = new ClientTarget();
        $ClientTarget->fill($data);
        $this->model->save($ClientTarget);
        $target =  $this->model->single($data['id']);


        $response =  $this->getResponseObject('success', 'Target', 'Updated successfully', [],  $target);
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
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this target', [], []);
            return $this->response->setJSON($response);
        }

        /**  validation */
        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        /**  Delete and response logic */
        $result = $this->model->delete($data['id']);
        $response = [];
        if ($result) {
            $response =  $this->getResponseObject('success', 'Target', 'Deleted successfully', [], []);
        } else {
            $response =  $this->getResponseObject('error', 'Error', 'Contact system administrator', [], []);
        }
        return $this->response->setJSON($response);
    }
    /*********************************************************************** */
    public function onHold()
    {
        if (!auth()->loggedIn() || !auth()->user()->can('client-program.target.on-hold')) {
            $response = $this->getResponseObject('error', 'Permission denied', 'You do not have permission to update target hold status.', [], []);
            return $this->response->setStatusCode(403)->setJSON($response);
        }

        $data = [
            'id' => $this->request->getPost('id'),
            'on_hold' => $this->request->getPost('on_hold'),
        ];

        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'on_hold' => [
                'label' => 'On Hold',
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'required' => '{field} Required',
                    'in_list' => '{field} must be 0 or 1',
                ],
            ],
        ];

        if (!$this->validateData($data, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $target = $this->model->single($data['id']);
        if (!$target) {
            $response = $this->getResponseObject('error', 'Target not found', 'Invalid target selected.', [], []);
            return $this->response->setJSON($response);
        }

        $retainedModel = new ClientTargetsRetainedModel();
        $isRetainedOrMastered = $retainedModel->where('target_id', (int) $data['id'])->countAllResults() > 0;
        if ($isRetainedOrMastered) {
            $response = $this->getResponseObject('error', 'Action prohibited', 'Mastered/retained target cannot be moved to or from hold.', [], []);
            return $this->response->setJSON($response);
        }

        $this->model->update((int) $data['id'], [
            'on_hold' => (int) $data['on_hold'],
            'updated_by' => auth()->user()->id,
        ]);

        $updatedTarget = $this->model->single($data['id']);
        $message = ((int) $data['on_hold'] === 1) ? 'Target placed on hold successfully' : 'Target removed from hold successfully';
        $response = $this->getResponseObject('success', 'Target', $message, [], $updatedTarget);
        return $this->response->setJSON($response);
    }
    /*********************************************************************** */
}
