<?php
/**
*
*/



class LogManager
{
	private $logFile;
	private $logLevel = 1;

	public function __construct($fileName = "")
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

	public function setLevel($type = LogRecordTypess::WARNING){
		$this->logLevel = $type['level'];
	}

	public function write($value, $type = LogRecordTypess::ERROR){
		if ($type['level'] <= $this->logLevel) {
			$record = "[".date("H:m:s")."][".$type['identifier']."]" . $value . "\n";
			fwrite($this->logFile, $record);
		}
	}

	public function __destruct(){
		if (isset($this->logFile) && $this->logFile != "Unable to open file!") {
			fclose($this->logFile);
		}
	}
}
