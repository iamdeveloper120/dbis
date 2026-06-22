<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;

class StimulusStepSessionsDataModel extends Model
{
    protected $table      = 'client_target_stimulus_step_sessions_data';
    protected $primaryKey = 'id';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'collection_id',
        'client_id',
        'target_id',
        'step_id',
        'session_id',
        'session_date',
        'phase_id',
        'method',
        'attempt_no',
        'input_result',
        'is_mastered_snapshot',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $useTimestamps = false;

    protected $returnType    = 'array';

    public function deleteByCollectionIds(array $collectionIds)
    {
        if (empty($collectionIds)) return;

        $this->whereIn('collection_id', $collectionIds)->delete();
    }


}
