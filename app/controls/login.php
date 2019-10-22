<?php
global $userManager;


if (
	isset($_POST['username']) &&
	$_POST['username'] != '' &&
	isset($_POST['password']) &&
	$_POST['password'] != ''
){
	$ota = false;
	$userName = $_POST['username'];
	$userPassword = $_POST['password'];
	$ota = $userManager->haveOtaEnabled($userName);

	$_SESSION['USERNAME'] = $userName;
	$_SESSION['PASSWORD'] = $userPassword;
	$_SESSION['OTA'] = $ota;
} else if (
	isset($_POST['otaCode']) &&
	$_POST['otaCode'] != ''
) {

	$otaCode = $_POST['otaCode'];
	$otaSecret = $_POST['otaSecret'];

	$ga = new PHPGangsta_GoogleAuthenticator();
	$ota = $_SESSION['OTA'];
	$userName = $_SESSION['USERNAME'];
	$userPassword = $_SESSION['PASSWORD'];
	unset($_SESSION['OTA']);
	$checkResult = $ga->verifyCode($otaSecret, $otaCode, 6);    // 2 = 2*30sec clock tolerance
	if ($checkResult) {
		$landingPage = $userManager->login($userName, $userPassword);
		header('Location: ' . BASEDIR . $landingPage);
		echo 'OK';
	} else {
		echo 'FAILED';
	}
	//TODO: upravi a ověřit jeslti ja zabezpečené
	//TODO:
	die();
}
