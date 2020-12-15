<?php

namespace Core\Application;

use Illuminate\Container\Container;

class Application
{
	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function run()
	{
		//todo: implement run logic.
	}
}
