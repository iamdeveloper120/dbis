<?php

namespace App\Controllers\Mands;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Mands\MandsReinforcerModel;
use App\Models\Mands\ClientMandsDefaultReinforcerModel;
use App\Models\ClientConfiguration\ClientModel;
use App\Entities\Mands\MandsReinforcer;

class MandsReinforcerController extends AdminController
{
    use ResponseTrait;

    protected $model;
    protected $clientDefaultsModel;
    protected $clientModel;

    public function __construct()
    {
        $this->model = new MandsReinforcerModel();
        $this->clientDefaultsModel = new ClientMandsDefaultReinforcerModel();
        $this->clientModel = new ClientModel();
    }

    /************************************************************************* */
    public function index()
    {
        $this->page_title = 'Mands Program - Reinforcer';
        $clients = $this->clientModel->get_active_client_list();

        return view('Mands/Reinforcer/index', [
            'page_title' => $this->page_title,
            'clients' => $clients,
        ]);
    }

    /************************************************************************* */
    public function list()
    {
        $data = $this->model->findAll();
        $response = $this->getResponseObject('success', 'Reinforcer', 'Listed successfully', [], $data);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function single()
    {
        $id = (int) $this->request->getPost('id');
        $reinforcer = $this->model->find($id);
        $response = $this->getResponseObject('success', '', '', [], $reinforcer);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function create()
    {
        $rules = [
            'name' => [
                'label'  => 'Reinforcer',
                'rules'  => 'required|min_length[1]|is_unique[mands_reinforcer.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
        ];

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'created_by' => auth()->user()->id,
            'updated_by' => null,
            'updated_at' => null,
        ];

        if (!$this->validateData($data, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $entity = new MandsReinforcer();
        $entity->fill($data);

        if (!$this->model->save($entity)) {
            $response = $this->getResponseObject('error', 'Error', 'Contact system administrator', [], []);
            return $this->response->setJSON($response);
        }

        $reinforcer = $this->model->find($this->model->getInsertID());
        $response = $this->getResponseObject('success', 'Reinforcer', 'Created successfully', [], $reinforcer);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function update()
    {
        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ],
            'name' => [
                'label'  => 'Reinforcer',
                'rules'  => 'required|min_length[1]|is_unique[mands_reinforcer.name,id,{id}]',
                'errors' => [
                    'required' => '{field} Required',
                    'is_unique' => '{field} must be unique',
                    'min_length' => '{field} min length is 1',
                ],
            ],
        ];

        $id = (int) $this->request->getPost('id');
        $data = [
            'id' => $id,
            'name' => trim((string) $this->request->getPost('name')),
            'updated_by' => auth()->user()->id,
        ];

        $reinforcer = $this->model->find($id);
        if (!$reinforcer) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['id' => 'Invalid reinforcer id'], []);
            return $this->response->setJSON($response);
        }

        if (!$this->validateData($data, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $entity = new MandsReinforcer();
        $entity->fill($data);

        if (!$this->model->save($entity)) {
            $response = $this->getResponseObject('error', 'Error', 'Contact system administrator', [], []);
            return $this->response->setJSON($response);
        }

        $updated = $this->model->find($id);
        $response = $this->getResponseObject('success', 'Reinforcer', 'Updated successfully', [], $updated);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function delete()
    {
        $id = (int) $this->request->getPost('id');
        $data = ['id' => $id];

        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} Required',
                ],
            ]
        ];

        if (!$this->validateData($data, $rules)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $this->validator->getErrors(), []);
            return $this->response->setJSON($response);
        }

        $reinforcer = $this->model->find($id);
        if (!$reinforcer) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['id' => 'Invalid reinforcer id'], []);
            return $this->response->setJSON($response);
        }

        $result = $this->model->delete($id);
        if (!$result) {
            $response = $this->getResponseObject('error', 'Error', 'Contact system administrator', [], []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Reinforcer', 'Deleted successfully', [], []);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function search()
    {
        $queryText = $this->request->getGet('query');
        $clientId = (int) $this->request->getGet('clientId');

        if (empty($queryText)) {
            return $this->respond([]);
        }

        $db = \Config\Database::connect();
        $query = $db->query(
            "
            SELECT r.name,
                   CASE WHEN s.client_id IS NOT NULL THEN 1 ELSE 0 END AS priority
            FROM mands_reinforcer r
            LEFT JOIN mands_session_data s
                   ON r.name = s.reinforcer_input
                  AND s.client_id = ?
            WHERE r.name LIKE ?
            GROUP BY r.name
            ORDER BY priority DESC, r.name ASC
            ",
            [$clientId, $queryText . '%']
        );

        $results = $query->getResultArray();
        return $this->respond($results);
    }

    /************************************************************************* */
    public function clientDefaultsList()
    {
        $clientId = (int) $this->request->getPost('client_id');

        if ($clientId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['client_id' => 'Client is required'], []);
            return $this->response->setJSON($response);
        }

        $data = $this->clientDefaultsModel
            ->where('client_id', $clientId)
            ->orderBy('order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $response = $this->getResponseObject('success', 'Reinforcer Defaults', 'Listed successfully', [], $data);
        return $this->response->setJSON($response);
    }

    /************************************************************************* */
    public function clientDefaultsSave()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $defaults = $this->request->getPost('defaults');

        if ($clientId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['client_id' => 'Client is required'], []);
            return $this->response->setJSON($response);
        }

        if (is_string($defaults)) {
            $decoded = json_decode($defaults, true);
            $defaults = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($defaults)) {
            $defaults = [];
        }

        if (count($defaults) > 10) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['defaults' => 'A maximum of 10 defaults is allowed'], []);
            return $this->response->setJSON($response);
        }

        $cleanNames = [];
        foreach ($defaults as $item) {
            if (is_array($item)) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $cleanNames[] = $name;
                }
            }
        }

        if (!in_array(count($cleanNames), [0, 10], true)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['defaults' => 'Provide either all 10 defaults or leave all blank'], []);
            return $this->response->setJSON($response);
        }

        $rows = [];
        foreach ($cleanNames as $index => $name) {
            $rows[] = [
                'client_id' => $clientId,
                'name' => $name,
                'order' => $index + 1,
                'created_by' => auth()->user()->id,
                'updated_by' => null,
            ];
        }

        $this->clientDefaultsModel->where('client_id', $clientId)->delete();
        if (!empty($rows)) {
            $this->clientDefaultsModel->insertBatch($rows);
        }

        $data = $this->clientDefaultsModel
            ->where('client_id', $clientId)
            ->orderBy('order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $response = $this->getResponseObject('success', 'Reinforcer Defaults', 'Saved successfully', [], $data);
        return $this->response->setJSON($response);
    }
}

