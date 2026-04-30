<?php
namespace App\Utils;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RedBeanPHP\OODBBean;

/**
 * JwtHandler
 *
 * Handles generation and verification of JSON Web Tokens using firebase/php-jwt.
 */
class JwtHandler
{
    private array $config;

    public function __construct(array $authConfig)
    {
        $this->config = $authConfig['jwt'];
    }

    /**
     * Generate a JWT for a user bean.
     */
    public function generate(OODBBean $user): string
    {
        $payload = [
            'iss' => $this->config['issuer'],
            'iat' => time(),
            'exp' => time() + $this->config['ttl'],
            'sub' => (int) $user->id,
            'role' => (string) $user->role,
        ];

        return JWT::encode($payload, $this->config['secret'], $this->config['algorithm']);
    }

    /**
     * Verify a JWT and return the decoded payload, or null if invalid.
     */
    public function verifyAndDecode(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->config['secret'], $this->config['algorithm']));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
