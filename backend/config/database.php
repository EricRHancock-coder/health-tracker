<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Database Configuration
 *
 * Initializes RedBeanPHP and manages the SQLite connection. Setup is
 * idempotent so tests that wire an in-memory connection up front are
 * not overwritten when production code requires this file.
 */
$config = [
    'sqlite' => [
        'path' => __DIR__ . '/../database/health_tracker.db',
    ],
    'encryption' => [
        'enabled' => false,
    ],
];

if (!\RedBeanPHP\R::testConnection()) {
    \RedBeanPHP\R::setup('sqlite:' . $config['sqlite']['path']);
}

return $config;
