<?php
/**
*
*/

class LogRecordType{
	const WARNING = 'warning';
	const ERROR = 'error';
	const INFO = 'info';
}

class LogKeeper
{
	function cleaningDir ($dir, $seconds) {
		$todayFileName = date ("Y-m-d").'.log';
		$logFiles = scandir ($dir);
		foreach ($logFiles as $key => $file) {
			if (in_array ($file,array (".", "..", ".gitkeep", $todayFileName)))
			{
				continue;
			}
			if (!is_dir($dir . $file)) {
				if (strtotime(str_replace(".log", "", $file)) < (strtotime("now") - $seconds)) {
					unlink ($dir . $file);
				}
			} else {
				$this->cleaningDir ($path . $file . "/", $seconds);
			}
		}
	}

	function purge ($days) {
		$seconds = $days * 86400;
		$this->cleaningDir ('../logs/', $seconds);
	}
}

class LogManager
{

	private $logFile;
	function __construct($fileName = "")
	{
		if ($fileName == ""){
			$fileName = '../logs/'. date("Y-m-d").'.log';
		}
		if(!is_dir("../logs/"))
		{
			mkdir("../logs/");
		}
		$this->logFile = fopen($fileName, "a") or die("Unable to open file!");
	}

	function write($value, $type = LogRecordType::ERROR){
		$record = "[".date("H:m:s")."][".$type."]" . $value . "\n";
		fwrite($this->logFile, $record);
	}

	function __destruct(){
		if (isset($this->logFile)) {
			fclose($this->logFile);
		}
	}
}
