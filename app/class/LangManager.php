<?php
/**
* Language Manager
*/
class LanguageManager
{

	private $lngCode = 'en';
	private $lngDatabase = [];

	function __construct(string $lngCode)
	{
		$this->lngCode = $lngCode;
	}

	function load()
	{
		$file = './app/lang/en.php';
		$arrayFirst = include($file);
		$file = './app/lang/' . $this->lngCode . '.php';
		$arraySecond = include($file);
		$this->lngDatabase = array_merge($arrayFirst,$arraySecond);
		return true;
	}

	function get(string $stringKey)
	{
		if (isset($this->lngDatabase[$stringKey])) {
			return $this->lngDatabase[$stringKey];
		}
		return $stringKey;
	}

	function echo(string $stringKey)
	{
		if (isset($this->lngDatabase[$stringKey])) {
			return $this->lngDatabase[$stringKey];
		}
		return $stringKey;
	}
}
