<?php
class Template extends Partial{
	var $assignedValues = [];
	var $partBuffer;
	var $path;
	var $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;
		if (!empty('templates/' . $path . '.phtml') && file_exists('templates/' . $path . '.phtml')) {
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
		if (!empty('controls/' . $this->path . '.php') && file_exists('controls/' . $this->path . '.php')) {
			require_once('controls/' . $this->path . '.php');
		}
		require_once('templates/' . $this->path . '.phtml');
	}
}
