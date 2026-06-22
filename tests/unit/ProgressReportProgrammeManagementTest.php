<?php

use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProgressReportProgrammeManagementTest extends CIUnitTestCase
{
    public function testBuildNetVsDtiTextFromSummaryReturnsNaWhenPercentagesMissing(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildNetVsDtiTextFromSummary');
        $method->setAccessible(true);

        $text = $method->invoke($service, [
            'net_percentage' => null,
            'dti_percentage' => null,
        ]);

        $this->assertSame('N/A', $text);
    }

    public function testBuildNetVsDtiTextFromSummaryRoundsAndKeepsHundred(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildNetVsDtiTextFromSummary');
        $method->setAccessible(true);

        $text = $method->invoke($service, [
            'net_percentage' => 33.6,
            'dti_percentage' => 66.4,
        ]);

        $this->assertSame('34% vs 66%', $text);
    }

    public function testBuildNetVsDtiTextFromSummaryClampsOutOfRangeValues(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildNetVsDtiTextFromSummary');
        $method->setAccessible(true);

        $text = $method->invoke($service, [
            'net_percentage' => 150,
            'dti_percentage' => -50,
        ]);

        $this->assertSame('100% vs 0%', $text);
    }

    public function testFormatHoursFromSecondsHandlesZeroAndDecimalFormat(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'formatHoursFromSeconds');
        $method->setAccessible(true);

        $this->assertSame('0.0 hours', $method->invoke($service, 0));
        $this->assertSame('18.5 hours', $method->invoke($service, 66600));
        $this->assertSame('1.0 hours', $method->invoke($service, 3599));
        $this->assertSame('0.0 hours', $method->invoke($service, -300));
    }

    public function testCurrentProgrammesSummaryReturnsNoneWhenNoRows(): void
    {
        $service = new ProgressReportService();
        $this->injectFakeDb($service, []);

        $method = new ReflectionMethod($service, 'getCurrentProgrammesSummaryByEndDate');
        $method->setAccessible(true);

        $result = $method->invoke($service, 1001, '2026-02-28');

        $this->assertSame('None', $result);
    }

    public function testCurrentProgrammesSummaryGroupsDomainAndDeduplicatesGoals(): void
    {
        $service = new ProgressReportService();
        $rows = [
            [
                'domain_code' => 'D01',
                'domain_name' => 'Communication',
                'goal_code' => 'G01',
                'goal_name' => 'Requesting',
            ],
            [
                'domain_code' => 'D01',
                'domain_name' => 'Communication',
                'goal_code' => 'G02',
                'goal_name' => 'Labeling',
            ],
            [
                'domain_code' => 'D01',
                'domain_name' => 'Communication',
                'goal_code' => 'G01',
                'goal_name' => 'Requesting',
            ],
            [
                'domain_code' => 'D02',
                'domain_name' => 'Social',
                'goal_code' => 'G03',
                'goal_name' => 'Turn Taking',
            ],
        ];
        $this->injectFakeDb($service, $rows);

        $method = new ReflectionMethod($service, 'getCurrentProgrammesSummaryByEndDate');
        $method->setAccessible(true);

        $result = $method->invoke($service, 1001, '2026-02-28');
        $expected = "Communication: G01 - Requesting, G02 - Labeling\n"
            . 'Social: G03 - Turn Taking';

        $this->assertSame($expected, $result);
    }

    public function testCurrentProgrammesSummaryFallsBackToNaLabels(): void
    {
        $service = new ProgressReportService();
        $rows = [
            [
                'domain_code' => '',
                'domain_name' => '',
                'goal_code' => '',
                'goal_name' => '',
            ],
        ];
        $this->injectFakeDb($service, $rows);

        $method = new ReflectionMethod($service, 'getCurrentProgrammesSummaryByEndDate');
        $method->setAccessible(true);

        $result = $method->invoke($service, 1001, '2026-02-28');

        $this->assertSame('N/A: N/A', $result);
    }

    public function testBuildCurrentProgrammeManagementSectionDataReturnsNaForInvalidContext(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildCurrentProgrammeManagementSectionData');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'subject_id' => 0,
            'period_start' => '2026-02-30',
            'period_end' => '',
        ]);

        $this->assertSame([
            'pm.sessions_count' => 'N/A',
            'pm.hours_of_instruction' => 'N/A',
            'pm.dti_net_ratio' => 'N/A',
            'pm.schedule_of_reinforcement' => 'N/A',
            'pm.current_programmes' => 'N/A',
        ], $result);
    }

    public function testBuildProgressSectionDataReturnsNaForInvalidContext(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildProgressSectionData');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'subject_id' => 0,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->assertSame([
            'progress.program_start_date_text' => 'N/A',
            'progress.cumulative_all_time_graph' => null,
            'progress.cumulative_period_graph' => null,
        ], $result);
    }

    public function testMapCumulativeGraphPayloadKeepsPhaseLinesAndNoSessionDataset(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'mapCumulativeGraphPayload');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'graph_data' => [
                'labels' => ['01-Feb-2026', '08-Feb-2026'],
                'datasets' => [
                    ['label' => 'Skills Retained', 'data' => [10, 11]],
                    ['label' => 'Degrees Of Independence', 'data' => [8, 9]],
                    ['label' => 'No Session', 'data' => [null, 0]],
                ],
            ],
            'phaseline' => [
                ['type' => 'line', 'value' => '08-Feb-2026'],
            ],
            'table' => '<table>ignored</table>',
        ]);

        $this->assertIsArray($result);
        $this->assertSame('line', $result['chart_type']);
        $this->assertSame(['01-Feb-2026', '08-Feb-2026'], $result['labels']);
        $this->assertCount(3, $result['datasets']);
        $this->assertSame('No Session', $result['datasets'][2]['label']);
        $this->assertSame([['type' => 'line', 'value' => '08-Feb-2026']], $result['phaseline']);
        $this->assertArrayNotHasKey('table', $result);
    }

    public function testMapCumulativeGraphPayloadReturnsNullWhenGraphDataMissing(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'mapCumulativeGraphPayload');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, ['graph_data' => ['labels' => [], 'datasets' => []]]));
        $this->assertNull($method->invoke($service, ['graph_data' => ['labels' => ['01-Feb-2026'], 'datasets' => []]]));
        $this->assertNull($method->invoke($service, ['graph_data' => ['labels' => [], 'datasets' => [['label' => 'Skills']]]]));
        $this->assertNull($method->invoke($service, null));
    }

    public function testBuildInstructionalSectionDataReturnsEmptyForInvalidContext(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildInstructionalSectionData');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'subject_id' => 0,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ]);

        $this->assertSame(['instructional.domains' => []], $result);
    }

    public function testBuildCumulativeSkillsDoiGraphDataBuildsIndependentCumulativeSeries(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildCumulativeSkillsDoiGraphData');
        $method->setAccessible(true);

        $result = $method->invoke(
            $service,
            ['2026-02-07' => 2, '2026-02-14' => 1],
            ['2026-02-14' => 3]
        );

        $this->assertIsArray($result);
        $this->assertCount(2, $result['labels']);
        $this->assertCount(2, $result['datasets']);
        $this->assertSame('Skills Retained', $result['datasets'][0]['label']);
        $this->assertSame([2, 3], $result['datasets'][0]['data']);
        $this->assertSame('Degrees Of Independence', $result['datasets'][1]['label']);
        $this->assertSame([null, 3], $result['datasets'][1]['data']);
    }

    public function testBuildCumulativeSkillsDoiGraphDataReturnsNullWhenNoWeeklyData(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'buildCumulativeSkillsDoiGraphData');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, [], []));
    }

    private function injectFakeDb(ProgressReportService $service, array $rows): void
    {
        $property = new ReflectionProperty($service, 'db');
        $property->setAccessible(true);
        $property->setValue($service, new FakeProgressDb($rows));
    }
}

final class FakeProgressDb
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function query(string $sql, array $params = []): FakeProgressQueryResult
    {
        return new FakeProgressQueryResult($this->rows);
    }
}

final class FakeProgressQueryResult
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getResultArray(): array
    {
        return $this->rows;
    }
}
