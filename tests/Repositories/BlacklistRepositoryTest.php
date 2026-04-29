<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\BlacklistRepository;
use App\Utils\Database;

class BlacklistRepositoryTest extends TestCase
{
    private Database $db;
    private BlacklistRepository $repository;

    protected function setUp(): void
    {
        // Initialize an in-memory database for isolation
        $this->db = Database::getInstance();
        $this->db->setTestDsn('sqlite::memory:');

        // Set up the table structure
        $this->db->execute("CREATE TABLE token_blacklist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token_hash TEXT UNIQUE NOT NULL,
            expires_at DATETIME NOT NULL
        )");
        $this->db->execute("CREATE INDEX idx_token_blacklist_expires_at ON token_blacklist(expires_at)");
        $this->db->execute("CREATE INDEX idx_token_blacklist_hash ON token_blacklist(token_hash)");

        $this->repository = new BlacklistRepository($this->db);
    }

    public function testAddAndVerifyBlacklist()
    {
        $hash = hash('sha256', 'mock_jwt_token');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->add($hash, $expiry);

        $this->assertTrue($this->repository->isBlacklisted($hash));
        $this->assertFalse($this->repository->isBlacklisted('non_existent_hash'));
    }

    public function testDuplicateHashThrowsError()
    {
        $hash = hash('sha256', 'duplicate_token');
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->add($hash, $expiry);

        // Expecting a PDOException due to the UNIQUE constraint on token_hash
        $this->expectException(\PDOException::class);
        $this->repository->add($hash, $expiry);
    }

    public function testCleanupRemovesExpiredTokens()
    {
        $oldExpiry = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $newExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Add one expired and one valid token
        $this->repository->add(hash('sha256', 'expired_token'), $oldExpiry);
        $this->repository->add(hash('sha256', 'valid_token'), $newExpiry);

        // Ensure the valid one is there initially
        $this->assertTrue($this->repository->isBlacklisted(hash('sha256', 'valid_token')));

        // Run cleanup
        $removedCount = $this->repository->cleanup();

        // Verify
        $this->assertEquals(1, $removedCount);
        $this->assertFalse($this->repository->isBlacklisted(hash('sha256', 'expired_token')));
        $this->assertTrue($this->repository->isBlacklisted(hash('sha256', 'valid_token')));
    }
}
