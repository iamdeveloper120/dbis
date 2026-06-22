<?php

use App\Models\ClientSessions\DailySessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 */
#[RequiresPhpExtension('sqlite3')]
final class DailySessionStartConflictDeepTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private DailySessionModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->query('DROP TABLE IF EXISTS db_daily_sessions');
        $this->db->query(
            'CREATE TABLE db_daily_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_date TEXT NOT NULL,
                client_id INTEGER NOT NULL,
                instructor_id INTEGER NOT NULL,
                supervisor_id INTEGER NULL,
                start_time TEXT NOT NULL,
                end_time TEXT NULL,
                status INTEGER NULL
            )'
        );

        $this->model = new DailySessionModel();
        $this->setPrivateProperty($this->model, 'db', $this->db);
    }

    #[DataProvider('clientStartConflictScenarios')]
    public function testClientStartConflictScenarios(array $existingRows, int $clientId, string $sessionDate, string $startTime, bool $expected): void
    {
        foreach ($existingRows as $row) {
            $this->db->table('daily_sessions')->insert($row);
        }

        $actual = $this->model->hasClientStartTimeConflict($clientId, $sessionDate, $startTime);
        $this->assertSame($expected, $actual);
    }

    #[DataProvider('instructorStartConflictScenarios')]
    public function testInstructorStartConflictScenarios(array $existingRows, int $instructorId, string $sessionDate, string $startTime, bool $expected): void
    {
        foreach ($existingRows as $row) {
            $this->db->table('daily_sessions')->insert($row);
        }

        $actual = $this->model->hasInstructorStartTimeConflict($instructorId, $sessionDate, $startTime);
        $this->assertSame($expected, $actual);
    }

    public static function clientStartConflictScenarios(): array
    {
        $cases = [];
        $date = '2026-02-24';
        $clientId = 501;
        $otherClient = 999;
        $instructorId = 701;

        // 25 closed-session overlap conflicts
        for ($i = 0; $i < 25; $i++) {
            $startHour = 8 + ($i % 8); // 08..15
            $start = sprintf('%02d:00:00', $startHour);
            $end = sprintf('%02d:00:00', $startHour + 1);
            $probe = sprintf('%02d:30:00', $startHour);
            $cases['client_closed_conflict_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $clientId,
                    'instructor_id' => $instructorId,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => 2,
                ],
            ], $clientId, $date, $probe, true];
        }

        // 25 open-session conflicts (this is where current production logic is weak)
        for ($i = 0; $i < 25; $i++) {
            $startHour = 7 + ($i % 10); // 07..16
            $startMinute = ($i % 4) * 15;
            $existingStart = sprintf('%02d:%02d:00', $startHour, $startMinute);
            $probeMinute = ($startMinute + 10) % 60;
            $probeHour = $startHour + (($startMinute + 10) >= 60 ? 1 : 0);
            $probe = sprintf('%02d:%02d:00', $probeHour, $probeMinute);

            $cases['client_open_conflict_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $clientId,
                    'instructor_id' => $instructorId,
                    'start_time' => $existingStart,
                    'end_time' => null,
                    'status' => 1,
                ],
            ], $clientId, $date, $probe, true];
        }

        // 10 negative controls to verify filtering by client/date
        for ($i = 0; $i < 5; $i++) {
            $cases['client_negative_other_client_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $otherClient,
                    'instructor_id' => $instructorId,
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'status' => 2,
                ],
            ], $clientId, $date, '10:00:00', false];

            $cases['client_negative_other_date_' . $i] = [[
                [
                    'session_date' => '2026-02-23',
                    'client_id' => $clientId,
                    'instructor_id' => $instructorId,
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'status' => 2,
                ],
            ], $clientId, $date, '10:00:00', false];
        }

        return $cases;
    }

    public static function instructorStartConflictScenarios(): array
    {
        $cases = [];
        $date = '2026-02-24';
        $instructorId = 1701;
        $otherInstructor = 1999;
        $clientId = 601;

        // 25 closed-session overlap conflicts
        for ($i = 0; $i < 25; $i++) {
            $startHour = 8 + ($i % 8); // 08..15
            $start = sprintf('%02d:00:00', $startHour);
            $end = sprintf('%02d:00:00', $startHour + 1);
            $probe = sprintf('%02d:20:00', $startHour);
            $cases['instructor_closed_conflict_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $clientId + $i,
                    'instructor_id' => $instructorId,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => 2,
                ],
            ], $instructorId, $date, $probe, true];
        }

        // 25 open-session conflicts (this is where current production logic is weak)
        for ($i = 0; $i < 25; $i++) {
            $startHour = 7 + ($i % 10); // 07..16
            $startMinute = ($i % 4) * 15;
            $existingStart = sprintf('%02d:%02d:00', $startHour, $startMinute);
            $probeMinute = ($startMinute + 10) % 60;
            $probeHour = $startHour + (($startMinute + 10) >= 60 ? 1 : 0);
            $probe = sprintf('%02d:%02d:00', $probeHour, $probeMinute);

            $cases['instructor_open_conflict_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $clientId + $i,
                    'instructor_id' => $instructorId,
                    'start_time' => $existingStart,
                    'end_time' => null,
                    'status' => 1,
                ],
            ], $instructorId, $date, $probe, true];
        }

        // 10 negative controls to verify filtering by instructor/date
        for ($i = 0; $i < 5; $i++) {
            $cases['instructor_negative_other_instructor_' . $i] = [[
                [
                    'session_date' => $date,
                    'client_id' => $clientId + 100 + $i,
                    'instructor_id' => $otherInstructor,
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'status' => 2,
                ],
            ], $instructorId, $date, '10:00:00', false];

            $cases['instructor_negative_other_date_' . $i] = [[
                [
                    'session_date' => '2026-02-23',
                    'client_id' => $clientId + 200 + $i,
                    'instructor_id' => $instructorId,
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'status' => 2,
                ],
            ], $instructorId, $date, '10:00:00', false];
        }

        return $cases;
    }
}
