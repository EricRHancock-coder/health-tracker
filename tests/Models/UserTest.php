<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use RedBeanPHP\R;

class UserTest extends TestCase {
    protected function setUp(): void {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }
    }

    protected function tearDown(): void {
        R::nuke();
    }

    private function createBean(array $data): \RedBeanPHP\OODBBean {
        $bean = R::dispense('users');
        foreach ($data as $key => $value) {
            $bean->$key = $value;
        }
        return $bean;
    }

    public function testInitializationAndGetters() {
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
        
        $bean = $this->createBean($data);
        R::store($bean);

        $userBean = R::load('users', 1);

        $this->assertEquals(1, $userBean->id);
        $this->assertEquals('test@example.com', $userBean->email);
        $this->assertEquals('admin', $userBean->role);
        $this->assertEquals('Test User', $userBean->full_name);
        $this->assertTrue((bool)$userBean->is_verified);
        $this->assertFalse((bool)$userBean->is_disabled);
        $this->assertEquals('2026-04-24 10:00:00', $userBean->last_login_at);
    }

    public function testIsValidRole() {
        $admin = $this->createBean(['role' => 'admin']);
        $this->assertTrue($admin->isValidRole());

        $readwrite = $this->createBean(['role' => 'readwrite']);
        $this->assertTrue($readwrite->isValidRole());

        $readonly = $this->createBean(['role' => 'readonly']);
        $this->assertTrue($readonly->isValidRole());

        $invalid = $this->createBean(['role' => 'super_admin']);
        $this->assertFalse($invalid->isValidRole());

        $empty = $this->createBean(['role' => '']);
        $this->assertFalse($empty->isValidRole());
    }

    public function testSoftDeleteSetsDisabledFlag() {
        $user = $this->createBean(['is_disabled' => 0]);
        $this->assertFalse((bool)$user->is_disabled);
        
        $user->softDelete();
        $this->assertTrue((bool)$user->is_disabled);
    }

    public function testIsActiveReturnsCorrectStatus() {
        $user = $this->createBean(['is_verified' => 1, 'is_disabled' => 0]);
        $this->assertTrue($user->isActive());

        $user = $this->createBean(['is_verified' => 0, 'is_disabled' => 0]);
        $this->assertFalse($user->isActive());

        $user = $this->createBean(['is_verified' => 1, 'is_disabled' => 1]);
        $this->assertFalse($user->isActive());
    }

    public function testToArrayCastsTypesForSQLite() {
        $user = $this->createBean([
            'is_verified' => 1,
            'is_disabled' => 0
        ]);
        $array = $user->toArray();

        $this->assertSame(1, $array['is_verified']);
        $this->assertSame(0, $array['is_disabled']);
    }
}
