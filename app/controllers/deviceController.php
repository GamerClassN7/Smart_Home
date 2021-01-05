<?php
if (!empty ($_POST)){
	$deviceManager = new DeviceManager ();
	$subDeviceManager = new SubDeviceManager ();
	if (!empty ($_FILES['deviceFirmware']) && !empty ($_FILES['deviceFirmware']['tmp_name']) && !empty ($_POST['deviceId'])) {
		$file = $_FILES['deviceFirmware'];
		$deviceMac = $deviceManager->getDeviceById ($_POST['deviceId'])['mac'];
		$fileName = (!empty ($deviceMac) ? str_replace (":", "", $deviceMac) . ".bin" : "");

		if ($fileName != "" && file_exists ("../updater/" . $fileName)) {
			unlink("../updater/" . $fileName);
		}
		if ($fileName != "") {
			copy ($file['tmp_name'], "../updater/" . $fileName);
		}
	}
	if (isset ($_POST['deviceCommand'])  && !empty ($_POST['deviceId'])) {
		$deviceManager->edit ($_POST['deviceId'], array ('command' => $_POST['deviceCommand']));
	} else if (!empty ($_POST['deviceCommand'])) {
		$devices = $deviceManager->getAllDevices();
		foreach ($devices as $key => $device) {
			$deviceManager->edit ($device['device_id'], array ('command' => $_POST['deviceCommand']));
		}
	}
	if (!empty ($_POST['deviceRoomId'])  && !empty ($_POST['deviceId'])) {
		$deviceManager->edit ($_POST['deviceId'], array ('room_id' => $_POST['deviceRoomId']));
	}
	if (!empty ($_POST['deviceName'])  && !empty ($_POST['deviceId'])) {
		$deviceManager->edit ($_POST['deviceId'], array ('name' => $_POST['deviceName']));
	}
	if (isset ($_POST['deviceHistory']) && !empty ($_POST['deviceId'])) {
		$subDeviceManager->editSubDevicesByDevice($_POST['deviceId'], array ('history' => $_POST['deviceHistory']));
	}
	header('Location: ' . BASEURL . str_replace(BASEDIR, "", $_SERVER['REQUEST_URI']));
	die();
}
