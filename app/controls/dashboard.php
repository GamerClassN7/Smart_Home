<?php
if (isset($_POST) && !empty($_POST)){

	if (isset($_POST['modalFinal']) && $_POST['modalFinal'] == "Next") {
		$subDeviceIds = $_POST['devices'];
		foreach ($subDeviceIds as $subDeviceId) {
			DashboardManager::Add($subDeviceId);
		}
	}
	header('Location: /vasek/home/' . strtolower(basename(__FILE__, '.php')));
	die();
}
?>
