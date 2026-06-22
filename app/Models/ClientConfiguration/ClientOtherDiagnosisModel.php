<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientOtherDiagnosisModel extends Model
{
    protected $table            = 'client_other_diagnoses';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id',
        'diagnosis_name',
        'diagnosis_date',
        'diagnosis_age',
    ];

    // This table only has created_at (no updated_at)
    protected $useTimestamps    = false;

    /**
     * Delete and replace all diagnoses for a given client.
     */
    public function replaceClientDiagnoses(int $clientId, array $rows): void
    {
        $this->where('client_id', $clientId)->delete();

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (empty(trim($row['diagnosis_name'] ?? ''))) {
                continue;
            }

            $this->insert([
                'client_id'      => $clientId,
                'diagnosis_name' => $row['diagnosis_name'] ?? null,
                'diagnosis_date' => $row['diagnosis_date'] ?? null,
                'diagnosis_age'  => $row['diagnosis_age'] ?? null,
            ]);
        }
    }

    /**
     * Retrieve all diagnoses for a specific client.
     */
    public function getByClientId(int $clientId): array
    {
        return $this->where('client_id', $clientId)
                    ->orderBy('id', 'asc')
                    ->findAll();
    }
}
