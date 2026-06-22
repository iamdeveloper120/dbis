<?php

namespace App\Entities\ClientConfiguration;

use CodeIgniter\Entity\Entity;
 

class Client  extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


    /**
     * Returns the full name of the user.
     */
    public function name(): string
    {
       return trim(implode(' ', [$this->first_name, $this->last_name]));
       // return trim(implode(' ', [$this->first_name]));
    }

    
}
