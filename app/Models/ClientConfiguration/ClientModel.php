<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;
use App\Entities\ClientConfiguration\Client;

class ClientModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table            = 'clients';
    protected $primaryKey       = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = Client::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['mrn', 'internal_mrn', 'first_name', 'last_name', 'status', 'description', 'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [
        'mrn' => [
            'is_unique' => 'Sorry. That MRN has already been taken. Please choose another.',
        ],
        'internal_mrn' => [
            'is_unique' => 'Sorry. That Internal MRN has already been taken. Please choose another.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /************************************************************************************************ */

    public function getClientById($client_id): ?Client
    {
        return $this->asObject(Client::class)->find($client_id);
    }
    /************************************************************************************************ */
    public function get_all_client_list()
    {
        $builder = $this->db->table('clients');
        $builder->select('clients.*');

        // Join with client_user_mapping and users if the user is not in the specified groups
        if (!auth()->user()->inGroup('superadmin', 'management', 'admin')) {
            $userId = auth()->user()->id; // Get the current user ID
            $builder->join('client_user_mapping', 'client_user_mapping.client_id = clients.id');
            $builder->join('users', 'client_user_mapping.user_id = users.id');
            $builder->where('users.id', $userId);
        }

        $result = $builder->get()->getResult(Client::class);
        return $result;
    }
    /************************************************************************************************ */
    public function get_active_client_list()
    {
        $builder = $this->db->table('clients');
        $builder->select('clients.*');
        $builder->where('clients.status', 1);

        // Join with client_user_mapping and users if the user is not in the specified groups
        if (!auth()->user()->inGroup('superadmin', 'management', 'admin')) {
            $userId = auth()->user()->id; // Get the current user ID
            $builder->join('client_user_mapping', 'client_user_mapping.client_id = clients.id');
            $builder->join('users', 'client_user_mapping.user_id = users.id');
            $builder->where('users.id', $userId);
        }
        $builder->orderBy('clients.internal_mrn', 'asc');
        $result = $builder->get()->getResult(Client::class);
        return $result;
    }
    public function get_dashboard_active_client_list()
    {
        $builder = $this->db->table('clients');
        $builder->select('clients.*');
        $builder->where('clients.status', 1);

        // Join with client_user_mapping and users if the user is not in the specified groups
        if (!auth()->user()->inGroup('superadmin', 'management')) {
            $userId = auth()->user()->id; // Get the current user ID
            $builder->join('client_user_mapping', 'client_user_mapping.client_id = clients.id');
            $builder->join('users', 'client_user_mapping.user_id = users.id');
            $builder->where('users.id', $userId);
        }
        $builder->orderBy('clients.internal_mrn', 'asc');
        $result = $builder->get()->getResult(Client::class);
        return $result;
    }
    /************************************************************************************************ */
    public function get_inactive_client_list()
    {
        $builder = $this->db->table('clients');
        $builder->select('clients.*');
        $builder->where('clients.status', 0);

        // Join with client_user_mapping and users if the user is not in the specified groups
        if (!auth()->user()->inGroup('superadmin', 'management', 'admin')) {
            $userId = auth()->user()->id; // Get the current user ID
            $builder->join('client_user_mapping', 'client_user_mapping.client_id = clients.id');
            $builder->join('users', 'client_user_mapping.user_id = users.id');
            $builder->where('users.id', $userId);
        }

        $result = $builder->get()->getResult(Client::class);
        return $result;
    }

    /************************************************************************************************ */

    public function get_user_clients_ids($user_id)
    {
        $builder = $this->db->table('client_user_mapping');
        $builder->select('client_id');
        $builder->where('user_id', $user_id);
        $query = $builder->get();
        $result_array = [''];
        foreach ($query->getResult() as $row) {
            $result_array[] = $row->client_id;
        }
        return $result_array;
    }
    public function get_all_active_clients_ids()
    {

        $builder = $this->db->table('clients');
        $builder->select('id');
        $builder->where('status', 1);
        $query = $builder->get();
        $result_array = [''];
        foreach ($query->getResult() as $row) {
            $result_array[] = $row->id;
        }
        return $result_array;
    }
    public function get_user_clients($user_id)
    {
        $builder = $this->db->table('client_user_mapping');
        $builder->select('*');
        $builder->where('user_id', $user_id);
        $query = $builder->get();
        return $query->getResult();
    }
    public function attach_client_to_user($user_id, $client_id)
    {

        $builder = $this->db->table('client_user_mapping');
        $data = [
            'user_id'       => $user_id,
            'client_id'        => $client_id
        ];

        return $builder->insert($data);
    }
    public function detach_client_from_user($user_id, $client_id)
    {
        $builder = $this->db->table('client_user_mapping');
        return $builder->delete(['user_id' => $user_id, 'client_id' => $client_id]);
    }
    public function detach_client_from_all_user($client_id)
    {

        $builder = $this->db->table('client_user_mapping');
        return $builder->delete(['client_id' => $client_id]);
    }
    public function isClientInUse($client_id)
    {
        $builder = $this->db->table('client_user_mapping');
        $builder->select('client_id');
        $builder->where('client_id', $client_id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_program_domains');
        $builder->select('client_id');
        $builder->where('client_id', $client_id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('mands_session_data');
        $builder->select('client_id');
        $builder->where('client_id', $client_id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }



        return false;
    }
    public function clientActiveProgram($client_id)
    {
        $builder = $this->db->table('daily_session_data_processed dp');
        $builder->select('g.goal_code, g.name as goal_name, d.domain_code');
        $builder->join('client_program_goals g', 'g.id = dp.goal_id');
        $builder->join('client_program_domains d', 'd.id = g.domain_id');
        $builder->where('dp.client_id', $client_id);
        $builder->whereIn('dp.next_phase_id', [2, 3]);
        $builder->distinct();

        $query = $builder->get();
        $results = $query->getResult();
        if (empty($results)) {
            return 'None';
        }
        $output = array_map(function ($row) {
            return '<span class="badge bg-dark-subtle text-muted" style="--vz-badge-font-size: 12px;  --vz-badge-font-weight: 400;background-color: transparent !important;">(' . $row->domain_code . ') - (' . $row->goal_code . ') -' . $row->goal_name . ',</span>';
        }, $results);

        return implode('', $output);
    }

    public function getProgressAll(array $clientIds): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $rows = $this->db->table('view_client_target_progress')
            ->select('client_id, internal_mrn, introduced, retained, percentage')
            ->whereIn('client_id', $clientIds)
            ->orderBy('percentage', 'DESC')
            ->get()
            ->getResultArray();

        // shape for dashboard cards
        return array_map(static function ($r) {
            return [
                'client'     => $r['internal_mrn'],
                'introduced' => (int) $r['introduced'],
                'retained'   => (int) $r['retained'],
                'percentage' => (float) $r['percentage'],
            ];
        }, $rows);
    }

    public function getProgressByClient(int $clientId): array
    {
        $r = $this->db->table('view_client_target_progress')
            ->select('client_id, internal_mrn, introduced, retained, percentage')
            ->where('client_id', $clientId)
            ->get()
            ->getRowArray();

        if (!$r) {
            return ['client' => null, 'introduced' => 0, 'retained' => 0, 'percentage' => 0];
        }

        return [
            'client'     => $r['internal_mrn'],
            'introduced' => (int) $r['introduced'],
            'retained'   => (int) $r['retained'],
            'percentage' => (float) $r['percentage'],
        ];
    }
}
