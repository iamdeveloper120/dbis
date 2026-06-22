<?php

namespace App\Models\ClientProblemBehavior;

use CodeIgniter\Model;

class DailySessionsPbRecordsModel extends Model
{
    protected $table = 'daily_sessions_pb_records';
    protected $allowedFields = [
        'pb_timer_id',
        'client_id',
        'session_id',
        'session_date',
        'behavior',
        'antecedent',
        'consequence',
        'abc_comments'
    ];
    protected $useTimestamps = true;

    // Method to get the complete record set with one-to-one relation
    public function getCompleteRecordSet($clientId, $sessionId = null)
    {
        $builder = $this->db->table('daily_sessions_pb_duration d');
        $builder->select('d.id as duration_id, d.session_id, d.session_date, d.client_id, d.start_time, d.end_time, 
                          r.id as record_id, r.pb_timer_id, r.antecedent, r.behavior, r.consequence,r.abc_comments,
                          r.created_at, r.updated_at');
        $builder->join('daily_sessions_pb_records r', 'd.id = r.pb_timer_id', 'left');
        $builder->where('d.client_id', $clientId);

        if ($sessionId) {
            $builder->where('d.session_id', $sessionId);
        }

        $builder->orderBy('d.session_date', 'DESC');
        return $builder->get()->getResultArray();
    }
    public function getSingleCompleteRecordSet($duration_id)
    {
        $builder = $this->db->table('daily_sessions_pb_duration d');
        $builder->select('d.id as duration_id, d.session_id, d.session_date, d.client_id, d.start_time, d.end_time, 
                          r.id as record_id, r.pb_timer_id, r.antecedent, r.behavior, r.consequence, r.abc_comments,
                          r.created_at, r.updated_at');
        $builder->join('daily_sessions_pb_records r', 'd.id = r.pb_timer_id', 'left');
        $builder->where('d.id', $duration_id);

        return $builder->get()->getRowArray();
    }
}
