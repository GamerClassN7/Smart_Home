<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['modalFinal']) && $_POST['action'] == "add") {
		$doCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);

		$value = $_POST['atSelector'];
		if ($_POST['atSelector'] == 'time'){
			  $value = $_POST['atSelectorValue'];
		} else if ($_POST['atSelector'] == 'atDeviceValue') {
			$value = json_decode($_POST['atSelectorValue']);
		} else if ($_POST['atSelector'] == 'inHome' || $_POST['atSelector'] == 'outHome') {
			$value = UserManager::getUserData('user_id');
		}


		$ifCode = json_encode([
			"type" => $_POST['atSelector'],
			"value" => $value,
		], JSON_PRETTY_PRINT);
		$onDays = $_POST['atDays'];

		//Debug
		// if (DEBUGMOD == 1) {
		// 	echo '<pre>';
		// 	echo $permissionsInJson;
		// 	echo $deviceId;
		// 	var_dump(json_decode ($permissionsInJson));
		// 	echo '</pre>';
		// 	echo '<a href="' . BASEDIR .'">CONTINUE</a>';
		// 	die();
		// }

		AutomationManager::create($_POST['name'], $onDays, $doCode, $ifCode);

		header('Location: ' . BASEURL . strtolower(basename(__FILE__, '.php')));
		die();
	} else if (isset($_POST['modalFinal']) && $_POST['action'] == "edit") {
		$doCode = json_encode($_POST['device'], JSON_PRETTY_PRINT);

		if (isset ($_POST['atDeviceValue'])) {
			$subDeviceId = $_POST['atDeviceValue'];
			$subDeviceValue = $_POST['atDeviceValueInt'];
			$subDevice = SubDeviceManager::getSubDevice($subDeviceId);
			$subDeviceMaster = SubDeviceManager::getSubDeviceMaster($subDeviceId,$subDevice['type']);

			$device = [
				'deviceID' => $subDeviceMaster['device_id'],
				'type'=> $subDevice['type'],
				'value'=> $subDeviceValue,
			];
		}


		$value = $_POST['atSelector'];
		if (isset($_POST['atTime'])){
				$value = $_POST['atTime'];
		} else if (isset($_POST['atDeviceValue'])) {
			$value = $device;
		}  else if ($_POST['atSelector'] == 'inHome' || $_POST['atSelector'] == 'outHome') {
			//TODO: opravit edit aby vkládal id původního uživatele
			$value = UserManager::getUserData('user_id');
		}

		$value = (isset($_POST['atTime']) ? $_POST['atTime'] : (isset($_POST['atDeviceValue']) ? $device : $_POST['atSelector']));
		$ifCode = json_encode([
			"type" => $_POST['atSelector'],
			"value" => $value,
		], JSON_PRETTY_PRINT);
		$onDays = ($_POST['day'] != '' ? json_encode($_POST['day']) : '');

		AutomationManager::create($_POST['name'], $onDays, $doCode, $ifCode, (isset ($_POST['automation_id']) ? $_POST['automation_id'] : ""));

		header('Location: ' . BASEURL . strtolower(basename(__FILE__, '.php')));
		die();
	}
}
?>
