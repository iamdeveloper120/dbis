<?php

use App\Libraries\Reports\HtmlTemplateRenderer;
use App\Services\Reports\DailyReportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DailyReportFormattingTest extends CIUnitTestCase
{
    public function testBuildNetVsDtiTextUsesPercentagesOverTotalSession(): void
    {
        $service = new DailyReportService();
        $method = new ReflectionMethod($service, 'buildNetVsDtiText');
        $method->setAccessible(true);

        $text = $method->invoke($service, [
            'total_session_seconds' => 3600,
            'net_seconds' => 900,
            'dti_seconds' => 1800,
            'net_percentage' => 25.0,
            'dti_percentage' => 50.0,
        ]);

        $this->assertSame('25% vs 75%', $text);
    }

    public function testBuildNetVsDtiTextReturnsNaWhenTotalSessionMissing(): void
    {
        $service = new DailyReportService();
        $method = new ReflectionMethod($service, 'buildNetVsDtiText');
        $method->setAccessible(true);

        $text = $method->invoke($service, [
            'total_session_seconds' => 0,
            'net_seconds' => 0,
            'dti_seconds' => 0,
            'net_percentage' => null,
            'dti_percentage' => null,
        ]);

        $this->assertSame('N/A', $text);
    }

    public function testRendererRemovesWowSectionWhenTokenIsNa(): void
    {
        $templatePath = tempnam(sys_get_temp_dir(), 'daily-report-template-');
        $html = '<div class="comment-section"><h2 class="mt-10">Wow Moments</h2><div class="comment-box">{{wow_moments}}</div></div>';
        file_put_contents($templatePath, $html);

        $renderer = new HtmlTemplateRenderer();
        $rendered = $renderer->render($templatePath, ['wow_moments' => 'N/A']);

        @unlink($templatePath);

        $this->assertStringNotContainsString('Wow Moments', $rendered);
        $this->assertStringNotContainsString('comment-box', $rendered);
    }
}
