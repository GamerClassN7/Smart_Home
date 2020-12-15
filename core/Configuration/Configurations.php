<?php

namespace Core\Configuration;

/**
 * Class Configurations
 * @package Core\Configuration
 * @author Romano Schoonheim https://github.com/romano1996
 */
class Configurations
{
	/** @var array */
	private $configurations;

	public function __construct(ConfigurationLoader $configurationLoader)
	{
		// Concern: Storing assoc array to this object.
		$this->configurations = $configurationLoader->load();
	}

	public function get(string $path)
	{
		// Concern: Accessing configurations based on "paths" application.something For example.
	}
}
