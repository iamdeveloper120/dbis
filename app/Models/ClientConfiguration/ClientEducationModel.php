<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientEducationModel extends Model
{
    protected $table            = 'client_education';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'client_id',
        'educational_setting',
        'school_name',
        'one_to_one_support',
        'school_type',
        'date_enrolled',
        'attendance_schedule',
        'home_program',
        'weekly_hours',
        'home_program_start_date',
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
