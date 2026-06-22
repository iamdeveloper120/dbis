<?php

use App\Models\ClientSessions\DailySessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @internal
 */
#[RequiresPhpExtension('sqlite3')]
final class DailySessionConflictModelTest extends CIUnitTestCase
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

    public function testClientStartTimeConflictDetectsOverlapWithClosedSession(): void
    {
        $this->db->table('daily_sessions')->insert([
            'session_date' => '2026-02-24',
            'client_id' => 10,
            'instructor_id' => 100,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 2,
        ]);

        $hasConflict = $this->model->hasClientStartTimeConflict(10, '2026-02-24', '09:30:00');

        $this->assertTrue($hasConflict);
    }

    public function testClientStartTimeConflictAllowsBoundaryStartAfterClosedSessionEnd(): void
    {
        $this->db->table('daily_sessions')->insert([
            'session_date' => '2026-02-24',
            'client_id' => 10,
            'instructor_id' => 100,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 2,
        ]);

        $hasConflict = $this->model->hasClientStartTimeConflict(10, '2026-02-24', '10:00:00');

        $this->assertFalse($hasConflict);
    }

    public function testClientStartTimeConflictDetectsOpenSessionWhenTimesDiffer(): void
    {
        $this->db->table('daily_sessions')->insert([
            'session_date' => '2026-02-24',
            'client_id' => 10,
            'instructor_id' => 100,
            'start_time' => '09:00:00',
            'end_time' => null,
            'status' => 1,
        ]);

        $hasConflict = $this->model->hasClientStartTimeConflict(10, '2026-02-24', '09:30:00');

        $this->assertTrue($hasConflict);
    }

    public function testInstructorStartTimeConflictDetectsOpenSessionWhenTimesDiffer(): void
    {
        $this->db->table('daily_sessions')->insert([
            'session_date' => '2026-02-24',
            'client_id' => 11,
            'instructor_id' => 100,
            'start_time' => '09:00:00',
            'end_time' => null,
            'status' => 1,
        ]);

        $hasConflict = $this->model->hasInstructorStartTimeConflict(100, '2026-02-24', '09:30:00');

        $this->assertTrue($hasConflict);
    }
}
