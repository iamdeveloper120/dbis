<?php

namespace App\Controllers\MasterProgram;

use App\Controllers\AdminController;
use App\Models\MasterProgram\MasterTargetModel;
use App\Models\MasterProgram\MasterDomainModel;
use App\Entities\MasterProgram\MasterTarget;


class MasterTargetController extends AdminController
{
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new MasterTargetModel();
    }
    /*********************************************************************** */
    public function index()
    {
        $this->page_title = 'Developmental Program Targets';
        $domainModel = new MasterDomainModel();
        $domains = $domainModel->listAll();

        return  view('MasterProgram/targets', ['domains' => $domains, 'page_title' => $this->page_title]);
    }
    /*********************************************************************** */
    public function list()
    {
        $goal_id = $this->request->getPost('goal_id');
        $targets = $this->model->listAll($goal_id);
        $response =  $this->getResponseObject('success', 'Targets', 'Listed successfully', [],  $targets);
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
                'rules'  => 'required|min_length[1]|is_master_target_name_unique[program_master_targets.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_master_target_name_unique' => '{field} must be unique',
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
        ];

        $data = [
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'goal_id' => $this->request->getPost('goal_id'),
            'created_by'   => auth()->user()->id,
            'updated_by'   => NULL,
            'updated_at'   => NULL,
        ];

        if (!$this->validateData($data, $rules)) {

            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        /** Response Logic */
        $MasterTarget = new MasterTarget();
        $MasterTarget->fill($data);
        $this->model->save($MasterTarget);
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
                'rules'  => 'required|min_length[1]|is_master_target_name_unique[program_master_targets.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_target_name_unique_target' => '{field} must be unique',
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
        ];

        $data = [
            'id'   => $this->request->getPost('id'),
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'goal_id' => $this->request->getPost('goal_id'),
            'updated_by'   =>  auth()->user()->id,
        ];

        /**  Check if in use */
        $isUsed = $this->model->isUsed($data['id']);
        if ($isUsed) {
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
        $MasterTarget = new MasterTarget();
        $MasterTarget->fill($data);
        $this->model->save($MasterTarget);
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
}
