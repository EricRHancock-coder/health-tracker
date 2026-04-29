<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        // Load the configuration file
        // The path is relative to this test file: tests/Config/AuthTest.php -> backend/config/auth.php
        $this->config = require __DIR__ . '/../../backend/config/auth.php';
    }

    public function testConfigReturnsArray(): void
    {
        $this->assertIsArray($this->config, 'The configuration must return an array.');
    }

    public function testJwtConfigStructure(): void
    {
        $this->assertArrayHasKey('jwt', $this->config, 'The config must have a "jwt" key.');
        $jwt = $this->config['jwt'];
        
        $this->assertIsArray($jwt, 'The "jwt" configuration must be an array.');
        $this->assertArrayHasKey('secret', $jwt, 'The jwt config must have a "secret" key.');
        $this->assertArrayHasKey('algorithm', $jwt, 'The jwt config must have an "algorithm" key.');
        $this->assertArrayHasKey('ttl', $jwt, 'The jwt config must have a "ttl" key.');
        $this->assertArrayHasKey('issuer', $jwt, 'The jwt config must have an "issuer" key.');
    }

    public function testJwtConfigValues(): void
    {
        $jwt = $this->config['jwt'];

        // Verify the algorithm is HS256 as decided
        $this->assertEquals('HS256', $jwt['algorithm'], 'The JWT algorithm should be HS256.');

        // Verify the TTL is 24 hours (86400 seconds) as decided
        $this->assertEquals(86400, $jwt['ttl'], 'The JWT TTL should be 86400 seconds (24 hours).');

        // Verify the issuer is set
        $this->assertEquals('health-tracker-api', $jwt['issuer'], 'The JWT issuer should be "health-tracker-api".');

        // Ensure secret is not empty (even if it is temporary)
        $this->assertNotEmpty($jwt['secret'], 'The JWT secret should not be empty.');
    }
}
