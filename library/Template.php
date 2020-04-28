<?php
class Template{
	private $assignedValues = [];
	private $partBuffer;
	private $path;
	private $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;
		if (!empty('../app/views/templates/' . $path . '.phtml') && file_exists('../app/views/templates/' . $path . '.phtml')) {
			$this->path = $path;
		} else {
			echo '<pre>';
			echo 'PHTML: Template File ' . $path . ' not found';
			echo '</pre>';
			die();
		}
	}

	function prepare($searchS, $repleaceS) {
		if (!empty($searchS)) {
			$this->assignedValues[strtoupper($searchS)] = $repleaceS;
		}
		echo ($this->debug == true ? var_dump($this->assignedValues) : '');
	}

	function render() {
		extract($this->assignedValues);
		if (!empty('../app/controls/' . $this->path . '.php') && file_exists('../app/controls/' . $this->path . '.php')) {
			include('../app/controls/' . $this->path . '.php');
		}
		require_once('../app/views/templates/' . $this->path . '.phtml');
	}
}
