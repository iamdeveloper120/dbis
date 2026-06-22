<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

/**
 * Model: client_target_stimulus_step_sessions
 */
class ClientStimulusStepSessionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'client_target_stimulus_step_sessions_data';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['client_id','domain_id','goal_id','target_id','probe_set_id', 'step_id', 'session_id', 'session_date','created_by'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
