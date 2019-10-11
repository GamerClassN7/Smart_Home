<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['modalFinal']) && $_POST['modalFinal'] == "Next") {
		$doCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);
		$ifCode = json_encode([
			"type" => $_POST['atSelector'],
			"value" => $_POST['atSelectorValue'],
		], JSON_PRETTY_PRINT);
		$onDays = $_POST['atDays'];

		AutomationManager::create($_POST['name'], $onDays, $doCode, $ifCode);

		header('Location: ' . BASEDIR . strtolower(basename(__FILE__, '.php')), TRUE);
		die();
	} else if (isset($_POST['modalFinal']) && $_POST['modalFinal'] == "Upravit") {
		$doCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);

		if (isset ($_POST['atDeviceValue'])) {
			$subDeviceId = $_POST['atDeviceValue'];
			$subDeviceValue = $_POST['atDeviceValueInt'];
			$subDevice = SubDeviceManager::getSubDevice($subDeviceId);
			$subDeviceMaster = SubDeviceManager::getSubDeviceMaster($subDeviceId,$subDevice['type']);

			$json = json_encode([
					'deviceID' => $subDeviceMaster['device_id'],
					'type'=> $subDevice['type'],
					'value'=> $subDeviceValue,
			]);
		}


		$_POST['atSelectorValue'] = (isset($_POST['atTime']) ? $_POST['atTime'] : (isset($_POST['atDeviceValue']) ? $json : $_POST['atSelector']));
		$ifCode = json_encode([
			"type" => $_POST['atSelector'],
			"value" => $_POST['atSelectorValue'],
		], JSON_PRETTY_PRINT);
		$onDays = ($_POST['day'] != '' ? json_encode($_POST['day']) : '');

		AutomationManager::create($_POST['name'], $onDays, $doCode, $ifCode, (isset ($_POST['automation_id']) ? $_POST['automation_id'] : ""));

		header('Location: ' . BASEDIR . strtolower(basename(__FILE__, '.php')));
		die();
	}
}
?>
