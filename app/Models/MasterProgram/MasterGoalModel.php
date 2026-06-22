<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;
use App\Entities\MasterProgram\MasterGoal;

class MasterGoalModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table      = 'program_master_goals';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = MasterGoal::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['goal_code', 'name', 'description', 'domain_id', 'combination_id', 'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];
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
    public function listAll($domain_id)
    {
        $builder = $this->db->table('program_master_goals');
        $builder->select('program_master_goals.*, program_master_domains.domain_code,program_master_domains.name as domain_name');
        $builder->join('program_master_domains', 'program_master_domains.id = program_master_goals.domain_id');
        $builder->where('program_master_goals.domain_id', $domain_id);
        $builder->orderBy('program_master_goals.name', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function single($id)
    {
        $builder = $this->db->table('program_master_goals');
        $builder->select('program_master_goals.*, program_master_domains.domain_code,program_master_domains.name as domain_name');
        $builder->join('program_master_domains', 'program_master_domains.id = program_master_goals.domain_id');
        $builder->where('program_master_goals.id', $id);
        $result = $builder->get()->getRow();
        return $result;
    }

    /************************************************************************ */
    public function isUsed($id)
    {
        $builder = $this->db->table('program_master_targets');
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
