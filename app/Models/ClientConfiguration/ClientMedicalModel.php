<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientMedicalModel extends Model
{
    protected $table            = 'client_medical_info';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'client_id',
        'prescribing_doctor',
        'previous_medications',
        'medical_conditions',
        'allergies',
        'current_medical_provider',
        'sleeping_habits',
        'eating_habits',
    ];

    // has created_at + updated_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Upsert by client_id (1:1 table)
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
