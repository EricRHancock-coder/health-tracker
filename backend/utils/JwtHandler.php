<?php

namespace App\Utils;

use App\Models\User;
use Exception;

/**
 * JwtHandler
 * 
 * Handles generation and verification of JSON Web Tokens.
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

        $header = json_encode(['alg' => $this->config['algorithm'], 'typ' => 'JWT']);
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $algoMap = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];

        $hashAlgo = $algoMap[$this->config['algorithm']] ?? 'sha256';

        $signature = hash_hmac(
            $hashAlgo,
            $base64UrlHeader . "." . $base64UrlPayload,
            $this->config['secret'],
            true
        );
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Verifies a JWT and returns the decoded payload.
     * 
     * @param string $token The JWT string.
     * @return array|null The decoded payload, or null if invalid.
     */
    public function verifyAndDecode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // 1. Verify signature
        $algoMap = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];

        $headerJson = $this->base64UrlDecode($base64UrlHeader);
        $header = json_decode($headerJson, true);
        
        if (!$header || !isset($header['alg'])) {
            return null;
        }

        $hashAlgo = $algoMap[$header['alg']] ?? 'sha256';

        $expectedSignature = hash_hmac(
            $hashAlgo,
            $base64UrlHeader . "." . $base64UrlPayload,
            $this->config['secret'],
            true
        );
        $expectedBase64UrlSignature = $this->base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedBase64UrlSignature, $base64UrlSignature)) {
            return null;
        }

        // 2. Decode payload
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        if (!$payload) {
            return null;
        }

        // 3. Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
