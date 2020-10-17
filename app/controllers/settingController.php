<?php
if (isset($_POST) && !empty($_POST)){
	$userManager = new UserManager();
	if (isset($_POST['submitPasswordChange']) && $_POST['submitPasswordChange'] != "") {
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword1'];
		$newPassword2 = $_POST['newPassword2'];
		$userManager->changePassword($oldPassword, $newPassword, $newPassword2);
		header('Location: ' . BASEURL . 'logout');
		die();
	} else if (isset($_POST['submitCreateUser']) && $_POST['submitCreateUser'] != "") {
		$userName = $_POST['userName'];
		$password = $_POST['userPassword'];
		$email = $_POST['userEmail'];
		$userManager->createUser($userName, $password, $email);
		header('Location: ' . BASEURL . 'setting');
		die();
	} else if (isset($_POST['submitEnableOta']) && $_POST['submitEnableOta'] != "") {
		$otaCode = $_POST['otaCode'];
		$otaSecret = $_POST['otaSecret'];

		$ga = new PHPGangsta_GoogleAuthenticator();
		$checkResult = $ga->verifyCode($otaSecret, $otaCode, 2);    // 2 = 2*30sec clock tolerance
		 if ($checkResult) {
			 $userManager->setOta($otaCode, $otaSecret);
		 }
		header('Location: ' . BASEURL . 'setting');
		die();
	} else if (isset ($_POST['userPermission']) && !empty ($_POST['userID'])) {
		$userManager->setUserDataAdmin("permission", $_POST['userPermission'], $_POST['userID']);
		header('Location: ' . BASEURL . 'setting');
		die();
	}
}
