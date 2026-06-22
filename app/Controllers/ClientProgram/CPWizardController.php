<?php

namespace App\Controllers\ClientProgram;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientProgram\CPWizardModel;

use App\Models\MasterProgram\MasterDomainModel;
use App\Models\MasterProgram\MasterGoalModel;
use App\Models\MasterProgram\MasterTargetModel;

use App\Models\ClientProgram\ClientDomainModel;
use App\Models\ClientProgram\ClientGoalModel;
use App\Models\ClientProgram\ClientTargetModel;
use CodeIgniter\HTTP\ResponseInterface;

class CPWizardController extends AdminController
{

    /*public function index()
    {
        $this->page_title = 'Clients';
        $ClientModel = new ClientModel();
        $clients = $ClientModel->get_active_client_list();
        return  view('ClientProgram/Wizard/index', ['clients' => $clients, 'page_title' => $this->page_title]);
    }*/
    public function index()
    {
        $this->page_title = 'Program Wizard';
        $ClientModel = new ClientModel();
        $clients = $ClientModel->get_active_client_list();
        return  view('ClientProgram/Wizard/program_tree', ['clients' => $clients, 'page_title' => $this->page_title]);
    }

    public function getClientList(): ResponseInterface
    {
        $ClientModel = new ClientModel();
        $clients = $ClientModel->get_active_client_list();
        return $this->response->setJSON(['clients' => $clients]);
    }

    public function getMasterProgramWithClientInfo()
    {
        $this->page_title = 'Master Program Setups';
        $client_id = $this->request->getPost('client_id');
        $model = new CPWizardModel();
        $masterProgramWizard = $model->getMasterProgramWithClientInfo($client_id);
        return $this->response->setJSON(['masterProgramWizard' => $masterProgramWizard]); 

    }
    /*********************************************************** */
    /** Domains */
    /*********************************************************** */
    public function domain_list()
    {
        $client_id = $this->request->getPost('client_id');
        $model = new CPWizardModel();
        $data = $model->get_domains($client_id);
        $html = view('ClientProgram/Wizard/domains', ['domains' => $data]);
        $response =  $this->getResponseObject('success', '', '', [],  $html);
        return $this->response->setJSON($response);
    }
    /*********************************************************** */
    public function assign_domain_to_client()
    {
        // Retrieve client_id and master_domain_id from the POST request
        $client_id = $this->request->getPost('client_id');
        $master_domain_id = $this->request->getPost('id');

        // Instantiate the MasterDomainModel and retrieve the master domain object
        $masterDomainModel = new MasterDomainModel();
        $masterDomain = $masterDomainModel->single($master_domain_id);

        // Instantiate the CPWizardModel and check if the domain exists in client domains
        $model = new CPWizardModel();
        $isDomainExist = $model->check_domain_code_and_name_exist_in_client_domain($client_id, $masterDomain);

        // If domain or code already exists for the client, return a JSON response
        if ($isDomainExist) {
            $response =  $this->getResponseObject('error', '', 'Domain name or code already exists for this client.', [],  '');
            return $this->response->setJSON($response);
        }

        // If not, add the domain to the client_program_domains table
        $data = [
            'name' => $masterDomain->name,
            'description' => $masterDomain->description,
            'domain_code' => $masterDomain->domain_code,
            'mp_domain_id' => $masterDomain->id,
            'client_id' => $client_id,
            'created_by' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s'),
            // Add any other necessary fields here
        ];

        // Insert the new domain into the client_program_domains table
        $clientDomainModel = new ClientDomainModel();
        if ($clientDomainModel->insert($data, false)) {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('success', '', 'Domain successfully assigned to client.', [],  '');
            return $this->response->setJSON($response);
        } else {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('error', '', 'Technical Error. Contact Programer.', [],  '');
            return $this->response->setJSON($response);
        }
    }


    /*********************************************************** */
    /** Goals */
    /*********************************************************** */
    public function goal_list()
    {
        $client_id = $this->request->getPost('client_id');
        $model = new CPWizardModel();
        $domains = $model->get_client_domains($client_id);
        $goals = $model->get_goals($client_id);
        $html = view('ClientProgram/Wizard/goals', ['domains' => $domains, 'goals' => $goals]);
        $response =  $this->getResponseObject('success', '', '', [],  $html);
        return $this->response->setJSON($response);
    }
    public function goal_list_by_filter()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        if ($domain_id == "") {
            $domain_id = null;
        }
        $model = new CPWizardModel();
        $goals = $model->get_goals($client_id, $domain_id);
        $html = view('ClientProgram/Wizard/goals_table', ['goals' => $goals]);
        $response =  $this->getResponseObject('success', '', '', [],  $html);
        return $this->response->setJSON($response);
    }
    /*********************************************************** */
    public function assign_goal_to_client()
    {
        // Retrieve client_id and master_goal_id from the POST request
        $client_id = $this->request->getPost('client_id');
        $master_goal_id = $this->request->getPost('id');

        // Instantiate the MasterGoalModel and retrieve the master goal object
        $masterGoalModel = new MasterGoalModel();
        $masterGoal = $masterGoalModel->single($master_goal_id);

        // Instantiate the CPWizardModel and check if the goal exists in client goals
        $model = new CPWizardModel();
        

        // Fetch the corresponding domain_id from client_program_domains
        $domain_id = $model->get_client_domain_id($client_id, $masterGoal->domain_id);

        // If the domain_id is not found, return an error response
        if (!$domain_id) {

            $response =  $this->getResponseObject('error', '', 'Corresponding domain for this goal not found for the client.', [],  '');
            return $this->response->setJSON($response);
        }

        $isGoalExist = $model->check_goal_code_and_name_exist_in_client_goal($client_id, $domain_id,$masterGoal);

        // If goal or code already exists for the client, return a JSON response
        if ($isGoalExist) {
            $response =  $this->getResponseObject('error', '', 'Goal name or code already exists for this client.', [],  '');
            return $this->response->setJSON($response);
        }

        // Prepare the data for insertion
        $data = [
            'domain_id' => $domain_id,
            'name' => $masterGoal->name,
            'description' => $masterGoal->description,
            'goal_code' => $masterGoal->goal_code,
            'mp_goal_id' => $masterGoal->id,
            'client_id' => $client_id,
            'created_by' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s'),
            // Add any other necessary fields here
        ];

        // Insert the new goal into the client_program_goals table
        $clientGoalModel = new ClientGoalModel();
        if ($clientGoalModel->insert($data, false)) {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('success', '', 'Goal successfully assigned to client.', [],  '');
            return $this->response->setJSON($response);
        } else {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('error', '', 'Technical Error. Contact Programer.', [],  '');
            return $this->response->setJSON($response);
        }
    }


    /*********************************************************** */
    /** Targets */
    /*********************************************************** */
    public function target_list()
    {
        $client_id = $this->request->getPost('client_id');
        $model = new CPWizardModel();
        $domains = $model->get_client_domains($client_id);
        $targets = $model->get_targets($client_id);
        $html = view('ClientProgram/Wizard/targets', ['domains' => $domains, 'targets' => $targets]);
        $response =  $this->getResponseObject('success', '', '', [],  $html);
        return $this->response->setJSON($response);
    }
    /*********************************************************** */
    public function target_list_by_filter()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $goal_id = $this->request->getPost('goal_id');
        if ($domain_id == "") {
            $domain_id = null;
        }
        if ($goal_id == "") {
            $goal_id = null;
        }
        $model = new CPWizardModel();
        $targets = $model->get_targets($client_id, $domain_id, $goal_id);
        $html = view('ClientProgram/Wizard/targets_table', ['targets' => $targets]);
        $response =  $this->getResponseObject('success', '', '', [],  $html);
        return $this->response->setJSON($response);
    }
    /*********************************************************** */
    public function get_client_domain_goals()
    {
        $client_id = $this->request->getPost('client_id');
        $domain_id = $this->request->getPost('domain_id');
        $model = new CPWizardModel();
        $goals = $model->get_client_goals($client_id, $domain_id);
        $response =  $this->getResponseObject('success', '', '', [], $goals);
        return $this->response->setJSON($response);
    }
    /*********************************************************** */

    public function assign_target_to_client()
    {
        // Retrieve client_id and master_goal_id from the POST request
        $client_id = $this->request->getPost('client_id');
        $master_target_id = $this->request->getPost('id');

        // Instantiate the MasterGoalModel and retrieve the master goal object
        $masterTargetModel = new MasterTargetModel();
        $masterTarget = $masterTargetModel->single($master_target_id);

        // Instantiate the CPWizardModel and check if the goal exists in client goals
        $model = new CPWizardModel();
       

        // Fetch the corresponding goal_id from client_program_domains
        $goal_id = $model->get_client_goal_id($client_id, $masterTarget->goal_id);

        // If the domain_id is not found, return an error response
        if (!$goal_id) {

            $response =  $this->getResponseObject('error', '', 'Corresponding Goal for this goal not found for the client.', [],  '');
            return $this->response->setJSON($response);
        }

        $isTargetExist = $model->check_target_exist_in_client_targets($client_id, $goal_id,$masterTarget);

        // If goal or code already exists for the client, return a JSON response
        if ($isTargetExist) {
            $response =  $this->getResponseObject('error', '', 'Target name already exists for this client.', [],  '');
            return $this->response->setJSON($response);
        }

        // Prepare the data for insertion
        $data = [
            'goal_id' => $goal_id,
            'name' => $masterTarget->name,
            'description' => $masterTarget->description,
            'mp_target_id' => $masterTarget->id,
            'client_id' => $client_id,
            'created_by' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s'),
            // Add any other necessary fields here
        ];

        // Insert the new goal into the client_program_goals table
        $clientTargetModel = new ClientTargetModel();
        if ($clientTargetModel->insert($data, false)) {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('success', '', 'Target successfully assigned to client.', [],  '');
            return $this->response->setJSON($response);
        } else {
            // Return a JSON response indicating success
            $response =  $this->getResponseObject('error', '', 'Technical Error. Contact Programer.', [],  '');
            return $this->response->setJSON($response);
        }
    }
    /*********************************************************** */
}
