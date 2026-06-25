<?php

namespace App\Models\Mands;

use CodeIgniter\Model;
use App\Entities\Mands\MandsSessionData;

class MandsSessionDataModel extends Model
{
    protected $table            = 'mands_session_data';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = MandsSessionData::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['session_date', 'session_id', 'client_id', 'reinforcer_input', 'utterance_input', 'is_peer_manding', 'is_eye_contact', 'prompt_level', 'mands_error', 'initial_attempt_input', 'initial_attempt', 'prompt_delay_input', 'prompt_delay', 'echoic_1_input', 'echoic_1', 'echoic_2_input', 'echoic_2', 'echoic_3_input', 'echoic_3', 'comparison_prompt_delay', 'comparison_echoic_trial', 'created_by', 'created_at', 'updated_by', 'updated_at'];

    protected bool $allowEmptyInserts = false;

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


    public function getDailyDataBySession($client_id, $session_id)
    {
        // Use query builder to fetch client program data

        $builder = $this->db->table('mands_session_data');
        $builder->select('*');
        $builder->where('mands_session_data.client_id', $client_id);
        $builder->where('mands_session_data.session_id', $session_id);
        $query = $builder->get();

        // Organize the data into a hierarchical structure
        $data = [];

        foreach ($query->getResult(\App\Entities\Mands\MandsSessionData::class) as $row) {
            $data[] = $row;
        }


        return $data;
    }
    public function getDailyData($client_id, $session_date)
    {
        // Use query builder to fetch client program data

        $builder = $this->db->table('mands_session_data');
        $builder->select('*');
        $builder->where('mands_session_data.client_id', $client_id);
        $builder->where('mands_session_data.session_date', $session_date);
        $query = $builder->get();

        // Organize the data into a hierarchical structure
        $data = [];

        foreach ($query->getResult(\App\Entities\Mands\MandsSessionData::class) as $row) {
            $data[] = $row;
        }


        return $data;
    }


    public function getSummaryData($clientId)
    {
        // Use query builder to fetch client program data
        $builder = $this->db->table('view_mands_session_data_summary');
        $builder->select('*');
        $builder->where('client_id', $clientId);
        $builder->orderBy('session_date', 'DESC');
        $queryData = $builder->get()->getResult();

        return $queryData;
    }

    public function getCurrentMandListData(int $clientId): array
    {
        unset($clientId);
        return [];
    }

    public function getTopReinforcerInputs(int $clientId)
    {
        // Priority 1: client-level fixed defaults (must be exactly 10 rows).
        $clientDefaults = $this->db->table('client_mands_default_reinforcers')
            ->select('name')
            ->where('client_id', $clientId)
            ->orderBy('order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (count($clientDefaults) === 10) {
            $defaultNames = array_map(static fn(array $row): string => (string) $row['name'], $clientDefaults);
            return array_values($defaultNames);
        }

        // Check if any entries exist for the given client_id
        $builder = $this->db->table('mands_session_data');
        $builder->where('client_id', $clientId);
        $totalEntries = $builder->countAllResults(false); // Retain the query builder state

        if ($totalEntries == 0) {
            return null; // Return null if no entries exist
        }

        // Query to get the most repeated reinforcer_inputs
        $builder->select('reinforcer_input, COUNT(reinforcer_input) as count')
            ->where('client_id', $clientId)
            ->groupBy('reinforcer_input')
            ->orderBy('count', 'DESC')
            ->orderBy('MAX(id)', 'DESC', false); // To handle tie cases, most recent first

        $repeatedResult = $builder->get()->getResultArray();

        $repeatedInputs = [];
        $remaining = 10;

        foreach ($repeatedResult as $row) {
            if ($row['count'] > 1 && $remaining > 0) {
                $repeatedInputs[] = $row['reinforcer_input'];
                $remaining--;
            }
        }

        if ($remaining > 0) {
            // Fetch the most recent unique inputs to fill up to 10 items
            $recentBuilder = $this->db->table('mands_session_data');
            $recentBuilder->select('reinforcer_input')
                ->where('client_id', $clientId)
                ->groupBy('reinforcer_input')
                ->orderBy('MAX(id)', 'DESC', false)
                ->limit($remaining);

            $recentResult = $recentBuilder->get()->getResultArray();

            foreach ($recentResult as $row) {
                if (!in_array($row['reinforcer_input'], $repeatedInputs)) {
                    $repeatedInputs[] = $row['reinforcer_input'];
                }
            }
        }

        return $repeatedInputs;
    }
}
