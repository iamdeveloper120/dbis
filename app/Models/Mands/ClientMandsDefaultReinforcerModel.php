<?php

namespace App\Models\Mands;

use CodeIgniter\Model;

class ClientMandsDefaultReinforcerModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'client_mands_default_reinforcers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['client_id', 'name', 'order', 'created_by', 'created_at', 'updated_by', 'updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
