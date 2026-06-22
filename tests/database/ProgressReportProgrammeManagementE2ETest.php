<?php

use App\Services\Reports\ProgressReportService;
use App\Models\Reports\ProgressReportVersionDataModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ProgressReportProgrammeManagementE2ETest extends CIUnitTestCase
{
    public function testPullCurrentProgrammeManagementPersistsComputedPmData(): void
    {
        $db = Database::connect('default');
        $row = $db->table('report_version rv')
            ->select([
                'rv.id AS version_id',
                'prvd.id AS prvd_id',
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
            $this->markTestSkipped('No unlocked DRAFT progress version found for E2E PM pull test.');
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
            $result = $service->pullSectionData($versionId, 'current_programme_management', null);

            $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'pullSectionData failed.'));

            $sectionData = $result['data']['section_data'] ?? [];
            $this->assertIsArray($sectionData);
            $this->assertArrayHasKey('pm.sessions_count', $sectionData);
            $this->assertArrayHasKey('pm.hours_of_instruction', $sectionData);
            $this->assertArrayHasKey('pm.dti_net_ratio', $sectionData);
            $this->assertArrayHasKey('pm.schedule_of_reinforcement', $sectionData);
            $this->assertArrayHasKey('pm.current_programmes', $sectionData);

            $joinedValues = implode(' | ', array_map(static fn($v) => (string) $v, $sectionData));
            $this->assertStringNotContainsString('Dummy', $joinedValues);

            $sessionsCount = (string) $sectionData['pm.sessions_count'];
            $this->assertMatchesRegularExpression('/^\\d+$|^N\\/A$/', $sessionsCount);

            $hours = (string) $sectionData['pm.hours_of_instruction'];
            $this->assertMatchesRegularExpression('/^\\d+\\.\\d hours$|^N\\/A$/', $hours);

            $ratio = (string) $sectionData['pm.dti_net_ratio'];
            $this->assertMatchesRegularExpression('/^\\d+% vs \\d+%$|^N\\/A$/', $ratio);

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
                $manual['pulled_sections']['current_programme_management']['data'] ?? null
            );
            $this->assertSame(
                $sectionData,
                $snapshot['sections']['current_programme_management']['data'] ?? null
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
