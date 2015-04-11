<?php

require __DIR__ . '/../vendor/autoload.php' ;
require_once __DIR__ . '/inc/BaseTest.php';

Tester\Environment::setup() ;
date_default_timezone_set('Europe/Prague');

// create temporary directory
define('TEMP_DIR', __DIR__ . '/temp/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);
