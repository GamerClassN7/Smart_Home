<?php
class Partial{
	var $assignedValues = [];
	var $partBuffer;
	var $path;
	var $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;
		if (!empty('templates/part/' . $path . '.phtml') && file_exists('templates/part/' . $path . '.phtml')) {
			$this->path = $path;
		} else {
			echo '<pre>';
			echo 'PHTML: Parial File ' . $path . ' not found';
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
		if (!empty($this->assignedValues)){
			extract($this->assignedValues);
		}

		require('templates/part/' . $this->path . '.phtml');
	}
}
