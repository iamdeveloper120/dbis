<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientMedicationModel extends Model
{
    protected $table            = 'client_medications';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'client_id',
        'category',     // Medication | Supplement
        'name',
        'dosage',
        'frequency',
        'prescribed_for',
    ];

    // only created_at exists in this table
    protected $useTimestamps    = false;
}
