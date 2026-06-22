<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class DailyReportQueryModel extends Model
{
    protected $table = 'report';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    private ?bool $hasDailyVersionDataTable = null;

    public function listBySubject(int $subjectId, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->db->table('report r');
        $select = [
            'r.period_key AS report_date',
            'r.id AS report_id',
            'r.latest_version_no',
            'r.subject_id',
            "CONCAT(c.first_name, ' ', c.last_name) AS learner_name",
            'c.internal_mrn',
            'rv.id AS version_id',
            'rv.generated_at',
            "CONCAT(gen.first_name, ' ', gen.last_name) AS generated_by_name",
            'pdf.id AS latest_artifact_id',
            'el.status AS email_status',
            'el.sent_at AS email_sent_at',
            "CONCAT(email_actor.first_name, ' ', email_actor.last_name) AS email_action_by_name",
        ];

        if ($this->dailyVersionDataTableExists()) {
            $select[] = 'COALESCE(drvd.workflow_status, "FINAL") AS latest_status';
        }

        $builder->select($select);
        $builder->join('clients c', 'c.id = r.subject_id', 'left');
        $builder->join('report_version rv', 'rv.report_id = r.id AND rv.version_no = r.latest_version_no', 'left');
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        if ($this->dailyVersionDataTableExists()) {
            $builder->join('daily_report_version_data drvd', 'drvd.report_version_id = rv.id', 'left');
        }
        $builder->join(
            '(SELECT a.report_version_id, MAX(a.id) AS max_artifact_id FROM report_artifact a WHERE a.artifact_type = "PDF" GROUP BY a.report_version_id) pdfmax',
            'pdfmax.report_version_id = rv.id',
            'left'
        );
        $builder->join('report_artifact pdf', 'pdf.id = pdfmax.max_artifact_id', 'left');
        $builder->join(
            '(SELECT t.report_version_id, t.status, t.sent_at, t.requested_by FROM report_email_log t INNER JOIN (SELECT report_version_id, MAX(id) AS last_id FROM report_email_log GROUP BY report_version_id) m ON m.last_id = t.id) el',
            'el.report_version_id = rv.id',
            'left'
        );
        $builder->join('users email_actor', 'email_actor.id = el.requested_by', 'left');
        $builder->where('r.report_type', 'DAILY');
        $builder->where('r.subject_type', 'LEARNER');
        $builder->where('r.subject_id', $subjectId);
        if ($startDate !== null && $startDate !== '') {
            $builder->where('r.period_key >=', $startDate);
        }
        if ($endDate !== null && $endDate !== '') {
            $builder->where('r.period_key <=', $endDate);
        }
        $builder->orderBy('r.period_key', 'DESC');
        $builder->orderBy('r.id', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function countSessionsForDate(int $subjectId, string $reportDate): int
    {
        return (int) $this->db->table('daily_sessions')
            ->where('client_id', $subjectId)
            ->where('DATE(session_date) = ' . $this->db->escape($reportDate), null, false)
            ->countAllResults();
    }

    public function getReportBySubjectAndDate(int $subjectId, string $reportDate): ?array
    {
        return $this->db->table('report')
            ->where('report_type', 'DAILY')
            ->where('subject_type', 'LEARNER')
            ->where('subject_id', $subjectId)
            ->where('period_key', $reportDate)
            ->get()
            ->getRowArray();
    }

    public function listVersionsByReportId(int $reportId): array
    {
        $builder = $this->db->table('report_version rv');
        $select = [
            'rv.id AS version_id',
            'rv.version_no',
            'rv.generated_at',
            'rv.generated_by',
            "CONCAT(gen.first_name, ' ', gen.last_name) AS generated_by_name",
            'ra.id AS artifact_id',
            'ra.file_name',
            'el.status AS email_status',
            'el.sent_at AS email_sent_at',
            'el.requested_by AS email_requested_by',
            "CONCAT(email_actor.first_name, ' ', email_actor.last_name) AS email_action_by_name",
        ];

        if ($this->dailyVersionDataTableExists()) {
            $select[] = 'COALESCE(drvd.workflow_status, "FINAL") AS status';
        }

        $builder->select($select);
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        if ($this->dailyVersionDataTableExists()) {
            $builder->join('daily_report_version_data drvd', 'drvd.report_version_id = rv.id', 'left');
        }
        $builder->join(
            '(SELECT a.report_version_id, MAX(a.id) AS max_artifact_id FROM report_artifact a WHERE a.artifact_type = "PDF" GROUP BY a.report_version_id) x',
            'x.report_version_id = rv.id',
            'left'
        );
        $builder->join('report_artifact ra', 'ra.id = x.max_artifact_id', 'left');
        $builder->join(
            '(SELECT t.report_version_id, t.status, t.sent_at, t.requested_by FROM report_email_log t INNER JOIN (SELECT report_version_id, MAX(id) AS last_id FROM report_email_log GROUP BY report_version_id) m ON m.last_id = t.id) el',
            'el.report_version_id = rv.id',
            'left'
        );
        $builder->join('users email_actor', 'email_actor.id = el.requested_by', 'left');
        $builder->where('rv.report_id', $reportId);
        $builder->orderBy('rv.version_no', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getLatestPdfArtifactByVersion(int $versionId): ?array
    {
        return $this->db->table('report_artifact')
            ->where('report_version_id', $versionId)
            ->where('artifact_type', 'PDF')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function getTutorNames(int $subjectId, string $reportDate): string
    {
        $rows = $this->db->table('daily_sessions ds')
            ->select('DISTINCT ds.instructor_id, i.first_name, i.last_name', false)
            ->join('users i', 'i.id = ds.instructor_id', 'left')
            ->where('ds.client_id', $subjectId)
            ->where('DATE(ds.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getResultArray();

        $names = [];
        foreach ($rows as $row) {
            $first = trim((string) ($row['first_name'] ?? ''));
            $last = trim((string) ($row['last_name'] ?? ''));

            if ($first === '' && $last === '') {
                continue;
            }

            if ($first !== '' && $last !== '' && strcasecmp($first, $last) === 0) {
                $name = $first;
            } else {
                $name = trim($first . ' ' . $last);
            }

            if ($name === '') {
                continue;
            }

            $names[strtolower($name)] = $name;
        }

        return implode(', ', array_values($names));
    }

    public function getDailySessionCommentsAndWow(int $subjectId, string $reportDate): array
    {
        return $this->db->table('daily_sessions ds')
            ->select([
                'ds.id',
                'ds.start_time',
                'ds.end_time',
                'ds.instructor_comments',
                'ds.comments',
            ])
            ->where('ds.client_id', $subjectId)
            ->where('DATE(ds.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->orderBy('ds.start_time', 'ASC')
            ->orderBy('ds.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getProcessedProgramProbeRows(int $subjectId, string $reportDate): array
    {
        $builder = $this->db->table('daily_session_data_collection dc');
        $builder->select([
            'dc.id',
            'dc.session_id',
            'dc.session_date',
            'dc.collected_data',
            'd.domain_code',
            'd.name AS domain_name',
            'g.goal_code',
            'g.name AS goal_name',
            't.name AS target_name',
            'tps.name AS probe_set_name',
            'tps.id AS master_probe_set_id',
        ]);
        $builder->join('daily_sessions ds', 'ds.id = dc.session_id', 'inner');
        $builder->join('client_program_domains d', 'd.id = dc.domain_id', 'inner');
        $builder->join('client_program_goals g', 'g.id = dc.goal_id', 'inner');
        $builder->join('client_program_targets t', 't.id = dc.target_id', 'inner');
        $builder->join('client_probe_set cps', 'cps.id = dc.client_probe_set_id', 'left');
        $builder->join('target_probe_sets tps', 'tps.id = cps.probe_set_id', 'left');

        $builder->where('dc.client_id', $subjectId);
        $builder->where('DATE(dc.session_date) = ' . $this->db->escape($reportDate), null, false);
        $builder->where('dc.is_processed', 1);
        $builder->where('ds.status', 3);

        $builder->orderBy("CAST(COALESCE(NULLIF(REGEXP_SUBSTR(d.domain_code, '[0-9]+'), ''), '0') AS UNSIGNED)", 'ASC', false);
        $builder->orderBy('d.domain_code', 'ASC');
        $builder->orderBy("CAST(COALESCE(NULLIF(REGEXP_SUBSTR(g.goal_code, '[0-9]+'), ''), '0') AS UNSIGNED)", 'ASC', false);
        $builder->orderBy('g.goal_code', 'ASC');
        $builder->orderBy('t.name', 'ASC');
        $builder->orderBy('dc.id', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getMandsSummaryByDate(int $subjectId, string $reportDate): ?array
    {
        return $this->db->table('view_mands_session_data_summary')
            ->select([
                'total_mands',
                'variety_of_mands',
            ])
            ->where('client_id', $subjectId)
            ->where('DATE(session_date) = ' . $this->db->escape($reportDate), null, false)
            ->orderBy('session_date', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function getProblemBehaviorSummaryByDate(int $subjectId, string $reportDate): array
    {
        $row = $this->db->table('daily_sessions_pb_duration d')
            ->select([
                'COUNT(d.id) AS frequency',
                'SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS total_duration_seconds',
            ], false)
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->get()
            ->getRowArray();

        return [
            'frequency' => (int) ($row['frequency'] ?? 0),
            'total_duration_seconds' => (int) ($row['total_duration_seconds'] ?? 0),
        ];
    }

    public function getNetVsDtiSummaryByDate(int $subjectId, string $reportDate): array
    {
        $totalSession = (int) ($this->db->table('daily_sessions ds')
            ->select('SUM(CASE WHEN ds.start_time IS NOT NULL AND ds.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, ds.start_time, ds.end_time) ELSE 0 END) AS seconds', false)
            ->where('ds.client_id', $subjectId)
            ->where('DATE(ds.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $teaching = (int) ($this->db->table('daily_sessions_teaching_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $mands = (int) ($this->db->table('daily_sessions_mands_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $pb = (int) ($this->db->table('daily_sessions_pb_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) = ' . $this->db->escape($reportDate), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $net = max(0, $mands);
        $dti = max(0, $teaching - $mands);
        $netPercentage = $teaching > 0 ? round(($net / $teaching) * 100, 2) : null;
        $dtiPercentage = $teaching > 0 ? round(($dti / $teaching) * 100, 2) : null;

        return [
            'total_session_seconds' => $totalSession,
            'teaching_seconds' => $teaching,
            'mands_seconds' => $mands,
            'pb_seconds' => $pb,
            'net_seconds' => $net,
            'dti_seconds' => $dti,
            'net_percentage' => $netPercentage,
            'dti_percentage' => $dtiPercentage,
        ];
    }

    private function dailyVersionDataTableExists(): bool
    {
        if ($this->hasDailyVersionDataTable === null) {
            $this->hasDailyVersionDataTable = $this->db->tableExists('daily_report_version_data');
        }

        return $this->hasDailyVersionDataTable;
    }
}
