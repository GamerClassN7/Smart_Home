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
		if (!empty('../app/controllers/' . $this->path . 'Controller.php') && file_exists('../app/controllers/' . $this->path . 'Controller.php')) {
			include('../app/controllers/' . $this->path . 'Controller.php');
		}
		require_once('../app/views/templates/' . $this->path . '.phtml');
	}
}
