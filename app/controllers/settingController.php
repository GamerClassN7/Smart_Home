<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['submitPasswordChange']) && $_POST['submitPasswordChange'] != "") {
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword1'];
		$newPassword2 = $_POST['newPassword2'];
		UserManager::changePassword($oldPassword, $newPassword, $newPassword2);
		header('Location: ' . BASEURL . 'logout');
		die();
	} else if (isset($_POST['submitCreateUser']) && $_POST['submitCreateUser'] != "") {
		$userName = $_POST['userName'];
		$password = $_POST['userPassword'];
		UserManager::createUser($userName, $password);
		header('Location: ' . BASEURL . 'setting');
		die();
	} else if (isset($_POST['submitEnableOta']) && $_POST['submitEnableOta'] != "") {
		echo $otaCode = $_POST['otaCode'];
		echo $otaSecret = $_POST['otaSecret'];


		$ga = new PHPGangsta_GoogleAuthenticator();
		$checkResult = $ga->verifyCode($otaSecret, $otaCode, 2);    // 2 = 2*30sec clock tolerance
		 if ($checkResult) {
			 UserManager::setOta($otaCode, $otaSecret);
		 }
		header('Location: ' . BASEURL . 'setting');
		die();
	}
}
