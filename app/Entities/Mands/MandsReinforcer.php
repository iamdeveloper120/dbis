<?php

namespace App\Entities\Mands;

use CodeIgniter\Entity\Entity;

class MandsReinforcer extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];
}
