<?php

use App\Controllers\Reports\ProgressReportController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProgressReportUrlStateTokenTest extends CIUnitTestCase
{
    public function testStateTokenRoundTripKeepsClientAndFilters(): void
    {
        $controller = new ProgressReportController();

        $buildQuery = new ReflectionMethod($controller, 'buildProgressListStateQueryFromState');
        $buildQuery->setAccessible(true);

        $decodeToken = new ReflectionMethod($controller, 'decodeProgressListStateToken');
        $decodeToken->setAccessible(true);

        $sanitize = new ReflectionMethod($controller, 'sanitizeProgressListState');
        $sanitize->setAccessible(true);

        $state = [
            'client_id' => '1',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
            'dt_page' => 3,
        ];

        $query = (string) $buildQuery->invoke($controller, $state);
        $this->assertStringStartsWith('?s=', $query);

        $token = (string) substr($query, 3);
        $decoded = $decodeToken->invoke($controller, urldecode($token));
        $this->assertIsArray($decoded);

        $normalized = $sanitize->invoke($controller, $decoded);
        $this->assertSame('1', (string) ($normalized['client_id'] ?? ''));
        $this->assertSame('2026-02-01', (string) ($normalized['start_date'] ?? ''));
        $this->assertSame('2026-02-28', (string) ($normalized['end_date'] ?? ''));
        $this->assertSame(3, (int) ($normalized['dt_page'] ?? -1));
    }

    public function testTamperedStateTokenIsRejected(): void
    {
        $controller = new ProgressReportController();

        $buildQuery = new ReflectionMethod($controller, 'buildProgressListStateQueryFromState');
        $buildQuery->setAccessible(true);

        $decodeToken = new ReflectionMethod($controller, 'decodeProgressListStateToken');
        $decodeToken->setAccessible(true);

        $query = (string) $buildQuery->invoke($controller, [
            'client_id' => '1',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
            'dt_page' => 1,
        ]);

        $token = (string) substr($query, 3);
        $tampered = $token . 'x';
        $decoded = $decodeToken->invoke($controller, urldecode($tampered));

        $this->assertNull($decoded);
    }
}

