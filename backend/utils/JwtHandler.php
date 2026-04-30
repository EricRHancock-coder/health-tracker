<?php
namespace App\Utils;

use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
     * Generates a JWT for a specific user.
     */
    public function generate(User $user): string
    {
        $payload = [
            'iss' => $this->config['issuer'],
            'iat' => time(),
            'exp' => time() + $this->config['ttl'],
            'sub' => $user->id,
            'role' => $user->role
        ];

        return JWT::encode($payload, $this->config['secret'], $this->config['algorithm']);
    }

    /**
     * Verifies a JWT and returns the decoded payload.
     * 
     * @param string $token The JWT string.
     * @return array|null The decoded payload, or null if invalid.
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
