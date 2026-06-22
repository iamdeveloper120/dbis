<?php

namespace App\Controllers\ClientProgram;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;

use App\Models\ClientProgram\ClientProgramModel;
use App\Models\ClientProgram\ClientStimulusStepModel;
use App\Models\ClientProgram\ClientStimulusChainModel;
use App\Models\ClientProgram\ClientTargetModel;


use CodeIgniter\HTTP\ResponseInterface;

class ClientProgramController extends AdminController
{

    public function treeView()
    {
        return view('ClientProgram/program_view', ['page_title' => 'Client Program']);
    }
    /*********************************************************** */
    public function getClientList(): ResponseInterface
    {
        $ClientModel = new ClientModel();
        $clients = $ClientModel->get_active_client_list();
        return $this->response->setJSON(['clients' => $clients]);
    }

    public function getClientProgramInfo()
    {
        $client_id = $this->request->getPost('client_id');
        $model = new ClientProgramModel();
        $clientProgram = $model->getClientProgramInfo($client_id);
        return $this->response->setJSON(['clientProgram' => $clientProgram]);
    }
    public function getClientSelectedGoalProbeSet()
    {
        $client_id = $this->request->getPost('client_id');
        $goal_id = $this->request->getPost('goal_id');
        $model = new ClientProgramModel();
        $probe_set = $model->getClientSelectedGoalProbeSet($client_id, $goal_id);
        return $this->response->setJSON($probe_set);
    }


    public function getClientSelectedStimulusTargetUpdatedDetail()
    {
        $clientId = $this->request->getPost('client_id');
        $targetId = $this->request->getPost('target_id');

        $model = new ClientProgramModel();
        $chaining = $model->getStimulusChainingDetailsForTarget($targetId);

        return $this->response->setJSON([
            'target_id' => $targetId,
            'chaining' => [
                'method' => $chaining['chaining_method'] ?? null,
                'total_steps' => isset($chaining['step_count']) ? (int) $chaining['step_count'] : null,
                'rule_override' => isset($chaining['rule_override']) ? json_decode($chaining['rule_override'], true) : null,
            ],
        ]);
    }
    /******************************************************************** */
    public function loadStimulusStepsEditor()
    {
        $clientId = $this->request->getPost('client_id');
        $goalId   = $this->request->getPost('goal_id');
        $targetId = $this->request->getPost('target_id');

        $model = new ClientStimulusStepModel();
        $steps = $model->where('target_id', $targetId)->orderBy('step_number', 'asc')->findAll();

        $clientTargetModel = new ClientTargetModel();
        $target = $clientTargetModel->single($targetId);

        $html = view('ClientProgram/stimulus_steps_editor', [
            'client_id' => $clientId,
            'goal_id'   => $goalId,
            'target_id' => $targetId,
            'steps' => $steps,
            'target'     => $target,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'html' => $html,
        ]);
    }
    public function addStimulusStep()
    {
        $model = new ClientStimulusStepModel();


        $data = [
            'target_id'     => $this->request->getPost('target_id'),
            'sd_text'       => $this->request->getPost('sd_text'),
            'c_text'        => $this->request->getPost('c_text'),
            'response_text' => $this->request->getPost('response_text'),
            'created_by' => auth()->user()->id,
        ];

        $clientProgramModel = new ClientProgramModel();
        if ($clientProgramModel->targetHasSessionData($data['target_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot add step: Data has already been collected for this target.'
            ]);
        }

        // Get step number
        $lastStep = $model->where('target_id', $data['target_id'])
            ->orderBy('step_number', 'DESC')
            ->first();
        $data['step_number'] = $lastStep ? $lastStep->step_number + 1 : 1;

        $newId = $model->insert($data);
        $newStep = $model->find($newId);

        // Return ONLY the rendered step
        $stepHtml = view('ClientProgram/_step_row', [
            'step' => $newStep,
            'index' => $data['step_number'] - 1,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Step added',
            'step_html' => $stepHtml,
        ]);
    }

    public function updateStimulusStep()
    {
        $id = $this->request->getPost('id');
        $model = new ClientStimulusStepModel();
        $step = $model->find($id);
        if (!$step) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Step not found'
            ]);
        }

        $clientProgramModel = new ClientProgramModel();
        if ($clientProgramModel->targetHasSessionData((int)$step->target_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot update: Data has already been collected for this target.'
            ]);
        }
        $model->update($id, [
            'sd_text'       => $this->request->getPost('sd_text'),
            'c_text'        => $this->request->getPost('c_text'),
            'response_text' => $this->request->getPost('response_text'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Step updated successfully'
        ]);
    }

    public function deleteStimulusStep()
    {
        $id = $this->request->getPost('id');
        $model = new ClientStimulusStepModel();

        $step = $model->find($id);
        if (!$step) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Step not found'
            ]);
        }

        $clientProgramModel = new ClientProgramModel();
        if ($clientProgramModel->targetHasSessionData((int)$step->target_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete: Data has already been collected for this target.'
            ]);
        }

        $model->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Step deleted successfully'
        ]);
    }

    public function reorderStimulusSteps()
    {
        $steps = json_decode($this->request->getPost('steps'));
        $model = new ClientStimulusStepModel();
        $clientProgramModel = new ClientProgramModel();
        if (!empty($steps)) {
            $firstStep = $model->find($steps[0]->id ?? null);
            if ($firstStep && $clientProgramModel->targetHasSessionData((int)$firstStep->target_id)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot reorder: Data has already been collected for this target.'
                ]);
            }

            foreach ($steps as $step) {
                $model->update($step->id, ['step_number' => $step->step_number]);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Steps reordered successfully'
        ]);
    }
    /******************************************************************** */
    public function loadStimulusChainEditor()
    {
        $clientId = $this->request->getPost('client_id');
        $goalId   = $this->request->getPost('goal_id');
        $targetId = $this->request->getPost('target_id');


        $model = new ClientStimulusChainModel();
        $chain = $model->asArray()->where('target_id', $targetId)->first();

        $clientTargetModel = new ClientTargetModel();
        $target = $clientTargetModel->single($targetId);

        $html = view('ClientProgram/stimulus_chain_editor', [
            'client_id' => $clientId,
            'goal_id'   => $goalId,
            'target_id' => $targetId,
            'chain' => $chain,
            'target'     => $target,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'html' => $html,
        ]);
    }
    public function saveStimulusChain()
    {
        $targetId = $this->request->getPost('target_id');
        $selectedMethod = $this->request->getPost('chaining_method');

        if (!$targetId || !$selectedMethod) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required input.'
            ]);
        }

        // Load models
        $chainModel = new \App\Models\ClientProgram\ClientStimulusChainModel();
        $stepSessionModel = new \App\Models\ClientSessions\StimulusStepSessionsDataModel();

        // Default chaining rule skeleton
        $fullRule = [
            'forward' => [
                'step_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 'IND'],
                'overall_mastery' => ['type' => 'all_steps_mastered']
            ],
            'backward' => [
                'step_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 'IND'],
                'overall_mastery' => ['type' => 'all_steps_mastered']
            ],
            'total_task' => [
                'overall_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 100]
            ]
        ];

        $existing = $chainModel->asArray()->where('target_id', $targetId)->first();
        $submittedRule = $this->request->getPost($selectedMethod);

        // Merge user-defined rule values
        if ($selectedMethod === 'forward' || $selectedMethod === 'backward') {
            $fullRule[$selectedMethod]['step_mastery']['value'] = (int) ($submittedRule['step_mastery']['value'] ?? 3);
        }

        if ($selectedMethod === 'total_task') {
            $fullRule[$selectedMethod]['overall_mastery']['value'] = (int) ($submittedRule['overall_mastery']['value'] ?? 3);
            $fullRule[$selectedMethod]['overall_mastery']['check'] = (int) ($submittedRule['overall_mastery']['check'] ?? 100);
        }

        // 🛑 Safety Check: Prevent switching to/from total_task if step session data already exists
        if ($existing && $existing['method'] !== $selectedMethod) {
            $hasForwardData = $stepSessionModel->where(['target_id' => $targetId, 'method' => 'forward'])->countAllResults() > 0;
            $hasBackwardData = $stepSessionModel->where(['target_id' => $targetId, 'method' => 'backward'])->countAllResults() > 0;
            $hasTotalTaskData = $stepSessionModel->where(['target_id' => $targetId, 'method' => 'total_task'])->countAllResults() > 0;

            // Block total_task <=> forward/backward
            if (
                ($existing['method'] === 'total_task' && ($hasTotalTaskData) && in_array($selectedMethod, ['forward', 'backward'])) ||
                (in_array($existing['method'], ['forward', 'backward']) && ($hasForwardData || $hasBackwardData) && $selectedMethod === 'total_task')
            ) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot switch between total task and forward/backward after data collection has started.'
                ]);
            }
        }

        // Save/update
        $data = [
            'rule_override' => json_encode($fullRule),
            'updated_by' => auth()->user()->id,
            'updated_at' => currentDate('Y-m-d H:i:s'),
        ];

        if ($existing) {
            if ($existing['method'] !== $selectedMethod) {
                $data['method'] = $selectedMethod;
            }
            $chainModel->update($existing['id'], $data);
        } else {
            $data['target_id'] = $targetId;
            $data['method'] = $selectedMethod;
            $data['created_by'] = auth()->user()->id;
            $chainModel->insert($data);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Chaining configuration saved successfully.'
        ]);
    }

    public function saveStimulusChain1()
    {
        // Default rule skeleton
        $fullRule = [
            'forward' => [
                'step_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 'IND'],
                'overall_mastery' => ['type' => 'all_steps_mastered']
            ],
            'backward' => [
                'step_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 'IND'],
                'overall_mastery' => ['type' => 'all_steps_mastered']
            ],
            'total_task' => [
                'overall_mastery' => ['type' => 'consecutive', 'value' => 3, 'check' => 100]
            ]
        ];
        $targetId = $this->request->getPost('target_id');
        $selectedMethod = $this->request->getPost('chaining_method');

        if (!$targetId || !$selectedMethod) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required input.'
            ]);
        }

        $model = new ClientStimulusChainModel();
        $programModel = new ClientProgramModel();

        $existing = $model->asArray()->where('target_id', $targetId)->first();

        // Fetch the rule section for the selected method
        // Merge user input into selected method only
        $submittedRule = $this->request->getPost($selectedMethod);

        if ($selectedMethod === 'forward' || $selectedMethod === 'backward') {
            $fullRule[$selectedMethod]['step_mastery']['value'] = (int) ($submittedRule['step_mastery']['value'] ?? 3);
        }

        if ($selectedMethod === 'total_task') {
            $fullRule[$selectedMethod]['overall_mastery']['value'] = (int) ($submittedRule['overall_mastery']['value'] ?? 3);
            $fullRule[$selectedMethod]['overall_mastery']['check'] = (int) ($submittedRule['overall_mastery']['check'] ?? 100);
        }

        // Save only updated rule config
        $data = [
            'rule_override' => json_encode($fullRule),
            'created_by' => auth()->user()->id,
        ];

        // Update method if changed (only if allowed)
        if ($existing) {
            /*if ($programModel->targetHasSessionData($targetId) && $existing['method'] !== $selectedMethod) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot change chaining method after data collection has started.'
                ]);
            }*/

            if ($existing['method'] !== $selectedMethod) {
                $data['method'] = $selectedMethod;
            }

            $model->update($existing['id'], $data);
        } else {
            $data['target_id'] = $targetId;
            $data['method'] = $selectedMethod;
            $model->insert($data);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Chaining configuration saved successfully.'
        ]);
    }

    /******************************************************************** */
}
