<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Utils\Database;
use App\Repositories\UserRepository;
use Model_Users;

class UserRepositoryTest extends TestCase {
    private Database $db;
    private UserRepository $repository;

    protected function setUp(): void {
        if (!\RedBeanPHP\R::testConnection()) {
            \RedBeanPHP\R::setup('sqlite::memory:');
        }
        
        // Initialize the users table
        \RedBeanPHP\R::exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            full_name TEXT NOT NULL,
            is_verified BOOLEAN NOT NULL DEFAULT 0,
            is_disabled BOOLEAN NOT NULL DEFAULT 0,
            last_login_at DATETIME
        )");

        $this->repository = new UserRepository();
    }

    protected function tearDown(): void {
        \RedBeanPHP\R::close();
    }



    public function testFindByEmailReturnsUser(): void {
        $email = 'test@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);
        
        \RedBeanPHP\R::exec(
            "INSERT INTO users (email, password_hash, role, full_name, is_verified) VALUES (?, ?, ?, ?, ?)",
            [$email, $password, 'admin', 'Test User', 1]
        );

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(Model_Users::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals('admin', $user->getRole());
        $this->assertEquals('Test User', $user->getFullName());
        $this->assertTrue($user->isVerified());
    }

    public function testFindByEmailReturnsNullIfNotFound(): void {
        $user = $this->repository->findByEmail('nonexistent@example.com');
        $this->assertNull($user);
    }

    public function testFindByEmailReturnsNullIfDisabled(): void {
        $email = 'disabled@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);
        
        \RedBeanPHP\R::exec(
            "INSERT INTO users (email, password_hash, role, full_name, is_verified, is_disabled) VALUES (?, ?, ?, ?, ?, ?)",
            [$email, $password, 'admin', 'Disabled User', 1, 1]
        );

        $user = $this->repository->findByEmail($email);
        $this->assertNull($user);
    }


    public function testFindByIdReturnsUser(): void {
        \RedBeanPHP\R::exec(
            "INSERT INTO users (email, password_hash, role, full_name) VALUES (?, ?, ?, ?)",
            ['user@example.com', 'hash', 'readonly', 'ID User']
        );
        $id = (int)\RedBeanPHP\R::getCell("SELECT last_insert_rowid()");

        $user = $this->repository->findById($id);

        $this->assertInstanceOf(Model_Users::class, $user);
        $this->assertEquals($id, $user->getId());
        $this->assertEquals('user@example.com', $user->getEmail());
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
        $this->assertEquals('new@example.com', $user->getEmail());
        $this->assertEquals('readwrite', $user->getRole());
        $this->assertEquals('New User', $user->getFullName());
        $this->assertTrue($user->isVerified());
    }
}
