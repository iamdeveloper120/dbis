<?php

namespace App\Entities\ClientConfiguration;

use CodeIgniter\Entity\Entity;

class ClientPermission extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
