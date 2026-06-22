<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;
use App\Entities\MasterProgram\MasterDomain;

class MasterDomainModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table      = 'program_master_domains';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = MasterDomain::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['domain_code', 'name', 'description',  'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];

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
    public function listAll()
    {
        $builder = $this->db->table('program_master_domains');
        $builder->select('program_master_domains.*');
        $builder->orderBy('program_master_domains.name', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function single($id)
    {
        $builder = $this->db->table('program_master_domains');
        $builder->select('program_master_domains.*');
        $builder->where('program_master_domains.id', $id);
        $result = $builder->get()->getRow();
        return $result;
    }
    /************************************************************************ */
    public function isUsed($id)
    {
        $builder = $this->db->table('program_master_goals');
        $builder->select('domain_id');
        $builder->where('domain_id', $id);
        $builder->limit(1);
        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            return true;
        }
        return false;
    }
    /************************************************************************ */
}
