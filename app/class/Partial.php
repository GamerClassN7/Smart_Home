<?php
class Partial{
	private $assignedValues = [];
	private $partBuffer;
	private $path;
	private $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;
		if (!empty('../app/templates/part/' . $path . '.phtml') && file_exists('../app/templates/part/' . $path . '.phtml')) {
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

		require('../app/templates/part/' . $this->path . '.phtml');
	}
}
