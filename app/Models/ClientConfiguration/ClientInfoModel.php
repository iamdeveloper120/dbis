<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientInfoModel extends Model
{
    protected $table            = 'client_information';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id',
        'date_of_birth',
        'address',
        'primary_diagnosis',
        'date_primary_diagnosis',
        'age_primary_diagnosis',
        'secondary_diagnosis',
        'date_secondary_diagnosis',
        'age_secondary_diagnosis',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Insert or update record by client_id (1:1 relation)
     */
    public function upsertByClientId(int $clientId, array $data): bool
    {
        $existing = $this->where('client_id', $clientId)->first();
        $data['client_id'] = $clientId;

        if ($existing) {
            return (bool) $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }
}
