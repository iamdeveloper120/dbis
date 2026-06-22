<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;


class PhaseLineModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'client_graph_phase_line';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['p_date', 'client_id', 'graph_type', 'p_key', 'created_by', 'created_at', 'updated_by', 'updated_at'];

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


    public function list($client_id, $type)
    {
        $builder = $this->db->table('client_graph_phase_line');
        $builder->select('*');
        $builder->where('graph_type', $type);
        $builder->where('client_id', $client_id);
        $builder->orderBy('p_date', 'desc');
        $result = $builder->get()->getResult();

        return $result;
    }

    public function single($id)
    {
        $builder = $this->db->table('client_graph_phase_line');
        $builder->select('*');
        $builder->where('id', $id);
        $query = $builder->get();
        $row = $query->getRow(0);
        return $row;
    }
}
