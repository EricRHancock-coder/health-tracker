<?php

namespace App\Utils;

/**
 * Response Utility
 * 
 * Provides a standardized JSON response structure for the API.
 */
class Response {
    private array $data;
    private int $statusCode;
    private array $headers;

    public function __construct(array $data, int $statusCode = 200, array $headers = []) {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Returns a successful JSON response.
     */
    public static function success(array $data, ?string $message = null, int $statusCode = 200): self {
        return new self([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Returns an error JSON response.
     */
    public static function error(string $message, int $statusCode = 400, $details = null): self {
        return new self([
            'success' => false,
            'error' => $message,
            'details' => $details,
        ], $statusCode);
    }

    /**
     * Generic JSON helper to match current controller usage.
     */
    public static function json(array $payload, int $statusCode = 200, array $headers = []): self {
        return new self($payload, $statusCode, $headers);
    }

    public function toArray(): array {
        return $this->data;
    }

    public function getBody(): string {
        return json_encode($this->data);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }
}
