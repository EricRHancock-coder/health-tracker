<?php
require_once __DIR__ . '/config/database.php';

try {
    // R::setup() was called in database.php
    // Let's check if we can perform a simple operation
    $testBean = \RedBeanPHP\R::dispense('testconnection');
    $testBean->name = 'verification';
    $id = \RedBeanPHP\R::store($testBean);
    
    if ($id > 0) {
        echo "SUCCESS: RedBeanPHP is connected and can write to the database.\n";
        \RedBeanPHP\R::trash($testBean); // Clean up
        exit(0);
    } else {
        echo "FAILURE: RedBeanPHP connected but could not store a bean.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "ERROR: RedBeanPHP connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
