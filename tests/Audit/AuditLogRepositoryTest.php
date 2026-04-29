<?php

namespace Tests\Audit;

use PHPUnit\Framework\TestCase;
use App\Repositories\AuditLogRepository;
use App\Models\AuditLog;
use App\Utils\Database;

class AuditLogRepositoryTest extends TestCase
{
    private $dbMock;
    private AuditLogRepository $repository;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(Database::class);
        $this->repository = new AuditLogRepository($this->dbMock);
    }

    public function testSavePersistsCorrectData()
    {
        $log = new AuditLog([
            'user_id' => 1,
            'action' => 'CREATE',
            'table_name' => 'users',
            'record_id' => 10,
            'old_values' => null,
            'new_values' => json_encode(['name' => 'John']),
            'ip_address' => '127.0.0.1'
        ]);

        $this->dbMock->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('INSERT INTO audit_log'),
                $this->callback(function($params) use ($log) {
                    return $params[0] === 1 && 
                           $params[1] === 'CREATE' && 
                           $params[4] === null &&
                           $params[5] === json_encode(['name' => 'John']);
                })
            );

        $this->repository->save($log);
    }

    public function testGetRecentReturnsAuditLogObjects()
    {
        $mockData = [
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'LOGIN',
                'table_name' => null,
                'record_id' => null,
                'old_values' => null,
                'new_values' => null,
                'ip_address' => '127.0.0.1',
                'timestamp' => '2026-04-27 10:00:00'
            ]
        ];

        $this->dbMock->expects($this->once())
            ->method('query')
            ->willReturn($mockData);

        $results = $this->repository->getRecent(1);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(AuditLog::class, $results[0]);
        $this->assertEquals(1, $results[0]->id);
        $this->assertEquals('LOGIN', $results[0]->action);
    }
}
