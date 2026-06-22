<?php

use App\Models\Reports\ProgressReportVersionDataModel;
use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ProgressReportInstructionalProgrammesE2ETest extends CIUnitTestCase
{
    public function testPullInstructionalProgrammesPersistsComputedDomainsGraphsAndGoals(): void
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
            $this->markTestSkipped('No unlocked DRAFT progress version found for instructional pull test.');
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
            $result = $service->pullSectionData($versionId, 'instructional_programmes', null);

            $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'pullSectionData failed.'));

            $sectionData = $result['data']['section_data'] ?? [];
            $this->assertIsArray($sectionData);
            $this->assertArrayHasKey('instructional.domains', $sectionData);
            $domains = $sectionData['instructional.domains'];
            $this->assertIsArray($domains);

            $json = json_encode($sectionData);
            $this->assertIsString($json);
            $this->assertStringNotContainsString('Dummy', $json);

            foreach ($domains as $domain) {
                $this->assertIsArray($domain);
                $this->assertArrayHasKey('key', $domain);
                $this->assertArrayHasKey('title', $domain);
                $this->assertArrayHasKey('period_graph', $domain);
                $this->assertArrayHasKey('goals', $domain);

                $this->assertNotSame('', trim((string) $domain['key']));
                $this->assertNotSame('', trim((string) $domain['title']));
                $this->assertIsArray($domain['goals']);

                foreach ($domain['goals'] as $goal) {
                    $this->assertIsArray($goal);
                    $this->assertArrayHasKey('goal_name', $goal);
                    $this->assertArrayHasKey('targets_mastered', $goal);
                    $this->assertNotSame('', trim((string) $goal['goal_name']));
                    $this->assertIsArray($goal['targets_mastered']);
                }

                $graph = $domain['period_graph'];
                if ($graph === null) {
                    continue;
                }

                $this->assertIsArray($graph);
                $this->assertSame('line', (string) ($graph['chart_type'] ?? ''));
                $this->assertIsArray($graph['labels'] ?? null);
                $this->assertIsArray($graph['datasets'] ?? null);
                $this->assertArrayHasKey('options', $graph);

                $datasetLabels = array_values(array_filter(array_map(
                    static fn($dataset) => is_array($dataset) ? (string) ($dataset['label'] ?? '') : '',
                    (array) ($graph['datasets'] ?? [])
                )));

                $this->assertContains('Skills Retained', $datasetLabels);
                $this->assertContains('Degrees Of Independence', $datasetLabels);
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
                $manual['pulled_sections']['instructional_programmes']['data'] ?? null
            );
            $this->assertSame(
                $sectionData,
                $snapshot['sections']['instructional_programmes']['data'] ?? null
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

