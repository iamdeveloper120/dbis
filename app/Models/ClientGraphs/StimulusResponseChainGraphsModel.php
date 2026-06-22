<?php

namespace App\Models\ClientGraphs;

use CodeIgniter\Model;

class StimulusResponseChainGraphsModel extends Model
{
    protected $DBGroup  = 'default';
    private const PROBE_TYPE = 'stimulus_program';
    private const CHAIN_METHODS = ['total_task', 'forward', 'backward'];

    public function getClientDomains(int $clientId)
    {
        $builder = $this->db->table('client_program_domains d');
        $builder->select('DISTINCT d.id, d.name, d.domain_code', false);
        $builder->join('client_program_goals g', 'g.domain_id = d.id AND g.client_id = d.client_id', 'inner');
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id AND cps.client_id = g.client_id AND cps.is_active = 1', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');
        $builder->where('d.client_id', $clientId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', self::PROBE_TYPE);
        $builder->where(
            "EXISTS (
                SELECT 1
                FROM client_program_targets t
                JOIN client_target_stimulus_chains ctsc ON ctsc.target_id = t.id
                JOIN daily_session_data_collection ddc
                    ON ddc.client_id = t.client_id
                    AND ddc.target_id = t.id
                    AND ddc.goal_id = t.goal_id
                    AND ddc.deleted_at IS NULL
                WHERE t.client_id = d.client_id
                  AND t.goal_id = g.id
                  AND ctsc.method IN ('total_task','forward','backward')
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.inputs.type')) = 'stimulus_program'
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.method')) = ctsc.method
            )",
            null,
            false
        );
        $builder->orderBy('d.domain_code', 'ASC');

        return $builder->get()->getResult();
    }

    public function getClientDomainGoals(int $clientId, int $domainId)
    {
        $builder = $this->db->table('client_program_goals g');
        $builder->select('DISTINCT g.id, g.name, g.goal_code', false);
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id AND cps.client_id = g.client_id AND cps.is_active = 1', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');
        $builder->where('g.client_id', $clientId);
        $builder->where('g.domain_id', $domainId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', self::PROBE_TYPE);
        $builder->where(
            "EXISTS (
                SELECT 1
                FROM client_program_targets t
                JOIN client_target_stimulus_chains ctsc ON ctsc.target_id = t.id
                JOIN daily_session_data_collection ddc
                    ON ddc.client_id = t.client_id
                    AND ddc.target_id = t.id
                    AND ddc.goal_id = t.goal_id
                    AND ddc.deleted_at IS NULL
                WHERE t.client_id = g.client_id
                  AND t.goal_id = g.id
                  AND ctsc.method IN ('total_task','forward','backward')
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.inputs.type')) = 'stimulus_program'
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.method')) = ctsc.method
            )",
            null,
            false
        );
        $builder->orderBy('g.goal_code', 'ASC');

        return $builder->get()->getResult();
    }

    public function getClientGoalTargets(int $clientId, int $goalId)
    {
        $builder = $this->db->table('client_program_targets t');
        $builder->select('DISTINCT t.id, t.name', false);
        $builder->join('client_program_goals g', 'g.id = t.goal_id', 'inner');
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id AND cps.client_id = g.client_id AND cps.is_active = 1', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');
        $builder->join('client_target_stimulus_chains ctsc', 'ctsc.target_id = t.id', 'inner');
        $builder->where('t.client_id', $clientId);
        $builder->where('t.goal_id', $goalId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', self::PROBE_TYPE);
        $builder->whereIn('ctsc.method', self::CHAIN_METHODS);
        $builder->where(
            "EXISTS (
                SELECT 1
                FROM daily_session_data_collection ddc
                WHERE ddc.client_id = t.client_id
                  AND ddc.target_id = t.id
                  AND ddc.goal_id = t.goal_id
                  AND ddc.deleted_at IS NULL
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.inputs.type')) = 'stimulus_program'
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.method')) = ctsc.method
            )",
            null,
            false
        );
        $builder->orderBy('t.name', 'ASC');

        return $builder->get()->getResult();
    }

    public function getGraphsData(int $clientId, int $domainId, int $goalId, ?int $targetId = null): array
    {
        $targets = $this->getGraphTargets($clientId, $domainId, $goalId, $targetId);
        $graphs = [];

        foreach ($targets as $target) {
            $graph = $this->buildTargetGraph($target, $clientId, $goalId);
            if (!empty($graph)) {
                $graphs[] = $graph;
            }
        }

        return [
            'targets' => $graphs,
        ];
    }

    private function getGraphTargets(int $clientId, int $domainId, int $goalId, ?int $targetId = null): array
    {
        $builder = $this->db->table('client_program_targets t');
        $builder->select('DISTINCT t.id as target_id, t.name as target_name, ctsc.method as chain_method, COALESCE(vtss.step_count, 0) as step_count', false);
        $builder->join('client_program_goals g', 'g.id = t.goal_id', 'inner');
        $builder->join('client_program_domains d', 'd.id = g.domain_id', 'inner');
        $builder->join('client_probe_set cps', 'cps.goal_id = g.id AND cps.client_id = t.client_id AND cps.is_active = 1', 'inner');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'inner');
        $builder->join('client_target_stimulus_chains ctsc', 'ctsc.target_id = t.id', 'inner');
        $builder->join('view_target_stimulus_step_summary vtss', 'vtss.target_id = t.id', 'left');
        $builder->where('t.client_id', $clientId);
        $builder->where('d.id', $domainId);
        $builder->where('g.id', $goalId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(tps.inputs, "$.type")) =', self::PROBE_TYPE);
        $builder->whereIn('ctsc.method', self::CHAIN_METHODS);
        $builder->where(
            "EXISTS (
                SELECT 1
                FROM daily_session_data_collection ddc
                WHERE ddc.client_id = t.client_id
                  AND ddc.target_id = t.id
                  AND ddc.goal_id = t.goal_id
                  AND ddc.deleted_at IS NULL
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.inputs.type')) = 'stimulus_program'
                  AND JSON_UNQUOTE(JSON_EXTRACT(ddc.collected_data, '$.method')) = ctsc.method
            )",
            null,
            false
        );

        if ($targetId !== null) {
            $builder->where('t.id', $targetId);
        }

        $builder->orderBy('t.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    private function buildTargetGraph(array $target, int $clientId, int $goalId): array
    {
        $method = $target['chain_method'] ?? '';
        if (!in_array($method, self::CHAIN_METHODS, true)) {
            return [];
        }

        $points = $this->getTargetCollectionPoints($clientId, (int) $target['target_id'], $goalId, $method);
        $baselinePoint = $this->getBaselinePoint($clientId, (int) $target['target_id'], $goalId, $method);
        if (!empty($baselinePoint)) {
            array_unshift($points, $baselinePoint);
        }
        if (empty($points)) {
            return [];
        }

        $labels = [];
        $values = [];
        $maxSteps = (int) ($target['step_count'] ?? 0);
        $maxValue = 0;

        foreach ($points as $point) {
            $labels[] = stringToDate($point['session_date'], CC_DATE_FORMAT);
            $values[] = $point['value'];
            if ((float) $point['value'] > $maxValue) {
                $maxValue = (float) $point['value'];
            }
            if ($point['total_steps'] > $maxSteps) {
                $maxSteps = $point['total_steps'];
            }
        }

        $graphType = $method === 'total_task' ? 'percentage' : 'steps';
        $labelText = $method === 'total_task' ? 'Percent Independent' : 'Mastered Steps';
        $yAxisLabel = $method === 'total_task' ? 'Percentage' : 'Number of Steps';
        $yAxisMax = $method === 'total_task' ? 100 : (int) max($maxSteps, (int) ceil($maxValue), 1);
        $yAxisStep = $method === 'total_task' ? 10 : 1;

        return [
            'target_id' => (int) $target['target_id'],
            'target_name' => $target['target_name'],
            'chain_method' => $method,
            'graph_type' => $graphType,
            'total_steps' => $maxSteps,
            'y_axis_min' => 0,
            'y_axis_max' => $yAxisMax,
            'y_axis_step' => $yAxisStep,
            'y_axis_label' => $yAxisLabel,
            'chart_data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $labelText,
                        'data' => $values,
                        'lineTension' => 0,
                        'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                        'borderColor' => 'rgba(32, 116, 186, 1)',
                        'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                        'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                        'pointBorderWidth' => 2,
                        'borderWidth' => 2,
                    ],
                ],
            ],
        ];
    }

    private function getTargetCollectionPoints(int $clientId, int $targetId, int $goalId, string $method): array
    {
        $builder = $this->db->table('daily_session_data_collection');
        $builder->select('id, session_date, collected_data');
        $builder->where('client_id', $clientId);
        $builder->where('target_id', $targetId);
        $builder->where('goal_id', $goalId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(collected_data, "$.inputs.type")) =', self::PROBE_TYPE);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(collected_data, "$.method")) =', $method);
        $builder->where('deleted_at', null);
        $builder->orderBy('session_date', 'ASC');
        $builder->orderBy('id', 'ASC');

        $rows = $builder->get()->getResultArray();
        $pointsByDate = [];

        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['collected_data'], true);
            if (!is_array($decoded)) {
                continue;
            }

            $value = null;
            $totalSteps = 0;

            if ($method === 'total_task') {
                $result = $decoded['result'][0] ?? null;
                if (is_numeric($result)) {
                    $value = round((float) $result, 2);
                }

                if ($value === null && isset($decoded['statistics']['total_IND'], $decoded['statistics']['total_steps'])) {
                    $ind = (float) $decoded['statistics']['total_IND'];
                    $steps = (float) $decoded['statistics']['total_steps'];
                    if ($steps > 0) {
                        $value = round(($ind / $steps) * 100, 2);
                    }
                }

                $totalSteps = (int) ($decoded['statistics']['total_steps'] ?? 0);
            } else {
                $masteredSteps = $decoded['statistics']['mastered_steps'] ?? null;
                if (is_numeric($masteredSteps)) {
                    $value = (int) $masteredSteps;
                }
                $totalSteps = (int) ($decoded['statistics']['total_steps'] ?? 0);

                if ($value === null) {
                    $resultPercent = $decoded['result'][0] ?? null;
                    if (is_numeric($resultPercent) && $totalSteps > 0) {
                        $value = (int) round(((float) $resultPercent / 100) * $totalSteps);
                    }
                }

                if ($value !== null) {
                    if ($value < 0) {
                        $value = 0;
                    }
                    if ($totalSteps > 0 && $value > $totalSteps) {
                        $value = $totalSteps;
                    }
                }
            }

            if ($value === null) {
                continue;
            }

            if ($method === 'total_task') {
                if ($value < 0) {
                    $value = 0;
                }
                if ($value > 100) {
                    $value = 100;
                }
            }

            $pointsByDate[$row['session_date']] = [
                'session_date' => $row['session_date'],
                'value' => $value,
                'total_steps' => $totalSteps,
            ];
        }

        return array_values($pointsByDate);
    }

    private function getBaselinePoint(int $clientId, int $targetId, int $goalId, string $method): array
    {
        $baselineCollection = $this->getBaselineCollection($clientId, $targetId, $goalId);
        if (empty($baselineCollection)) {
            return [];
        }

        $sessionDate = (string) ($baselineCollection['session_date'] ?? '');
        if ($sessionDate === '') {
            return [];
        }

        $decoded = json_decode((string) ($baselineCollection['collected_data'] ?? ''), true);
        if (!is_array($decoded)) {
            return [];
        }

        $totalSteps = $this->extractBaselineTotalSteps($decoded);
        $value = null;

        if ($method === 'total_task') {
            $value = $this->extractBaselineAveragePercentage($decoded);
        } else {
            $baselineCollectionId = isset($baselineCollection['id']) ? (int) $baselineCollection['id'] : 0;
            $value = $this->getBaselineMasteredSteps($clientId, $targetId, $baselineCollectionId);
        }

        if (!is_numeric($value)) {
            return [];
        }

        if ($method === 'total_task') {
            $value = round((float) $value, 2);
            if ($value < 0) {
                $value = 0;
            }
            if ($value > 100) {
                $value = 100;
            }
        } else {
            $value = (int) $value;
            if ($value < 0) {
                $value = 0;
            }
            if ($totalSteps > 0 && $value > $totalSteps) {
                $value = $totalSteps;
            }
        }

        return [
            'session_date' => $sessionDate,
            'value' => $value,
            'total_steps' => $totalSteps,
        ];
    }

    private function getBaselineCollection(int $clientId, int $targetId, int $goalId): array
    {
        $builder = $this->db->table('daily_session_data_collection');
        $builder->select('id, session_date, collected_data');
        $builder->where('client_id', $clientId);
        $builder->where('target_id', $targetId);
        $builder->where('goal_id', $goalId);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(collected_data, "$.inputs.type")) =', self::PROBE_TYPE);
        $builder->where('JSON_UNQUOTE(JSON_EXTRACT(collected_data, "$.method")) =', 'baseline');
        $builder->where('deleted_at', null);
        $builder->orderBy('session_date', 'ASC');
        $builder->orderBy('id', 'ASC');
        $row = $builder->get()->getRowArray();

        return is_array($row) ? $row : [];
    }

    private function extractBaselineAveragePercentage(array $decoded): ?float
    {
        $values = [];

        if (isset($decoded['result'])) {
            if (is_array($decoded['result'])) {
                foreach ($decoded['result'] as $result) {
                    if (is_numeric($result)) {
                        $values[] = (float) $result;
                    }
                }
            } elseif (is_numeric($decoded['result'])) {
                $values[] = (float) $decoded['result'];
            }
        }

        if (empty($values) && isset($decoded['statistics']) && is_array($decoded['statistics'])) {
            foreach ($decoded['statistics'] as $stat) {
                if (is_array($stat) && isset($stat['percentage']) && is_numeric($stat['percentage'])) {
                    $values[] = (float) $stat['percentage'];
                }
            }
        }

        if (empty($values) && isset($decoded['statistics']) && is_array($decoded['statistics'])) {
            $totalInd = $decoded['statistics']['total_IND'] ?? null;
            $totalSteps = $decoded['statistics']['total_steps'] ?? null;
            if (is_numeric($totalInd) && is_numeric($totalSteps) && (float) $totalSteps > 0) {
                return round(((float) $totalInd / (float) $totalSteps) * 100, 2);
            }
        }

        if (empty($values)) {
            return null;
        }

        return round(array_sum($values) / count($values), 2);
    }

    private function extractBaselineTotalSteps(array $decoded): int
    {
        if (isset($decoded['statistics']['total_steps']) && is_numeric($decoded['statistics']['total_steps'])) {
            return (int) $decoded['statistics']['total_steps'];
        }

        if (isset($decoded['statistics']) && is_array($decoded['statistics'])) {
            foreach ($decoded['statistics'] as $stat) {
                if (is_array($stat) && isset($stat['total_steps']) && is_numeric($stat['total_steps'])) {
                    return (int) $stat['total_steps'];
                }
            }
        }

        return 0;
    }

    private function getBaselineMasteredSteps(int $clientId, int $targetId, int $collectionId): int
    {
        $builder = $this->db->table('client_target_stimulus_step_mastery');
        $builder->select('COUNT(DISTINCT step_id) as total_mastered', false);
        $builder->where('client_id', $clientId);
        $builder->where('target_id', $targetId);
        $builder->where('method', 'baseline');

        if ($collectionId > 0) {
            $builder->where('collection_id', $collectionId);
        }

        $row = $builder->get()->getRowArray();
        if (empty($row)) {
            return 0;
        }

        return (int) ($row['total_mastered'] ?? 0);
    }
}
