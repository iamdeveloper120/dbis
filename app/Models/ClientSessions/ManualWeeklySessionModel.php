<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;
use App\Entities\ClientSessions\DailySession;


class ManualWeeklySessionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'daily_session_manual_weekly';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = DailySession::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['week_date', 'client_id', 'supervisor_id', 'hours', 'skills_retained', 'doi', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
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

    public function list($client_id, $start_date, $end_date)
    {
        $builder = $this->db->table('daily_session_manual_weekly');
        $builder->select('daily_session_manual_weekly.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_session_manual_weekly.supervisor_id', 'left');
        $builder->join('clients', 'clients.id = daily_session_manual_weekly.client_id ', 'left');
        $builder->where('daily_session_manual_weekly.client_id', $client_id);
        if ($start_date !== NULL && $end_date !== NULL) {
            $builder->where('daily_session_manual_weekly.week_date >= ', $start_date);
            $builder->where('daily_session_manual_weekly.week_date <=', $end_date);
        }
        $builder->orderBy('daily_session_manual_weekly.week_date', 'DESC');

        $result = $builder->get()->getResult(DailySession::class);

        foreach ($result as $row) {
            $row->supervisor_name = $row->supervisor_name();
            $row->is_session = $row->is_session();
        }

        return $result;
    }

    public function single($id)
    {

        $builder = $this->db->table('daily_session_manual_weekly');
        $builder->select('daily_session_manual_weekly.*, u1.first_name as supervisor_first_name, u1.last_name as supervisor_last_name, clients.mrn,clients.internal_mrn,clients.first_name as client_first_name, clients.last_name as client_last_name');
        $builder->join('users u1', 'u1.id = daily_session_manual_weekly.supervisor_id', 'left');
        $builder->join('clients', 'clients.id = daily_session_manual_weekly.client_id ', 'left');
        $builder->where('daily_session_manual_weekly.id', $id); 
        
        $row = $builder->get()->getRow();
        if ($row) {
            // Manually cast the result into the DailySession entity
            $session = new DailySession();
            // Map the result to the DailySession entity fields
            $session->fill((array)$row);            
            $session->supervisor_name = $session->supervisor_name(); 
            $session->is_session = $session->is_session(); 
            return $session;
        }

        return null;
 
    }
}
