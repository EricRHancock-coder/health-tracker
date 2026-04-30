<?php

namespace App\Models;

use RedBeanPHP\SimpleModel;

/**
 * User Model
 * 
 * Handles user identity, authentication data, and role-based access.
 * Implements soft-delete via the is_disabled flag.
 */
class User extends SimpleModel {
    /**
     * @return int|null The user ID
     */
    public function getId(): ?int {
        return (int)$this->bean->id;
    }

    /**
     * @return string The user's email
     */
    public function getEmail(): string {
        return (string)$this->bean->email;
    }

    /**
     * @return string The hashed password
     */
    public function getPasswordHash(): string {
        return (string)$this->bean->password_hash;
    }

    /**
     * @return string The user's role (admin, readwrite, or readonly)
     */
    public function getRole(): string {
        return (string)$this->bean->role;
    }

    /**
     * @return string The user's full name
     */
    public function getFullName(): string {
        return (string)$this->bean->full_name;
    }

    /**
     * @return bool Whether the user is verified
     */
    public function isVerified(): bool {
        return (bool)$this->bean->is_verified;
    }

    /**
     * @return bool Whether the user is disabled
     */
    public function isDisabled(): bool {
        return (bool)$this->bean->is_disabled;
    }

    /**
     * @return string|null The last login timestamp
     */
    public function getLastLoginAt(): ?string {
        return $this->bean->last_login_at;
    }

    /**
     * Validates the user's role against allowed values.
     */
    public function isValidRole(): bool {
        return in_array($this->bean->role, ['admin', 'readwrite', 'readonly']);
    }

    /**
     * Implements Soft Delete behavior.
     * Instead of removing the row, we set is_disabled to true.
     */
    public function softDelete(): void {
        $this->bean->is_disabled = true;
    }

    /**
     * Helper to check if the user is active and verified.
     */
    public function isActive(): bool {
        return !$this->bean->is_disabled && $this->bean->is_verified;
    }

    /**
     * Converts the bean to an associative array.
     * Useful for database updates and creating diffs for the AuditLog.
     */
    public function toArray(): array {
        return [
            'id' => (int)$this->bean->id,
            'email' => $this->bean->email,
            'password_hash' => $this->bean->password_hash,
            'role' => $this->bean->role,
            'full_name' => $this->bean->full_name,
            'is_verified' => (int)$this->bean->is_verified,
            'is_disabled' => (int)$this->bean->is_disabled,
            'last_login_at' => $this->bean->last_login_at,
        ];
    }
}
