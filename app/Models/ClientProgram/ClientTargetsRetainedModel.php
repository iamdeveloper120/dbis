<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientTargetsRetainedModel extends Model
{
    protected $table = 'client_program_targets_retained';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'processed_data_id',
        'collection_id',
        'session_id',
        'session_date',
        'client_id',
        'domain_id',
        'goal_id',
        'target_id',
        'client_probe_set_id',
        'created_at',
        'created_by'
    ];

    public function saveRetainedTarget($data)
    {
        $this->insert($data);
        return $this->getInsertID();  // Return the ID of the inserted record
    }

}
