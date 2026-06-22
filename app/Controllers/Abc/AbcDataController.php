<?php

namespace App\Controllers\Abc;

use App\Controllers\AdminController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientProblemBehavior\ClientAbcItemModel;
use App\Models\ClientProblemBehavior\MasterAbcItemModel;

class AbcDataController extends AdminController
{
    use ResponseTrait;

    private const VALID_CATEGORIES = ['antecedent', 'behavior', 'consequence'];

    protected $clientModel;
    protected $clientAbcModel;
    protected $masterAbcModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->clientAbcModel = new ClientAbcItemModel();
        $this->masterAbcModel = new MasterAbcItemModel();
    }

    public function index()
    {
        $this->page_title = 'Manage ABC Data';
        $clients = $this->clientModel->get_active_client_list();

        return view('Abc/index', [
            'page_title' => $this->page_title,
            'clients' => $clients,
            'categories' => self::VALID_CATEGORIES,
        ]);
    }

    public function masterList()
    {
        $category = strtolower(trim((string) $this->request->getGetPost('category')));
        $query = $this->masterAbcModel;

        if ($category !== '') {
            if (!$this->isValidCategory($category)) {
                return $this->response->setJSON(
                    $this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['category' => 'Invalid category'], [])
                );
            }
            $query = $query->where('category', $category);
        }

        $rows = $query->orderBy('value', 'ASC')->findAll();
        $data = [
            'antecedent' => [],
            'behavior' => [],
            'consequence' => [],
        ];
        foreach ($rows as $row) {
            $cat = (string) ($row['category'] ?? '');
            if (!isset($data[$cat])) {
                continue;
            }
            $value = trim((string) ($row['value'] ?? ''));
            if ($value !== '') {
                $data[$cat][] = $value;
            }
        }

        return $this->response->setJSON($this->getResponseObject('success', 'Master ABC', 'Listed successfully', [], $data));
    }

    public function clientList()
    {
        $clientId = (int) $this->request->getGetPost('client_id');
        $category = strtolower(trim((string) $this->request->getGetPost('category')));

        if ($clientId <= 0) {
            return $this->response->setJSON($this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['client_id' => 'Client is required'], []));
        }

        if ($category !== '' && !$this->isValidCategory($category)) {
            return $this->response->setJSON($this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['category' => 'Invalid category'], []));
        }

        $data = [
            'antecedent' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'antecedent'),
            'behavior' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'behavior'),
            'consequence' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'consequence'),
        ];

        if ($category !== '') {
            $data = [$category => $data[$category]];
        }

        return $this->response->setJSON($this->getResponseObject('success', 'Client ABC', 'Listed successfully', [], $data));
    }

    public function clientSave()
    {
        $clientId = (int) $this->request->getPost('client_id');
        if ($clientId <= 0) {
            return $this->response->setJSON($this->getResponseObject('error', 'Validation_Error', 'Validation Errors', ['client_id' => 'Client is required'], []));
        }

        $antecedents = $this->normalizeValuesInput($this->request->getPost('antecedents'));
        $behaviors = $this->normalizeValuesInput($this->request->getPost('behaviors'));
        $consequences = $this->normalizeValuesInput($this->request->getPost('consequences'));

        $validationErrors = [];
        if (count($antecedents) < 1) {
            $validationErrors['antecedents'] = 'At least one antecedent is required';
        }
        if (count($behaviors) < 1) {
            $validationErrors['behaviors'] = 'At least one behavior is required';
        }
        if (count($consequences) < 1) {
            $validationErrors['consequences'] = 'At least one consequence is required';
        }

        if (!empty($validationErrors)) {
            return $this->response->setJSON($this->getResponseObject('error', 'Validation_Error', 'Validation Errors', $validationErrors, []));
        }

        $userId = (int) auth()->user()->id;
        $this->clientAbcModel->replaceClientCategoryValues($clientId, 'antecedent', $antecedents, $userId);
        $this->clientAbcModel->replaceClientCategoryValues($clientId, 'behavior', $behaviors, $userId);
        $this->clientAbcModel->replaceClientCategoryValues($clientId, 'consequence', $consequences, $userId);

        $data = [
            'antecedent' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'antecedent'),
            'behavior' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'behavior'),
            'consequence' => $this->clientAbcModel->getClientValuesByCategory($clientId, 'consequence'),
        ];

        return $this->response->setJSON($this->getResponseObject('success', 'Client ABC', 'Saved successfully', [], $data));
    }

    private function isValidCategory(string $category): bool
    {
        return in_array($category, self::VALID_CATEGORIES, true);
    }

    private function normalizeValuesInput($input): array
    {
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                $input = $decoded;
            } else {
                $input = [$input];
            }
        }

        if (!is_array($input)) {
            return [];
        }

        $result = [];
        foreach ($input as $value) {
            $v = trim((string) $value);
            if ($v !== '') {
                $result[] = $v;
            }
        }

        return array_values(array_unique($result));
    }
}
