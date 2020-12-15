<?php

/**
 * Composer autoload
 */

use Core\Application\Application;
use Illuminate\Container\Container;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

/**
 * Load providers
 */



/**
 * Create application & run
 */
$application = new Application(
	$container
);
$application->run();
