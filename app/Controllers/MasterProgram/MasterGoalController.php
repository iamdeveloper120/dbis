<?php



namespace App\Controllers\MasterProgram;



use App\Controllers\AdminController;

use App\Models\MasterProgram\MasterDomainModel;

use App\Models\MasterProgram\MasterGoalModel;

use App\Entities\MasterProgram\MasterGoal;





class MasterGoalController extends AdminController

{

    protected $model;

    public function __construct()

    {

        // Load your model in the constructor

        $this->model = new MasterGoalModel();
    }

    /*********************************************************************** */

    public function index()

    {

        $this->page_title = 'Developmental Program Goals';

        $domainModel = new MasterDomainModel();

        $domains = $domainModel->listAll();



        return  view('MasterProgram/goals', ['domains' => $domains,   'page_title' => $this->page_title]);
    }

    /*********************************************************************** */

    public function list()

    {

        $domain_id = $this->request->getPost('domain_id');

        $goals = $this->model->listAll($domain_id);

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

                'rules'  => 'required|min_length[1]|is_master_goal_code_unique[program_master_goals.goal_code,id,{id}]',

                'errors' => [

                    'required' => '{field} Required',

                    'is_master_goal_code_unique' => '{field} must be unique',

                    'min_length' => '{field} min length is 1',

                ],

            ],

            'name' => [

                'label'  => 'Name',

                'rules'  => 'required|min_length[3]|is_master_goal_name_unique[program_master_goals.name,id,{id}]',

                'errors' => [

                    'required' => '{field} Required',

                    'is_master_goal_name_unique' => '{field} must be unique',

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

        ];



        $data = [

            'goal_code' => $this->request->getPost('goal_code'),

            'name' => $this->request->getPost('name'),

            'description'   => $this->request->getPost('description'),

            'domain_id' => $this->request->getPost('domain_id'),

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

        $MasterGoal = new MasterGoal();

        $MasterGoal->fill($data);

        $this->model->save($MasterGoal);

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

                'rules'  => 'required|min_length[1]|is_master_goal_code_unique[program_master_goals.goal_code,id,{id}]',

                'errors' => [

                    'required' => '{field} Required',

                    'is_master_goal_code_unique' => '{field} must be unique',

                    'min_length' => '{field} min length is 1',

                ],

            ],

            'name' => [

                'label'  => 'Name',

                'rules'  => 'required|min_length[3]|is_master_goal_name_unique[program_master_goals.name,id,{id}]',

                'errors' => [

                    'required' => '{field} Required',

                    'is_master_goal_name_unique' => '{field} must be unique',

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

                'label'  => 'Domain Type',

                'rules'  => 'required',

                'errors' => [

                    'required' => '{field} Required',

                ],

            ]

        ];



        $data = [

            'id'   => $this->request->getPost('id'),

            'goal_code' => $this->request->getPost('goal_code'),

            'name' => $this->request->getPost('name'),

            'description'   => $this->request->getPost('description'),

            'domain_id' => $this->request->getPost('domain_id'),

            'updated_by'   =>  auth()->user()->id,

        ];

        /**  Check if in use */

        $isUsed = $this->model->isUsed($data['id']);

        if ($isUsed) {

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

        $MasterGoal = new MasterGoal();

        $MasterGoal->fill($data);

        $this->model->save($MasterGoal);

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
