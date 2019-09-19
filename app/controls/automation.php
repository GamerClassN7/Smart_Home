<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['modalFinal']) && $_POST['modalFinal'] == "Next") {
		$doCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);
		$ifCode = json_encode([
			"type" => $_POST['atSelector'],
			"value" => $_POST['atSelectorValue'],
		], JSON_PRETTY_PRINT);
		$onDays = $_POST['atDays'];

		AutomationManager::create('name', $onDays, $doCode, $ifCode);

		header('Location: /vasek/home/' . strtolower(basename(__FILE__, '.php')), TRUE);
		die();
	}
}
?>
