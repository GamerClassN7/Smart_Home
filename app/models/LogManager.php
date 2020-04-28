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
	function purge($days){
		$todayFileName = date("Y-m-d").'.log';
		$seconds = $days * 86400;

		$logFiles = scandir('../logs/');
		foreach ($logFiles as $key => $file) {
			if (in_array($file,array(".","..", ".gitkeep", $todayFileName)))
			{
				continue;
			}
			if (filemtime($file) > $seconds) {
				unlink('../logs/'.$file);
			}
		}
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
		if (strlen($record) > 65 ) {
			$record = Utilities::stringInsert($record,"\n",65);
		}
		fwrite($this->logFile, $record);
	}

	function __destruct(){
		if (isset($this->logFile)) {
			fclose($this->logFile);
		}
	}
}
