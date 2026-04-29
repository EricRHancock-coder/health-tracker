<?php

/**
 * Authentication Configuration
 * 
 * NOTE: The following configuration is for development purposes only.
 * The JWT_SECRET should be moved to an environment variable (.env) 
 * before deploying to production.
 */

return [
    'jwt' => [
        // TEMPORARY: Hardcoded secret for development.
        'secret' => getenv('JWT_SECRET') ?: 'dev_secret_key_change_this_in_production_12345',
        
        // Algorithm used for signing and verifying tokens.
        'algorithm' => 'HS256',
        
        // Token expiration time in seconds (24 hours).
        'ttl' => 86400,
        
        // Optional: standard claims
        'issuer' => 'health-tracker-api',
    ],
];
