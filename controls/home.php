<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['saveDevice']) && $_POST['saveDevice'] != "") {
		$deviceId = $_POST['deviceId'];
		$deviceName = $_POST['deviceName'];
		$deviceIcon = $_POST['deviceIcon'];
		$sleepTime = $_POST['sleepTime'];
		//TODO: if device isnt on off
		$permissionsInJson = json_encode([
			(int) $_POST['permissionOwner'],
			(int) $_POST['permissionOther'],
		]);


		$deviceOwnerUserId = $_POST['deviceOwnerUserId'];
		try {
			$args = array(
				'owner' => $deviceOwnerUserId,
				'name' => $deviceName,
				'icon' => $deviceIcon,
				'permission' => $permissionsInJson,
				'sleep_time' => $sleepTime
			);
			DeviceManager::edit($deviceId, $args);
		} catch (\Exception $e) {
			echo $e->message();

		}



		//Debug
		if (DEBUGMOD == 1) {
			echo '<pre>';
			echo $permissionsInJson;
			echo $deviceId;
			var_dump(json_decode ($permissionsInJson));
			echo '</pre>';
			echo '<a href="/vasek/home/">CONTINUE</a>';
			die();
		}
	} else if (isset($_POST['approveDevice'])) {
		$deviceId = $_POST['deviceId'];
		$args = array(
			'approved' => 1,
		);
		DeviceManager::edit($deviceId, $args);
	} else if (isset($_POST['disableDevice'])) {
		$deviceId = $_POST['deviceId'];
		$args = array(
			'approved' => 2,
		);
		DeviceManager::edit($deviceId, $args);
	}

	//Debug
	if (DEBUGMOD == 1) {
		echo '<pre>';
		var_dump($POST);
		echo '</pre>';
		echo '<a href="/vasek/home/">CONTINUE</a>';
		die();
	}
	header('Location: /vasek/home/', TRUE);
	die();
}
?>
