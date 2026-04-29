<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\AuditLog;

class AuditLogTest extends TestCase {
    public function testConstructorInitializesCorrectly() {
        $data = [
            'id' => 10,
            'user_id' => 5,
            'action' => 'UPDATE',
            'table_name' => 'users',
            'record_id' => 1,
            'old_values' => '{"name": "old"}',
            'new_values' => '{"name": "new"}',
            'ip_address' => '127.0.0.1',
            'timestamp' => '2026-04-24 12:00:00'
        ];
        $log = new AuditLog($data);

        $this->assertEquals(10, $log->id);
        $this->assertEquals(5, $log->userId);
        $this->assertEquals('UPDATE', $log->action);
        $this->assertEquals('users', $log->tableName);
        $this->assertEquals(1, $log->recordId);
        $this->assertEquals('{"name": "old"}', $log->oldValues);
        $this->assertEquals('{"name": "new"}', $log->newValues);
        $this->assertEquals('127.0.0.1', $log->ipAddress);
        $this->assertEquals('2026-04-24 12:00:00', $log->timestamp);
    }

    public function testConstructorHandlesNullFields() {
        // Test for a LOGIN attempt where table_name and record_id are NULL
        $data = [
            'user_id' => 5,
            'action' => 'LOGIN',
            'ip_address' => '192.168.1.1'
        ];
        $log = new AuditLog($data);

        $this->assertEquals(5, $log->userId);
        $this->assertEquals('LOGIN', $log->action);
        $this->assertNull($log->tableName);
        $this->assertNull($log->recordId);
        $this->assertEquals('192.168.1.1', $log->ipAddress);
        $this->assertNotNull($log->timestamp);
    }

    public function testToArrayCastsCorrectly() {
        $data = [
            'id' => 1,
            'user_id' => 99,
            'action' => 'DELETE',
            'table_name' => 'residents',
            'record_id' => 42,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => '10.0.0.1',
            'timestamp' => '2026-04-24 12:00:00'
        ];
        $log = new AuditLog($data);
        $array = $log->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(99, $array['user_id']);
        $this->assertSame(42, $array['record_id']);
        $this->assertNull($array['old_values']);
        $this->assertSame('DELETE', $array['action']);
    }
}
