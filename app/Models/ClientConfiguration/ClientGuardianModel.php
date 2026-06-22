<?php

namespace App\Models\ClientConfiguration;

use CodeIgniter\Model;

class ClientGuardianModel extends Model
{
    protected $table            = 'client_guardians';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'client_id',
        'name',
        'address',
        'telephone',
        'email',
    ];

    // has created_at + updated_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
