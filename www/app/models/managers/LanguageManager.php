<?php
/**
* Language Manager
*/
class LanguageManager
{

	private $lngCode = 'en';
	private $lngDatabase = [];
	private $debug = false;

	function __construct(string $lngCode, bool $debug = false)
	{
		$this->lngCode = $lngCode;
		$this->debug = $debug;
	}

	function load()
	{
		$file = '../lang/en.php';
		if (!file_exists($file)){
			echo 'ERROR: en.php not found';
			die();
			//TODO add lng EXEPTIONS
		}
		$arrayFirst = include($file);
		$file = '../lang/' . $this->lngCode . '.php';
		$arraySecond = [];
		if (file_exists($file)){
			$arraySecond = include($file);
		}
		$this->lngDatabase = array_merge($arrayFirst, $arraySecond);
		return true;
	}

	function get(string $stringKey)
	{
		if ($this->debug) {
			return $stringKey;
		}
		if (isset($this->lngDatabase[$stringKey])) {
			return $this->lngDatabase[$stringKey];
		}
		return $stringKey;
	}

	function echo(string $stringKey)
	{
		if ($this->debug) {
			echo $stringKey;
			return;
		}
		if (isset($this->lngDatabase[$stringKey])) {
			echo $this->lngDatabase[$stringKey];
			return;
		}
		echo $stringKey;
		return;
	}
}
