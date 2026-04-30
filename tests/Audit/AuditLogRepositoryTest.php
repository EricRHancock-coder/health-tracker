<?php

namespace Tests\Audit;

use PHPUnit\Framework\TestCase;
use App\Repositories\AuditLogRepository;
use RedBeanPHP\R;

class AuditLogRepositoryTest extends TestCase
{
    private AuditLogRepository $repository;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        R::exec("CREATE TABLE IF NOT EXISTS audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            table_name TEXT,
            record_id INTEGER,
            old_values TEXT,
            new_values TEXT,
            ip_address TEXT,
            timestamp DATETIME NOT NULL
        )");

        $this->repository = new AuditLogRepository();
    }

    protected function tearDown(): void
    {
        R::nuke();
    }

    public function testSavePersistsAuditLogBean(): void
    {
        $log = R::dispense('audit_log');
        $log->user_id = 1;
        $log->action = 'CREATE';
        $log->table_name = 'users';
        $log->record_id = 10;
        $log->new_values = json_encode(['name' => 'John']);
        $log->ip_address = '127.0.0.1';
        $log->timestamp = '2026-04-30 10:00:00';

        $id = $this->repository->save($log);

        $this->assertGreaterThan(0, $id);

        $stored = R::load('audit_log', $id);
        $this->assertEquals('CREATE', $stored->action);
        $this->assertEquals(1, (int) $stored->user_id);
        $this->assertEquals(10, (int) $stored->record_id);
        $this->assertEquals(json_encode(['name' => 'John']), $stored->new_values);
    }

    public function testGetRecentReturnsBeansNewestFirst(): void
    {
        $first = R::dispense('audit_log');
        $first->action = 'LOGIN';
        $first->user_id = 1;
        $first->timestamp = '2026-04-29 09:00:00';
        R::store($first);

        $second = R::dispense('audit_log');
        $second->action = 'FAILED_LOGIN';
        $second->timestamp = '2026-04-30 10:00:00';
        R::store($second);

        $results = $this->repository->getRecent(10);

        $this->assertCount(2, $results);
        $this->assertEquals('FAILED_LOGIN', $results[array_key_first($results)]->action);
    }

    public function testGetRecentRespectsLimit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $log = R::dispense('audit_log');
            $log->action = 'LOGIN';
            $log->timestamp = sprintf('2026-04-%02d 10:00:00', 25 + $i);
            R::store($log);
        }

        $results = $this->repository->getRecent(2);
        $this->assertCount(2, $results);
    }
}
