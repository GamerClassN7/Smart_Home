<?php
/**
*
*/

class LogRecordType{
	const WARNING = 'warning';
	const ERROR = 'error';
	const INFO = 'info';
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
