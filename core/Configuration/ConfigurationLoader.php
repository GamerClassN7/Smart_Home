<?php

namespace Core\Configuration;

class ConfigurationLoader
{
	private const CONFIGURATIONS_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR
														  . '..' . DIRECTORY_SEPARATOR
														  . '..' . DIRECTORY_SEPARATOR . 'config'
														  . DIRECTORY_SEPARATOR;

	public function load(): array
	{
		return [];
	}

	/**
	 * Concerns
	 * 	 -> Loading configuration files
	 * 		- Scan directory for files.
	 * 		- Filtering none config / php files.
	 * 		- Creating assoc array.
	 */
}
