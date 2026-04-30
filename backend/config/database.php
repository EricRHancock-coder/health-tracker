<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Database Configuration
 * 
 * Initializes RedBeanPHP and manages SQLite connection.
 */

$config = [
    'sqlite' => [
        'path' => __DIR__ . '/../database/health_tracker.db',
    ],
    'encryption' => [
        'enabled' => false,
    ],
];

// Initialize RedBeanPHP
\RedBeanPHP\R::setup("sqlite:" . $config['sqlite']['path']);

return $config;

