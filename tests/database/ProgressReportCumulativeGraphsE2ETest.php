<?php

use App\Models\Reports\ProgressReportVersionDataModel;
use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ProgressReportCumulativeGraphsE2ETest extends CIUnitTestCase
{
    public function testPullProgressPersistsCumulativeGraphPayload(): void
    {
        $db = Database::connect('default');
        $row = $db->table('report_version rv')
            ->select([
                'rv.id AS version_id',
                'prvd.manual_json',
                'prvd.snapshot_json',
                'prvd.section_status_json',
                'prvd.updated_at',
                'prvd.updated_by',
            ])
            ->join('report r', 'r.id = rv.report_id', 'inner')
            ->join('progress_report_version_data prvd', 'prvd.report_version_id = rv.id', 'inner')
            ->where('r.report_type', 'PROGRESS')
            ->where('r.subject_type', 'LEARNER')
            ->where('prvd.workflow_status', 'DRAFT')
            ->where('prvd.is_locked', 0)
            ->orderBy('rv.id', 'DESC')
            ->get()
            ->getRowArray();

        if (!$row) {
            $this->markTestSkipped('No unlocked DRAFT progress version found for E2E progress graph pull test.');
        }

        $versionId = (int) $row['version_id'];
        $original = [
            'manual_json' => (string) ($row['manual_json'] ?? '{}'),
            'snapshot_json' => (string) ($row['snapshot_json'] ?? '{}'),
            'section_status_json' => (string) ($row['section_status_json'] ?? '{}'),
            'updated_at' => $row['updated_at'],
            'updated_by' => $row['updated_by'],
        ];

        try {
            $service = new ProgressReportService();
            $this->injectDefaultDbIntoProgressService($service, $db);
            $result = $service->pullSectionData($versionId, 'progress', null);

            $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'pullSectionData failed.'));

            $sectionData = $result['data']['section_data'] ?? [];
            $this->assertIsArray($sectionData);
            $this->assertArrayHasKey('progress.program_start_date_text', $sectionData);
            $this->assertArrayHasKey('progress.cumulative_all_time_graph', $sectionData);
            $this->assertArrayHasKey('progress.cumulative_period_graph', $sectionData);

            $programStartText = (string) ($sectionData['progress.program_start_date_text'] ?? '');
            $this->assertNotSame('', $programStartText);

            foreach (['progress.cumulative_all_time_graph', 'progress.cumulative_period_graph'] as $graphKey) {
                $graph = $sectionData[$graphKey];
                if ($graph === null) {
                    continue;
                }

                $this->assertIsArray($graph, $graphKey . ' must be array or null.');
                $this->assertSame('line', (string) ($graph['chart_type'] ?? ''));
                $this->assertIsArray($graph['labels'] ?? null);
                $this->assertIsArray($graph['datasets'] ?? null);
                $this->assertIsArray($graph['phaseline'] ?? null);
                $this->assertArrayNotHasKey('table', $graph);

                $labels = array_values(array_filter(array_map(
                    static fn($dataset) => is_array($dataset) ? (string) ($dataset['label'] ?? '') : '',
                    (array) ($graph['datasets'] ?? [])
                )));

                $this->assertContains('Skills Retained', $labels);
                $this->assertContains('Degrees Of Independence', $labels);
                $this->assertContains('No Session', $labels);
            }

            $after = $db->table('progress_report_version_data')
                ->select(['manual_json', 'snapshot_json', 'section_status_json'])
                ->where('report_version_id', $versionId)
                ->get()
                ->getRowArray();

            $this->assertIsArray($after);
            $manual = json_decode((string) ($after['manual_json'] ?? '{}'), true);
            $snapshot = json_decode((string) ($after['snapshot_json'] ?? '{}'), true);
            $this->assertIsArray($manual);
            $this->assertIsArray($snapshot);
            $this->assertSame(
                $sectionData,
                $manual['pulled_sections']['progress']['data'] ?? null
            );
            $this->assertSame(
                $sectionData,
                $snapshot['sections']['progress']['data'] ?? null
            );
        } finally {
            $db->table('progress_report_version_data')
                ->where('report_version_id', $versionId)
                ->update([
                    'manual_json' => $original['manual_json'],
                    'snapshot_json' => $original['snapshot_json'],
                    'section_status_json' => $original['section_status_json'],
                    'updated_at' => $original['updated_at'],
                    'updated_by' => $original['updated_by'],
                ]);
        }
    }

    private function injectDefaultDbIntoProgressService(ProgressReportService $service, $db): void
    {
        $serviceDb = new ReflectionProperty($service, 'db');
        $serviceDb->setAccessible(true);
        $serviceDb->setValue($service, $db);

        $prvdModel = new ProgressReportVersionDataModel();
        $modelDb = new ReflectionProperty($prvdModel, 'db');
        $modelDb->setAccessible(true);
        $modelDb->setValue($prvdModel, $db);

        $serviceModel = new ReflectionProperty($service, 'progressReportVersionDataModel');
        $serviceModel->setAccessible(true);
        $serviceModel->setValue($service, $prvdModel);
    }
}

