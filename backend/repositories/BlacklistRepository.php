<?php

namespace App\Repositories;

use App\Utils\Database;

/**
 * Blacklist Repository
 * 
 * Manages persistence for revoked JWT tokens.
 */
class BlacklistRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Adds a token hash to the blacklist.
     */
    public function add(string $tokenHash, string $expiresAt): void
    {
        $sql = "INSERT INTO token_blacklist (token_hash, expires_at) VALUES (?, ?)";
        $this->db->execute($sql, [$tokenHash, $expiresAt]);
    }

    /**
     * Checks if a specific token hash is currently blacklisted.
     */
    public function isBlacklisted(string $tokenHash): bool
    {
        $sql = "SELECT 1 FROM token_blacklist WHERE token_hash = ? LIMIT 1";
        $result = $this->db->query($sql, [$tokenHash]);
        
        return !empty($result);
    }

    /**
     * Removes expired tokens from the blacklist to keep the table small.
     * Returns the number of rows removed.
     */
    public function cleanup(): int
    {
        $sql = "DELETE FROM token_blacklist WHERE expires_at < CURRENT_TIMESTAMP";
        return $this->db->execute($sql, []);
    }
}
