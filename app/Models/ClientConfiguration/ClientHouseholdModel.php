<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientHouseholdModel extends Model
{
    protected $table            = 'client_household_members';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'client_id',
        'name',
        'age',
        'relationship',
    ];

    // only created_at exists in this table
    protected $useTimestamps    = false;
}
