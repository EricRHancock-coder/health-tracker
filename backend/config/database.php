<?php

/**
 * Authentication Configuration
 * 
 * NOTE: The following configuration is for development purposes only.
 * The JWT_SECRET should be moved to an environment variable (.env) 
 * before deploying to production.
 */

return [
    'sqlite' => [
        'path' => __DIR__ . '/../database/health_tracker.db',
    ],
    // Placeholder for future encryption implementation as per PROJECT.md
    'encryption' => [
        'enabled' => false,
    ],
];
