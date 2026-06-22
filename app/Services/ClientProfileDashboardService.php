<?php

namespace App\Services;

use App\Models\ClientConfiguration\ClientEffectiveTeachingProcedureModel;
use App\Models\ClientDataSheet\ClientLiveDailyDataModel;
use App\Models\ClientProgram\ClientProgramModel;
use App\Models\ClientSessions\DailySessionModel;

class ClientProfileDashboardService
{
    public function build(int $clientId, $client, $supervisor, array $tutors = [], array $dashboardWidgets = []): array
    {
        helper('custom');
        $weekRange = week_start_end_dates(date('Y-m-d'));
        $dashboardMetricTotals = $this->getDashboardMetricTotals(
            $clientId,
            (string) ($weekRange['week_start'] ?? date('Y-m-d')),
            (string) ($weekRange['week_end'] ?? date('Y-m-d'))
        );
        $activeProgramData = $this->getActiveProgramData($clientId);
        $activeTargets = (int) ($activeProgramData['program_summary']['total_targets_active'] ?? 0);

        $sessionSummary = [
            'poor' => 0,
            'good' => 0,
            'excellent' => 0,
        ];
        if ($this->isWidgetEnabled($dashboardWidgets, 'sessionQuality')) {
            $sessionSummary = $this->buildSessionSummary($this->getExecutedSessions($clientId));
        }

        $sessionOverview = $this->isWidgetEnabled($dashboardWidgets, 'sessionOverview')
            ? $this->getRecentInstructorComments($clientId)
            : [];
        $wowMoments = $this->isWidgetEnabled($dashboardWidgets, 'wowMoments')
            ? $this->getRecentWowComments($clientId)
            : [];
        $keyInformation = $this->isWidgetEnabled($dashboardWidgets, 'keyInformation')
            ? $this->getKeyInformation($clientId)
            : [];

        $summaryMetrics = [
            ['label' => 'Hours Delivered', 'value' => $this->formatMetricValue($dashboardMetricTotals['hours_delivered'], true), 'period' => 'This week', 'icon' => 'ri-time-line', 'color' => 'primary'],
            ['label' => 'Targets Mastered', 'value' => $this->formatMetricValue($dashboardMetricTotals['targets_mastered']), 'period' => 'This week', 'icon' => 'ri-star-line', 'color' => 'success'],
            ['label' => 'Developing Independence', 'value' => $this->formatMetricValue($dashboardMetricTotals['developing_independence']), 'period' => 'This week', 'icon' => 'ri-line-chart-line', 'color' => 'secondary'],
            ['label' => 'Active Targets', 'value' => (string) $activeTargets, 'period' => 'Current', 'icon' => 'ri-clipboard-line', 'color' => 'warning'],
            ['label' => 'Mand Frequency', 'value' => $this->formatMetricValue($dashboardMetricTotals['mand_frequency']), 'period' => 'This week', 'icon' => 'ri-message-2-line', 'color' => 'info'],
            ['label' => 'Behaviour Incidents', 'value' => $this->formatMetricValue($dashboardMetricTotals['behaviour_incidents']), 'period' => 'This week', 'icon' => 'ri-error-warning-line', 'color' => 'danger'],
        ];

        $sessionQualityChart = [
            'labels' => ['Poor', 'Good', 'Excellent'],
            'series' => [
                (int) $sessionSummary['poor'],
                (int) $sessionSummary['good'],
                (int) $sessionSummary['excellent'],
            ],
        ];

        return [
            'summaryMetrics' => $summaryMetrics,
            'sessionOverview' => $sessionOverview,
            'wowMoments' => $wowMoments,
            'sessionQualityChart' => $sessionQualityChart,
            'keyInformation' => $keyInformation,
            'activeProgramData' => $this->isWidgetEnabled($dashboardWidgets, 'activeTargets') ? $activeProgramData : null,
        ];
    }

    private function getDashboardMetricTotals(int $clientId, string $weekStart, string $weekEnd): array
    {
        $liveDailyDataModel = new ClientLiveDailyDataModel();
        $row = $liveDailyDataModel->getClientDashboardWeeklyMetrics($clientId, $weekStart, $weekEnd);

        return [
            'hours_delivered' => (float) ($row['total_hours_delivered'] ?? 0),
            'targets_mastered' => (float) ($row['total_targets_mastered'] ?? 0),
            'developing_independence' => (float) ($row['total_developing_independence'] ?? 0),
            'mand_frequency' => (float) ($row['total_mand_frequency'] ?? 0),
            'behaviour_incidents' => (float) ($row['total_behaviour_incidents'] ?? 0),
        ];
    }

    private function getActiveProgramData(int $clientId): array
    {
        $programModel = new ClientProgramModel();

        return $programModel->getSelectedClientActiveProgram($clientId);
    }

    private function getKeyInformation(int $clientId): array
    {
        $effectiveTeachingProcedureModel = new ClientEffectiveTeachingProcedureModel();

        return $effectiveTeachingProcedureModel->where('client_id', $clientId)->first() ?? [];
    }

    private function isWidgetEnabled(array $dashboardWidgets, string $widget): bool
    {
        return array_key_exists($widget, $dashboardWidgets) ? (bool) $dashboardWidgets[$widget] : true;
    }

    private function formatMetricValue(float $value, bool $allowDecimals = false): string
    {
        if (!$allowDecimals) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function getExecutedSessions(int $clientId): array
    {
        $sessionModel = new DailySessionModel();

        return $sessionModel->get_client_executed_sessions($clientId, null, null, null, null, null, null);
    }

    private function getRecentInstructorComments(int $clientId, int $limit = 5): array
    {
        $sessionModel = new DailySessionModel();
        $rows = $sessionModel->get_recent_instructor_comments($clientId, $limit);

        usort($rows, static function (array $a, array $b): int {
            if ((string) $a['session_date'] === (string) $b['session_date']) {
                return ((int) $a['id']) <=> ((int) $b['id']);
            }

            return strcmp((string) $a['session_date'], (string) $b['session_date']);
        });

        return array_map(static function (array $row): array {
            return [
                'date' => app_date((string) $row['session_date']),
                'note' => trim((string) ($row['instructor_comments'] ?? '')),
            ];
        }, $rows);
    }

    private function getRecentWowComments(int $clientId, int $limit = 5): array
    {
        $sessionModel = new DailySessionModel();
        $rows = $sessionModel->get_recent_wow_comments($clientId, $limit);

        usort($rows, static function (array $a, array $b): int {
            if ((string) $a['session_date'] === (string) $b['session_date']) {
                return ((int) $a['id']) <=> ((int) $b['id']);
            }

            return strcmp((string) $a['session_date'], (string) $b['session_date']);
        });

        return array_map(static function (array $row): array {
            return [
                'date' => app_date((string) $row['session_date']),
                'note' => trim((string) ($row['comments'] ?? '')),
            ];
        }, $rows);
    }

    private function buildSessionSummary(array $sessions): array
    {
        $summary = [
            'in_progress' => 0,
            'in_review' => 0,
            'processed' => 0,
            'partial_processed' => 0,
            'poor' => 0,
            'good' => 0,
            'excellent' => 0,
            'wow_moments_sessions' => 0,
            'total' => count($sessions),
        ];

        foreach ($sessions as $session) {
            $status = (int) ($session['status'] ?? 0);
            $rating = (int) ($session['session_rating'] ?? 0);

            if ($status === 1) {
                $summary['in_progress']++;
            }

            if ($status === 2) {
                $summary['in_review']++;
            }

            if ($status === 3) {
                $summary['processed']++;
            }

            if ($status === 4) {
                $summary['partial_processed']++;
            }

            if ($rating === 1) {
                $summary['poor']++;
            }

            if ($rating === 2) {
                $summary['good']++;
            }

            if ($rating === 3) {
                $summary['excellent']++;
            }

            if (trim((string) ($session['comments'] ?? '')) !== '') {
                $summary['wow_moments_sessions']++;
            }
        }

        return $summary;
    }
}
