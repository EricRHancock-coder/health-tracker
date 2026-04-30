<?php

namespace App\Repositories;

use RedBeanPHP\R;

/**
 * Blacklist Repository
 *
 * Manages persistence for revoked JWT tokens.
 */
class BlacklistRepository
{
    /**
     * Add a token hash to the blacklist with its expiration timestamp.
     */
    public function add(string $tokenHash, string $expiresAt): int
    {
        $bean = R::dispense('token_blacklist');
        $bean->token_hash = $tokenHash;
        $bean->expires_at = $expiresAt;

        return (int) R::store($bean);
    }

    /**
     * Check whether a token hash is currently blacklisted.
     */
    public function isBlacklisted(string $tokenHash): bool
    {
        return R::findOne('token_blacklist', 'token_hash = ?', [$tokenHash]) !== null;
    }

    /**
     * Remove expired tokens. Returns the number of rows removed.
     */
    public function cleanup(): int
    {
        return (int) R::exec('DELETE FROM token_blacklist WHERE expires_at < CURRENT_TIMESTAMP');
    }
}
