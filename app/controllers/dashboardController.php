<?php
if (isset($_POST) && !empty($_POST)){

	if (isset($_POST['modalFinal']) && $_POST['modalFinal'] != "") {
		$subDeviceIds = $_POST['devices'];
		foreach ($subDeviceIds as $subDeviceId) {
			DashboardManager::Add($subDeviceId);
		}
	}
	header('Location: ' . BASEURL . strtolower(basename(__FILE__, '.php')));
	die();
}
?>
