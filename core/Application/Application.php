<?php

namespace Core\Application;

use Core\Configuration\Configurations;
use Illuminate\Container\Container;

class Application
{
	/** @var Container $container */
	private $container;

	/** @var Configurations */
	private $configurations;

	public function __construct(Container $container, Configurations $configurations)
	{
		$this->container = $container;
		$this->configurations = $configurations;
	}

	public function run()
	{
		//todo: implement run logic.
	}
}
