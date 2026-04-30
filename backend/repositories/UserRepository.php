<?php

namespace App\Repositories;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserRepository {
    /**
     * Find an active user by email.
     */
    public function findByEmail(string $email): ?OODBBean {
        return R::findOne('users', 'email = ? AND is_disabled = 0', [$email]);
    }

    /**
     * Find a user by id, regardless of disabled state.
     *
     * Auth middleware needs the bean back so it can distinguish
     * "User not found" (401) from "Account is disabled" (403).
     */
    public function findById(int $id): ?OODBBean {
        $user = R::load('users', $id);
        return $user->id ? $user : null;
    }

    /**
     * Create a new user. Returns the new user id.
     */
    public function create(array $data): int {
        $user = R::dispense('users');
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->role = $data['role'] ?? 'readonly';
        $user->full_name = $data['full_name'] ?? '';
        $user->is_verified = (int)($data['is_verified'] ?? 0);
        $user->is_disabled = (int)($data['is_disabled'] ?? 0);

        return (int) R::store($user);
    }

    /**
     * Persist changes to an existing user bean.
     */
    public function update(OODBBean $user): void {
        R::store($user);
    }
}
