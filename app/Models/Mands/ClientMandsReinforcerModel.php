<?php

namespace App\Models\Mands;

use CodeIgniter\Model;
use App\Entities\Mands\ClientMandsReinforcer;

class ClientMandsReinforcerModel extends Model
{
    protected $table            = 'client_mands_reinforcer';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ClientMandsReinforcer::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_id',
        'reinforcer_name',
        'introduced_at',
        'vocal_sign',
        'description',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function listByClient(int $clientId): array
    {
        return $this->where('client_id', $clientId)
            ->orderBy('reinforcer_name', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}

