<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load FUSE model before RedBeanPHP setup
require_once __DIR__ . '/models/Model_Users.php';

// Allow snake_case bean types like `audit_log` and `token_blacklist`,
// matching the schema used by the application.
\RedBeanPHP\Util\DispenseHelper::setEnforceNamingPolicy(false);
