<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;


class TargetMonthModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'client_graph_target_month';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['t_date', 'client_id', 'graph_type', 'created_by', 'created_at', 'updated_by', 'updated_at'];

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


    public function list($client_id)
    {

        $builder = $this->db->table('client_graph_target_month');
        $builder->select('id, client_id,t_date,graph_type,created_by,created_at,updated_by,updated_at');
        $builder->where('client_id', $client_id);

        $builder->orderBy('graph_type', 'ASC');
        $builder->orderBy('t_date', 'Desc');

        $result = $builder->get()->getResult();

        foreach ($result as $row) {
            $date = new \DateTime($row->t_date);
            $t_date = $date->format('M-Y');
            $row->date = $t_date;
        }
        return $result;
    }

    public function single($id)
    {
        $builder = $this->db->table('client_graph_target_month');
        $builder->select('*');
        $builder->where('id', $id);
        $query = $builder->get();
        $row = $query->getRow(0);
        if (isset($row)) {
            $date = new \DateTime($row->t_date);
            $t_date = $date->format('M-Y');
            $row->date = $t_date;
        }
        return $row;
    }
}
