<?php
if (!empty ($_POST)){
	if (!empty ($_FILES['deviceFirmware']) && !empty ($_FILES['deviceFirmware']['tmp_name']) && !empty ($_POST['deviceId'])) {
		$deviceManager = new DeviceManager ();
		$file = $_FILES['deviceFirmware'];
		$deviceMac = $deviceManager->getDeviceById ($_POST['deviceId'])['mac'];
		$fileName = (!empty ($deviceMac) ? str_replace (":", "", $deviceMac) . ".bin" : "");
		if ($fileName != "" && file_exists ("../app/updater/" . $fileName)) {
			unlink("../app/updater/" . $fileName);
		}
		if ($fileName != "") {
			copy ($file['tmp_name'], "../app/updater/" . $fileName);
		} else {

		}
	}
	if (isset ($_POST['deviceCommand'])  && !empty ($_POST['deviceId'])) {
		$deviceManager = new DeviceManager ();
		$deviceManager->edit ($_POST['deviceId'], array ('command' => $_POST['deviceCommand']));
	}
	header('Location: ./');
	die();
}
