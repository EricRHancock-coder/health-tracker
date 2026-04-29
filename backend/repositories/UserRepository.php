<?php

namespace App\Repositories;

use App\Models\User;
use App\Utils\Database;
use PDOException;
use RuntimeException;

class UserRepository {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User {
        $sql = "SELECT * FROM users WHERE email = ? AND is_disabled = 0 LIMIT 1";
        $rows = $this->db->query($sql, [$email]);

        if (empty($rows)) {
            return null;
        }

        return new User($rows[0]);
    }

    /**
     * Find a user by their ID.
     */
    public function findById(int $id): ?User {
        $sql = "SELECT * FROM users WHERE id = ? AND is_disabled = 0 LIMIT 1";
        $rows = $this->db->query($sql, [$id]);

        if (empty($rows)) {
            return null;
        }

        return new User($rows[0]);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): int {
        $sql = "INSERT INTO users (email, password_hash, role, full_name, is_verified, is_disabled) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? 'readonly',
            $data['full_name'] ?? '',
            (int)($data['is_verified'] ?? 0),
            (int)($data['is_disabled'] ?? 0)
        ];

        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing user.
     */
    public function update(User $user): void {
        $sql = "UPDATE users SET 
                email = ?, 
                password_hash = ?, 
                role = ?, 
                full_name = ?, 
                is_verified = ?, 
                is_disabled = ?, 
                last_login_at = ?
                WHERE id = ?";
        
        $this->db->execute($sql, [
            $user->email,
            $user->password_hash,
            $user->role,
            $user->full_name,
            (int)$user->is_verified,
            (int)$user->is_disabled,
            $user->last_login_at,
            $user->id
        ]);
    }
}
