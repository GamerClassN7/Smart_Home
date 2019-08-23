<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['modalFinal']) && $_POST['modalFinal'] == "Next") {
		$ifCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);
		$doCode = $_POST['atSelector'];
		$onDays = $_POST['atDays'];

		AutomationManager::create('name', $onDays, $doCode, $ifCode);

		header('Location: /vasek/home/' . strtolower(basename(__FILE__, '.php')), TRUE);
		die();
	}
}
?>
