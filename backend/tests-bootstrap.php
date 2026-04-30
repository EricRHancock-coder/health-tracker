<?php

require_once __DIR__ . '/vendor/autoload.php';

// Allow snake_case bean types like `audit_log` and `token_blacklist`,
// matching the schema used by the application.
\RedBeanPHP\Util\DispenseHelper::setEnforceNamingPolicy(false);
