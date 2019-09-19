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
			$fileName = './app/logs/'. date("Y-m-d").'.log';
		}
		if(!is_dir("./app/logs/"))
		{
			mkdir("./app/logs/");
		}
		$this->logFile = fopen($fileName, "a") or die("Unable to open file!");
	}

	function write($value, $type = LogRecordType::ERROR){
		$record = "[".date("H:m:s")."][".$type."]" . $value . "\n";
		$record = Utilities::stringInsert($record,"\n",65);
		fwrite($this->logFile, $record);
	}

	function __destruct(){
		if (isset($this->logFile)) {
			fclose($this->logFile);
		}
	}
}
