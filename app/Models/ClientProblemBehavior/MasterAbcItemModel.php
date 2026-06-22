<?php

namespace App\Models\ClientProblemBehavior;

use CodeIgniter\Model;

class MasterAbcItemModel extends Model
{
    protected $table = 'dropdown_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['category', 'value', 'created_at'];
    protected $useTimestamps = false;
}
