<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\UserRepository;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        R::exec("CREATE TABLE IF NOT EXISTS users (
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

    protected function tearDown(): void
    {
        R::nuke();
    }

    public function testFindByEmailReturnsUser(): void
    {
        $email = 'test@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);

        R::exec(
            'INSERT INTO users (email, password_hash, role, full_name, is_verified) VALUES (?, ?, ?, ?, ?)',
            [$email, $password, 'admin', 'Test User', 1]
        );

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(OODBBean::class, $user);
        // Method calls are routed through FUSE to Model_Users.
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals('admin', $user->getRole());
        $this->assertEquals('Test User', $user->getFullName());
        $this->assertTrue($user->isVerified());
    }

    public function testFindByEmailReturnsNullIfNotFound(): void
    {
        $user = $this->repository->findByEmail('nonexistent@example.com');
        $this->assertNull($user);
    }

    public function testFindByEmailReturnsNullIfDisabled(): void
    {
        $email = 'disabled@example.com';
        $password = password_hash('secret', PASSWORD_BCRYPT);

        R::exec(
            'INSERT INTO users (email, password_hash, role, full_name, is_verified, is_disabled) VALUES (?, ?, ?, ?, ?, ?)',
            [$email, $password, 'admin', 'Disabled User', 1, 1]
        );

        $user = $this->repository->findByEmail($email);
        $this->assertNull($user);
    }

    public function testFindByIdReturnsUserEvenIfDisabled(): void
    {
        R::exec(
            'INSERT INTO users (email, password_hash, role, full_name, is_disabled) VALUES (?, ?, ?, ?, ?)',
            ['disabled@example.com', 'hash', 'readonly', 'Disabled User', 1]
        );
        $id = (int) R::getCell('SELECT last_insert_rowid()');

        $user = $this->repository->findById($id);

        $this->assertInstanceOf(OODBBean::class, $user);
        $this->assertEquals($id, (int) $user->id);
        $this->assertTrue($user->isDisabled());
    }

    public function testFindByIdReturnsNullIfMissing(): void
    {
        $this->assertNull($this->repository->findById(9999));
    }

    public function testCreateUser(): void
    {
        $userData = [
            'email' => 'new@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'readwrite',
            'full_name' => 'New User',
            'is_verified' => 1,
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

    public function testUpdatePersistsChanges(): void
    {
        $id = $this->repository->create([
            'email' => 'mut@example.com',
            'password_hash' => 'hash',
            'role' => 'readonly',
            'full_name' => 'Mut User',
            'is_verified' => 1,
        ]);

        $user = $this->repository->findById($id);
        $user->last_login_at = '2026-04-30 12:00:00';
        $this->repository->update($user);

        $reloaded = $this->repository->findById($id);
        $this->assertEquals('2026-04-30 12:00:00', $reloaded->last_login_at);
    }
}
