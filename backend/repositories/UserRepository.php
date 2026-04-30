<?php

namespace App\Repositories;

use Model_Users;
use RedBeanPHP\R;

class UserRepository {
    /**
     * Find a user by their email address.
     * 
     * Uses RedBeanPHP FUSE to return an instance of Model_Users.
     */
    public function findByEmail(string $email): ?Model_Users {
        // R::findOne returns the bean (wrapped in the Model) or null
        $user = R::findOne('users', 'email = ? AND is_disabled = 0', [$email]);
        
        return $user instanceof Model_Users ? $user : null;
    }

    /**
     * Find a user by their ID.
     */
    public function findById(int $id): ?Model_Users {
        // R::load returns a bean with ID 0 if not found
        $user = R::load('users', $id);
        
        if (!$user->id) {
            return null;
        }

        return $user instanceof Model_Users ? $user : null;
    }

    /**
     * Create a new user.
     * 
     * @param array $data
     * @return int The new user ID
     */
    public function create(array $data): int {
        $user = R::dispense('users');
        
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->role = $data['role'] ?? 'readonly';
        $user->full_name = $data['full_name'] ?? '';
        $user->is_verified = (int)($data['is_verified'] ?? 0);
        $user->is_disabled = (int)($data['is_disabled'] ?? 0);
        
        // R::store returns the ID of the newly inserted bean
        return R::store($user);
    }

    /**
     * Update an existing user.
     */
    public function update(Model_Users $user): void {
        // RedBeanPHP manages the update via the bean property
        // Since $user is a Fused Model, $user->bean is available
        R::store($user->bean);
    }
}
