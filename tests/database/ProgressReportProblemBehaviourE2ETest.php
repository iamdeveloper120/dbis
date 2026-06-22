<?php

use App\Models\ClientGraphs\DailyDataGraphsModel;
use App\Models\Reports\ProgressReportVersionDataModel;
use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ProgressReportProblemBehaviourE2ETest extends CIUnitTestCase
{
    public function testPullProblemBehaviourPersistsRealDailyDataBasedGraphs(): void
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
            $this->markTestSkipped('No unlocked DRAFT progress version found for problem behaviour pull test.');
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
            $result = $service->pullSectionData($versionId, 'problem_behaviour_reduction', null);

            $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'pullSectionData failed.'));

            $sectionData = $result['data']['section_data'] ?? [];
            $this->assertIsArray($sectionData);
            $this->assertArrayHasKey('problem_behaviour.graphs', $sectionData);
            $graphs = $sectionData['problem_behaviour.graphs'];
            $this->assertIsArray($graphs);

            $json = json_encode($sectionData);
            $this->assertIsString($json);
            $this->assertStringNotContainsString('Dummy', $json);

            $graphKeys = [];
            foreach ($graphs as $graph) {
                $this->assertIsArray($graph);
                $this->assertArrayHasKey('key', $graph);
                $this->assertArrayHasKey('title', $graph);
                $this->assertArrayHasKey('graph', $graph);
                $graphKeys[] = (string) $graph['key'];

                $payload = $graph['graph'] ?? null;
                $this->assertIsArray($payload);
                $this->assertSame('line', (string) ($payload['chart_type'] ?? ''));
                $this->assertIsArray($payload['labels'] ?? null);
                $this->assertIsArray($payload['datasets'] ?? null);
                $this->assertArrayHasKey('options', $payload);
            }

            foreach ($graphKeys as $key) {
                $this->assertContains($key, ['pb_frequency', 'pb_duration']);
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
                $manual['pulled_sections']['problem_behaviour_reduction']['data'] ?? null
            );
            $this->assertSame(
                $sectionData,
                $snapshot['sections']['problem_behaviour_reduction']['data'] ?? null
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
        $prvdModelDb = new ReflectionProperty($prvdModel, 'db');
        $prvdModelDb->setAccessible(true);
        $prvdModelDb->setValue($prvdModel, $db);

        $dailyDataModel = new DailyDataGraphsModel();
        $dailyDataModelDb = new ReflectionProperty($dailyDataModel, 'db');
        $dailyDataModelDb->setAccessible(true);
        $dailyDataModelDb->setValue($dailyDataModel, $db);

        $servicePrvdModel = new ReflectionProperty($service, 'progressReportVersionDataModel');
        $servicePrvdModel->setAccessible(true);
        $servicePrvdModel->setValue($service, $prvdModel);

        $serviceDailyDataModel = new ReflectionProperty($service, 'dailyDataGraphsModel');
        $serviceDailyDataModel->setAccessible(true);
        $serviceDailyDataModel->setValue($service, $dailyDataModel);
    }
}
