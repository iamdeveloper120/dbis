<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;
use App\Entities\ClientProgram\ClientDomain;

class ClientDomainModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table      = 'client_program_domains';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = ClientDomain::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['domain_code', 'name', 'description', 'client_id','mp_domain_id', 'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];

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
    public function listAll($client_id)
    {
        $builder = $this->db->table('client_program_domains');
        $builder->select('client_program_domains.*');
        $builder->where('client_program_domains.client_id', $client_id);
        $builder->orderBy('client_program_domains.name', 'ASC');
        $result = $builder->get()->getResult();

        return $result;
    }
    /************************************************************************ */
    public function single($id)
    {
        $builder = $this->db->table('client_program_domains');
        $builder->select('client_program_domains.*');
        $builder->where('client_program_domains.id', $id);
        $result = $builder->get()->getRow();
        return $result;
    }
    /************************************************************************ */
    public function isUsed($id)
    {
        $builder = $this->db->table('client_program_goals');
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
