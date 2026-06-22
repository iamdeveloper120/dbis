<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;
use App\Entities\ClientProgram\ClientGoal;

class ClientGoalModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table      = 'client_program_goals';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = ClientGoal::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['goal_code', 'name', 'description', 'domain_id', 'client_id', 'mp_goal_id', 'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];
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
    protected $validationMessages   = [];
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

    /************************************************************************ */
    public function listAll($client_id, $domain_id)
    {
        $builder = $this->db->table('client_program_goals');
        $builder->select('client_program_goals.*, client_program_domains.domain_code,client_program_domains.name as domain_name');
        $builder->join('client_program_domains', 'client_program_goals.domain_id = client_program_domains.id');
        $builder->where('client_program_goals.domain_id', $domain_id);
        $builder->where('client_program_goals.client_id', $client_id);
        $builder->orderBy('client_program_goals.name', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function single($id)
    {
        $builder = $this->db->table('client_program_goals');
        $builder->select('client_program_goals.*, client_program_domains.domain_code,client_program_domains.name as domain_name');
        $builder->join('client_program_domains', 'client_program_domains.id = client_program_goals.domain_id');
        $builder->where('client_program_goals.id', $id);
        $result = $builder->get()->getRow();
        return $result;
    }

    /************************************************************************ */
    public function isUsed($id)
    {
        $builder = $this->db->table('client_program_targets');
        $builder->select('goal_id');
        $builder->where('goal_id', $id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        $builder = $this->db->table('client_probe_set');
        $builder->select('goal_id');
        $builder->where('goal_id', $id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }

        return false;
    }
    /************************************************************************ */
}
