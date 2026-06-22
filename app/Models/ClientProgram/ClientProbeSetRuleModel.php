<?php

namespace App\Models\ClientProgram;

use CodeIgniter\Model;

class ClientProbeSetRuleModel extends Model
{
    protected $DBGroup  = 'default';
    protected $table = 'client_probe_rules';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_probe_set_id',
        'phase_id',
        'rules',
    ];

    public function getRulesByProbeSet($probeSetHistoryId)
    {
        return $this->where('client_probe_set_id', $probeSetHistoryId)->findAll();
    }

    public function saveRule($data)
    {
        if (isset($data['id'])) {
            $this->update($data['id'], $data);
        } else {
            $this->insert($data);
            return $this->getInsertID();
        }
    }
}
