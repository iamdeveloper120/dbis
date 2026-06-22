<?php

namespace App\Entities\Mands;

use CodeIgniter\Entity\Entity;

class ClientMandsReinforcer extends Entity
{
    protected $datamap = [];
    protected $dates = ['introduced_at', 'created_at', 'updated_at'];
    protected $casts = [];
}

