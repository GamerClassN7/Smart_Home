<?php
class Template extends Partial{
	var $assignedValues = [];
	var $partBuffer;
	var $path;
	var $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;
		if (!empty('app/templates/' . $path . '.phtml') && file_exists('app/templates/' . $path . '.phtml')) {
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
		if (!empty('app/controls/' . $this->path . '.php') && file_exists('app/controls/' . $this->path . '.php')) {
			require_once('app/controls/' . $this->path . '.php');
		}
		require_once('app/templates/' . $this->path . '.phtml');
	}
}
