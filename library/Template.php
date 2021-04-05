<?php
class Template{
	private $assignedValues = [];
	private $partBuffer;
	private $path;
	private $debug;

	function __construct($path = "", $debug = false) {
		$this->debug = $debug;

		if (!empty(__DIR__ . '/../app/views/templates/' . $path . '.phtml') && file_exists(__DIR__ . '/../app/views/templates/' . $path . '.phtml')) {
			$this->path = $path;
		} else {
			echo '<pre>';
			echo __DIR__ . '/../app/views/templates/' . $path . '.phtml</br>';
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
		if (!empty(__DIR__ . '/../app/controllers/' . $this->path . 'Controller.php') && file_exists(__DIR__ . '/../app/controllers/' . $this->path . 'Controller.php')) {
			include(__DIR__ . '/../app/controllers/' . $this->path . 'Controller.php');
		}
		require_once(__DIR__ . '/../app/views/templates/' . $this->path . '.phtml');
	}
}
