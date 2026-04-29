<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Utils\Database;
use App\Repositories\UserRepository;
use App\Models\User;

class UserRepositoryTest extends TestCase {
    private Database $db;
    private UserRepository $repository;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->db->setTestDsn('sqlite::memory:');
        
        // Initialize the users table
        $this->db->execute("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            full_name TEXT NOT NULL,
            is_verified BOOLEAN NOT NULL DEFAULT 0,
            is_disabled BOOLEAN NOT NULL DEFAULT 0,
            last_login_at DATETIME
        )");

        $this->repository = new UserRepository($this->db);
    }

    public function testFindByEmailReturnsUser(): void {
        $email = 'test@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);
        
        $this->db->execute(
            "INSERT INTO users (email, password_hash, role, full_name, is_verified) VALUES (?, ?, ?, ?, ?)",
            [$email, $password, 'admin', 'Test User', 1]
        );

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('Test User', $user->full_name);
        $this->assertTrue($user->is_verified);
    }

    public function testFindByEmailReturnsNullIfNotFound(): void {
        $user = $this->repository->findByEmail('nonexistent@example.com');
        $this->assertNull($user);
    }

    public function testFindByEmailReturnsNullIfDisabled(): void {
        $email = 'disabled@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);
        
        $this->db->execute(
            "INSERT INTO users (email, password_hash, role, full_name, is_verified, is_disabled) VALUES (?, ?, ?, ?, ?, ?)",
            [$email, $password, 'admin', 'Disabled User', 1, 1]
        );

        $user = $this->repository->findByEmail($email);
        $this->assertNull($user);
    }

    public function testFindByIdReturnsUser(): void {
        $this->db->execute(
            "INSERT INTO users (email, password_hash, role, full_name) VALUES (?, ?, ?, ?)",
            ['user@example.com', 'hash', 'readonly', 'ID User']
        );
        $id = (int)$this->db->lastInsertId();

        $user = $this->repository->findById($id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->id);
        $this->assertEquals('user@example.com', $user->email);
    }

    public function testCreateUser(): void {
        $userData = [
            'email' => 'new@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'readwrite',
            'full_name' => 'New User',
            'is_verified' => 1
        ];

        $id = $this->repository->create($userData);

        $this->assertGreaterThan(0, $id);

        $user = $this->repository->findById($id);
        $this->assertNotNull($user);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('readwrite', $user->role);
        $this->assertEquals('New User', $user->full_name);
        $this->assertTrue($user->is_verified);
    }
}
