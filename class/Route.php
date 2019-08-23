<?php
class Route{
	private $urls = [];
	private $views = [];

	function __construct() {
		// code...
	}

	function add($url, $view = "", $conrol = "") {
		$this->urls[] = '/'.trim($url, '/');
		if (!empty($view)) {
			$this->views[] = $view;
		}
	}

	function submit(){
		$urlGetParam = isset($_GET['url']) ? '/' . $_GET['url'] : '/';
		foreach ($this->urls as $urlKey => $urlValue) {
			if ($urlValue === $urlGetParam) {
				$useView = $this->views[$urlKey];
				new $useView();
			}
		}
	}
}
