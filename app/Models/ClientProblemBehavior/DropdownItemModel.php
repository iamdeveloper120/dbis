<?php 

namespace App\Models\ClientProblemBehavior;

use CodeIgniter\Model;

class DropdownItemModel extends Model
{
    protected $table = 'dropdown_items';
    protected $allowedFields = ['category', 'value'];
    protected $useTimestamps = true;
}