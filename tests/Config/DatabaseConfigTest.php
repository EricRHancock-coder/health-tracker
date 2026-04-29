<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;

class DatabaseConfigTest extends TestCase {
    public function testConfigStructure(): void {
        $config = require __DIR__ . '/../../backend/config/database.php';
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('sqlite', $config);
        $this->assertArrayHasKey('path', $config['sqlite']);
        $this->assertStringEndsWith('health_tracker.db', $config['sqlite']['path']);
    }
}
