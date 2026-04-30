<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\BlacklistRepository;
use RedBeanPHP\R;

class BlacklistRepositoryTest extends TestCase
{
    private BlacklistRepository $repository;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        R::exec("CREATE TABLE IF NOT EXISTS token_blacklist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token_hash TEXT UNIQUE NOT NULL,
            expires_at DATETIME NOT NULL
        )");
        R::exec('CREATE INDEX IF NOT EXISTS idx_token_blacklist_expires_at ON token_blacklist(expires_at)');
        R::exec('CREATE INDEX IF NOT EXISTS idx_token_blacklist_hash ON token_blacklist(token_hash)');

        $this->repository = new BlacklistRepository();
    }

    protected function tearDown(): void
    {
        R::nuke();
    }

    public function testAddAndVerifyBlacklist(): void
    {
        $hash = hash('sha256', 'mock_jwt_token');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->add($hash, $expiry);

        $this->assertTrue($this->repository->isBlacklisted($hash));
        $this->assertFalse($this->repository->isBlacklisted('non_existent_hash'));
    }

    public function testDuplicateHashThrowsError(): void
    {
        $hash = hash('sha256', 'duplicate_token');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->add($hash, $expiry);

        // RedBeanPHP wraps PDOExceptions in RedException\SQL.
        $this->expectException(\RedBeanPHP\RedException\SQL::class);
        $this->repository->add($hash, $expiry);
    }

    public function testCleanupRemovesExpiredTokens(): void
    {
        $oldExpiry = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $newExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->add(hash('sha256', 'expired_token'), $oldExpiry);
        $this->repository->add(hash('sha256', 'valid_token'), $newExpiry);

        $this->assertTrue($this->repository->isBlacklisted(hash('sha256', 'valid_token')));

        $removedCount = $this->repository->cleanup();

        $this->assertEquals(1, $removedCount);
        $this->assertFalse($this->repository->isBlacklisted(hash('sha256', 'expired_token')));
        $this->assertTrue($this->repository->isBlacklisted(hash('sha256', 'valid_token')));
    }
}
