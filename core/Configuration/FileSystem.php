<?php

namespace Core\Configuration;

use Core\Configuration\Factories\FileFactory;
use Core\Configuration\Objects\File;

/**
 * Class FileSystem
 * @package Core\Configuration
 * @author Romano Schoonheim https://github.com/romano1996
 */
class FileSystem
{
	private const CONFIGURATION_DIRECTORY = __DIR__ . '/../../config/';

	private $fileFactory;

	public function __construct(FileFactory $factory)
	{

	}


	/**
	 * @return File[]
	 */
	public function getConfigurationFiles(): array
	{
		return [];
	}

}
