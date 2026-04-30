<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\JwtHandler;
use App\Models\User;

class JwtHandlerTest extends TestCase
{
    private array $authConfig;
    private JwtHandler $jwtHandler;

    protected function setUp(): void
    {
        $this->authConfig = [
            'jwt' => [
                'secret' => 'a_very_long_and_secure_test_secret_key_that_is_at_least_64_chars_long_for_hs512',
                'algorithm' => 'HS256',
                'ttl' => 3600,
                'issuer' => 'test-issuer',
            ],
        ];
        $this->jwtHandler = new JwtHandler($this->authConfig);
    }

    public function testGenerateReturnsValidJwtString()
    {
        $user = new User([
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $token = $this->jwtHandler->generate($user);

        $this->assertIsString($token);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, "JWT should consist of three parts separated by dots.");
    }

    public function testGenerateContainsCorrectClaims()
    {
        $user = new User([
            'id' => 42,
            'email' => 'dev@example.com',
            'role' => 'readwrite'
        ]);

        $token = $this->jwtHandler->generate($user);
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

        $this->assertEquals(42, $payload['sub']);
        $this->assertEquals('test-issuer', $payload['iss']);
        $this->assertEquals('readwrite', $payload['role']);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testVerifyAndDecodeWithValidToken()
    {
        $user = new User([
            'id' => 99,
            'email' => 'valid@example.com',
            'role' => 'caregiver'
        ]);

        $token = $this->jwtHandler->generate($user);
        $payload = $this->jwtHandler->verifyAndDecode($token);

        $this->assertIsArray($payload);
        $this->assertEquals(99, $payload['sub']);
        $this->assertEquals('caregiver', $payload['role']);
    }

    public function testVerifyAndDecodeWithInvalidSignature()
    {
        $user = new User([
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $token = $this->jwtHandler->generate($user);
        $tamperedToken = $token . 'extra';

        $this->assertNull($this->jwtHandler->verifyAndDecode($tamperedToken));
    }

    public function testVerifyAndDecodeWithMalformedToken()
    {
        $this->assertNull($this->jwtHandler->verifyAndDecode('not.a.jwt'));
        $this->assertNull($this->jwtHandler->verifyAndDecode('onepart'));
    }

    public function testVerifyAndDecodeWithExpiredToken()
    {
        // Create a short-lived config for testing expiration
        $expiredConfig = [
            'jwt' => [
                'secret' => 'a_very_long_and_secure_test_secret_key_that_is_at_least_64_chars_long_for_hs512',
                'algorithm' => 'HS256',
                'ttl' => -10, // Expired 10 seconds ago
                'issuer' => 'test-issuer',
            ],
        ];
        $expiredHandler = new JwtHandler($expiredConfig);

        $user = new User(['id' => 1, 'email' => 'test@example.com', 'role' => 'admin']);
        $token = $expiredHandler->generate($user);

        $this->assertNull($expiredHandler->verifyAndDecode($token));
    }

    public function testVerifyAndDecodeWithAlgorithmMismatch()
    {
        $user = new User(['id' => 1, 'email' => 'test@example.com', 'role' => 'admin']);
        
        // Generate a token with HS512
        $otherConfig = [
            'jwt' => [
                'secret' => 'a_very_long_and_secure_test_secret_key_that_is_at_least_64_chars_long_for_hs512',
                'algorithm' => 'HS512',
                'ttl' => 3600,
                'issuer' => 'test-issuer',
            ],
        ];
        $otherHandler = new JwtHandler($otherConfig);
        $token = $otherHandler->generate($user);

        // Attempt to decode with the main handler (which is HS256)
        $this->assertNull($this->jwtHandler->verifyAndDecode($token));
    }

    public function testVerifyAndDecodeWithInvalidKey()
    {
        $user = new User(['id' => 1, 'email' => 'test@example.com', 'role' => 'admin']);
        $token = $this->jwtHandler->generate($user);

        // Create a handler with a different (but valid length) key
        $wrongKeyConfig = [
            'jwt' => [
                'secret' => 'another_very_long_and_secure_test_secret_key_that_is_long_enough',
                'algorithm' => 'HS256',
                'ttl' => 3600,
                'issuer' => 'test-issuer',
            ],
        ];
        $wrongKeyHandler = new JwtHandler($wrongKeyConfig);

        // Attempt to decode the original token with the wrong key
        $this->assertNull($wrongKeyHandler->verifyAndDecode($token));
    }
}
