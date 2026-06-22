<?php

namespace App\Controllers\MasterProgram;

use App\Controllers\AdminController;
use App\Models\MasterProgram\MasterDomainModel;
use App\Entities\MasterProgram\MasterDomain;


class MasterDomainController extends AdminController
{
    protected $model;
    public function __construct()
    {
        // Load your model in the constructor
        $this->model = new MasterDomainModel();
    }
    public function index()
    {
        $this->page_title = 'Client Management';
        return  view('MasterProgram/domains', ['page_title' => $this->page_title]);
    }

    public function list()
    {
        $data = $this->model->listAll();
        $response =  $this->getResponseObject('success', '', '', [],  $data);
        return $this->response->setJSON($response);
    }

    public function single()
    {
        $id = $this->request->getPost('id');
        $domain =  $this->model->single($id);
        $response =  $this->getResponseObject('success', '', '', [],  $domain);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function create()
    {
        $rules =    [
            'domain_code' => [
                'label'  => 'Domain Code',
                'rules'  => 'required|min_length[1]|is_unique[program_master_domains.domain_code,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[3]|is_unique[program_master_domains.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
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
        ];

        $data = [
            'domain_code' => $this->request->getPost('domain_code'),
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'created_by'   => auth()->user()->id,
            'updated_by'   => NULL,
            'updated_at'   => NULL,
        ];
     

        /**   Validation Check */
        if (!$this->validateData($data, $rules)) {

            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(),  []);
            return $this->response->setJSON($response);
        }

        /** Response Logic */
        $MasterDomain = new MasterDomain();
        $MasterDomain->fill($data);
        $this->model->save($MasterDomain);
        $domain =  $this->model->single($this->model->getInsertID());

        $response =  $this->getResponseObject('success', 'Domain', 'Created successfully', [],  $domain);
        return $this->response->setJSON($response);
    }
    /************************************************************************* */
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
            'domain_code' => [
                'label'  => 'Domain Code',
                'rules'  => 'required|min_length[1]|is_unique[program_master_domains.domain_code,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|min_length[3]|is_unique[program_master_domains.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
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
            ]
        ];

        $data = [
            'id'   => $this->request->getPost('id'),
            'domain_code' => $this->request->getPost('domain_code'),
            'name' => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'updated_by'   =>  auth()->user()->id,
        ];

        /**  Check if in use */
        $isUsed = $this->model->isUsed($data['id']);
        if ($isUsed) {
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this domain', [], []);
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

        /**  Update logic */
        $MasterDomain = new MasterDomain();
        $MasterDomain->fill($data);
        $this->model->save($MasterDomain);
        $domain =  $this->model->single($data['id']);

        $response =  $this->getResponseObject('success', 'Domain', 'Updated successfully', [],  $domain);
        return $this->response->setJSON($response);
    }
    /************************************************************************* */
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
            $response =  $this->getResponseObject('error', 'Action prohibited', 'Clinical data exists for this domain', [], []);
            return $this->response->setJSON($response);
        }

        /**  Validation logic */
        if (!$this->validateData($data, $rules)) {
            $response =  $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        /**  Delete logic */
        $result = $this->model->delete($data['id']);
        $response = [];
        if ($result) {
            $response =  $this->getResponseObject('success', 'Domain', 'Deleted successfully', [], []);
        } else {
            $response =  $this->getResponseObject('error', 'Error', 'Contact system administrator', [], []);
        }
        return $this->response->setJSON($response);
    }
    /************************************************************************* */
}
