<?php

use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProgressReportFinalizeValidationTest extends CIUnitTestCase
{
    public function testFinalizeValidationFailsWhenRequiredSectionsAndFieldsAreMissing(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'validateFinalizeReadiness');
        $method->setAccessible(true);

        $result = $method->invoke($service, []);

        $this->assertIsArray($result);
        $this->assertFalse((bool) ($result['success'] ?? true));
        $this->assertSame('FINALIZE_VALIDATION_ERROR', (string) ($result['code'] ?? ''));
        $this->assertNotEmpty($result['data']['missing_requirements'] ?? []);
    }

    public function testFinalizeValidationPassesWhenAllRequirementsArePresent(): void
    {
        $service = new ProgressReportService();
        $method = new ReflectionMethod($service, 'validateFinalizeReadiness');
        $method->setAccessible(true);

        $manualData = [
            'approved_by' => 'Test Approver',
            'conclusion_comment' => 'Conclusion text',
            'pulled_sections' => [
                'current_programme_management' => ['data' => []],
                'progress' => ['data' => []],
                'instructional_programmes' => ['data' => []],
                'manding' => ['data' => []],
                'problem_behaviour_reduction' => ['data' => []],
            ],
        ];

        $result = $method->invoke($service, $manualData);

        $this->assertIsArray($result);
        $this->assertTrue((bool) ($result['success'] ?? false));
    }
}

