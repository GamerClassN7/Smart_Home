<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['saveDevice']) && $_POST['saveDevice'] != "") {
		$deviceId = $_POST['deviceId'];
		$deviceName = $_POST['deviceName'];
		$deviceIcon = $_POST['deviceIcon'];
		$sleepTime = 0;
		if (isset($_FILES['deviceFirmware']) && isset($_FILES['deviceFirmware']['tmp_name']) && $_FILES['deviceFirmware']['tmp_name'] != "") {
			$file = $_FILES['deviceFirmware'];
			$fileName = (isset ($_POST['deviceMac']) ? str_replace(":", "", $_POST['deviceMac']) . ".bin" : "");
			if (file_exists("./app/updater/" . $fileName)) {
				unlink("./app/updater/" . $fileName);
			}
			if ($fileName != "") {
				copy($file['tmp_name'], "./app/updater/" . $fileName);
			} else {

			}
		}

		if (isset($_POST['sleepTime'])) {
			$sleepTime = $_POST['sleepTime'];
		}
		//TODO: if device isnt on off
		$permissionsInJson = json_encode([
			(int) $_POST['permissionOwner'],
			(int) $_POST['permissionOther'],
		]);


		$deviceOwnerUserId = $_POST['deviceOwnerUserId'];
		$deviceOwnerRoomId = $_POST['deviceOwnerId'];

		try {
			$args = array(
				'owner' => $deviceOwnerUserId,
				'name' => $deviceName,
				'icon' => $deviceIcon,
				'permission' => $permissionsInJson,
				'sleep_time' => $sleepTime,
				'room_id' => $deviceOwnerRoomId,
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
			echo '<a href="' . BASEDIR .'">CONTINUE</a>';
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
		echo '<a href="' . BASEDIR . '">CONTINUE</a>';
		die();
	}
	header('Location: ' . BASEDIR );
	die();
}
?>
