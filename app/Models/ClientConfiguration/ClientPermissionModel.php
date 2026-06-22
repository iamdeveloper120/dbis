<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;
use App\Entities\ClientConfiguration\ClientPermission;

class ClientPermissionModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table      = 'client_user_mapping';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType       = ClientPermission::class;
    protected $useSoftDeletes = false;

    protected $allowedFields    = ['user_id', 'client_id', 'is_default'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
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
}
