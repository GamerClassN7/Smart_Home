<?php

/**
 * Composer autoload
 */

use Core\Application\Application;
use Core\Configuration\Configurations;
use Illuminate\Container\Container;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$container->singleton(
	Configurations::class,
	Configurations::class
);

/**
 * Create application & run
 */
$application = new Application(
	$container,
	$container->make(Configurations::class)
);
$application->run();


/**
 * Bootstrap v1.0
 */
require_once __DIR__ . '/../app/Bootstrap.php';
