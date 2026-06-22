<?php

namespace App\Models\Mands;

use CodeIgniter\Model;
use App\Entities\Mands\ClientMandsReinforcerMedia;

class ClientMandsReinforcerMediaModel extends Model
{
    protected $table            = 'client_mands_reinforcer_media';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ClientMandsReinforcerMedia::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_reinforcer_id',
        'media_type',
        'media_path',
        'created_by',
        'created_at',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = false;
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

    public function listByClientReinforcerIds(array $reinforcerIds): array
    {
        if (empty($reinforcerIds)) {
            return [];
        }

        return $this->whereIn('client_reinforcer_id', $reinforcerIds)
            ->orderBy('media_type', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}

