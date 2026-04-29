<?php

namespace App\Models;

/**
 * User Model
 * 
 * Handles user identity, authentication data, and role-based access.
 * Implements soft-delete via the is_disabled flag.
 */
class User {
    public ?int $id = null;
    public string $email;
    public string $password_hash;
    public string $role; // admin, readwrite, or readonly
    public string $full_name;
    public bool $is_verified = false;
    public bool $is_disabled = false;
    public ?string $last_login_at = null;

    /**
     * @param array $data Associative array of user attributes from the database
     */
    public function __construct(array $data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->email = $data['email'] ?? '';
        $this->password_hash = $data['password_hash'] ?? '';
        $this->role = $data['role'] ?? 'readonly';
        $this->full_name = $data['full_name'] ?? '';
        $this->is_verified = (bool)($data['is_verified'] ?? false);
        $this->is_disabled = (bool)($data['is_disabled'] ?? false);
        $this->last_login_at = $data['last_login_at'] ?? null;
    }

    /**
     * Validates the user's role against allowed values.
     */
    public function isValidRole(): bool {
        return in_array($this->role, ['admin', 'readwrite', 'readonly']);
    }

    /**
     * Implements Soft Delete behavior.
     * Instead of removing the row, we set is_disabled to true.
     */
    public function softDelete(): void {
        $this->is_disabled = true;
    }

    /**
     * Helper to check if the user is active and verified.
     */
    public function isActive(): bool {
        return !$this->is_disabled && $this->is_verified;
    }

    /**
     * Converts the object to an associative array.
     * Useful for database updates and creating diffs for the AuditLog.
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
            'role' => $this->role,
            'full_name' => $this->full_name,
            'is_verified' => (int)$this->is_verified,
            'is_disabled' => (int)$this->is_disabled,
            'last_login_at' => $this->last_login_at,
        ];
    }
}
