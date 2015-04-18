<?php

/**
 * Test bootstrap.
 */

// Check composer autoload
if (@!include __DIR__ . '/../vendor/autoload.php') {
    echo 'Install Nette Tester using `composer update --dev`';
    exit(1);
}

// Includes
include __DIR__ . '/Gopay/BaseTestCase.php';

// Configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

// Create temporary directory
define('TEMP_DIR', __DIR__ . '/tmp/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);

// Test functions
function test(\Closure $function)
{
    $function();
}
