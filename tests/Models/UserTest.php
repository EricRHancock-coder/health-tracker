<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase {
    public function testConstructorInitializesCorrectly() {
        $data = [
            'id' => 1,
            'email' => 'test@example.com',
            'password_hash' => 'hashed_password',
            'role' => 'admin',
            'full_name' => 'Test User',
            'is_verified' => 1,
            'is_disabled' => 0,
            'last_login_at' => '2026-04-24 10:00:00'
        ];
        $user = new User($data);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('Test User', $user->full_name);
        $this->assertTrue($user->is_verified);
        $this->assertFalse($user->is_disabled);
        $this->assertEquals('2026-04-24 10:00:00', $user->last_login_at);
    }

    public function testIsValidRole() {
        // Test allowed roles defined in PROJECT.md
        $admin = new User(['role' => 'admin']);
        $this->assertTrue($admin->isValidRole());

        $readwrite = new User(['role' => 'readwrite']);
        $this->assertTrue($readwrite->isValidRole());

        $readonly = new User(['role' => 'readonly']);
        $this->assertTrue($readonly->isValidRole());

        // Test invalid roles
        $invalid = new User(['role' => 'super_admin']);
        $this->assertFalse($invalid->isValidRole());

        $empty = new User(['role' => '']);
        $this->assertFalse($empty->isValidRole());
    }

    public function testSoftDeleteSetsDisabledFlag() {
        $user = new User(['is_disabled' => 0]);
        $this->assertFalse($user->is_disabled);
        
        $user->softDelete();
        $this->assertTrue($user->is_disabled);
    }

    public function testIsActiveReturnsCorrectStatus() {
        // Case: Active
        $user = new User(['is_verified' => 1, 'is_disabled' => 0]);
        $this->assertTrue($user->isActive());

        // Case: Not verified
        $user = new User(['is_verified' => 0, 'is_disabled' => 0]);
        $this->assertFalse($user->isActive());

        // Case: Disabled
        $user = new User(['is_verified' => 1, 'is_disabled' => 1]);
        $this->assertFalse($user->isActive());
    }

    public function testToArrayCastsTypesForSQLite() {
        $user = new User([
            'is_verified' => true,
            'is_disabled' => false
        ]);
        $array = $user->toArray();

        $this->assertSame(1, $array['is_verified']);
        $this->assertSame(0, $array['is_disabled']);
    }
}
