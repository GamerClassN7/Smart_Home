<?php

namespace Core\Configuration;

/**
 * Class Configurations
 * @package Core\Configuration
 * @author Romano Schoonheim https://github.com/romano1996
 */
class Configurations
{
	private $configurations = [];

	public function __construct(FileSystem $fileSystem)
	{
		foreach ($fileSystem->getConfigurationFiles() as $configurationFile) {
			print_r($configurationFile);
		}
	}




//	private const IGNORED_FILES = [
//		'.',
//		'..',
//		'config.php',
//		'config_sample.php'
//	];
//
//
//	private $configurations = [];
//
//	public function __construct()
//	{
//		foreach (scandir(self::CONFIGURATION_DIRECTORY) as $item) {
//			if (in_array($item, self::IGNORED_FILES)) {
//				continue;
//			}
//
//			$filePath = self::CONFIGURATION_DIRECTORY . $item;
//
//
//
//
//			die();
//		}
//	}
//
//	public function config(string $path): array
//	{
//		return $this->configurations;
//	}
}
