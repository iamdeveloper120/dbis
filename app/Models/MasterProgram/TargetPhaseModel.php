<?php

namespace App\Models\MasterProgram;

use CodeIgniter\Model;

class TargetPhaseModel extends Model
{
    protected $table = 'target_phases';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'];
}
