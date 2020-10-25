<?php
$userManager = new UserManager();


if (
	isset($_POST['username']) &&
	$_POST['username'] != '' &&
	isset($_POST['password']) &&
	$_POST['password'] != ''
){
	$ota = false;
	$userName = $_POST['username'];
	$userPassword = $_POST['password'];
	$rememberMe = (isset ($_POST['remember']) ? $_POST['remember'] : "");
	$ota = $userManager->haveOtaEnabled($userName);
	if ($ota == "") {
		$landingPage = $userManager->login($userName, $userPassword, $rememberMe);
		header('Location: ' . BASEURL . $landingPage);
		die();
	}

	$_SESSION['USERNAME'] = $userName;
	$_SESSION['PASSWORD'] = $userPassword;
	$_SESSION['REMEMBER'] = $rememberMe;
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
	$rememberMe = $_SESSION['REMEMBER'];
	unset($_SESSION['OTA']);
	$checkResult = $ga->verifyCode($otaSecret, $otaCode, 2);    // 2 = 2*30sec clock tolerance
	if ($checkResult) {
		$landingPage = $userManager->login($userName, $userPassword, $rememberMe);
		header('Location: ' . BASEURL . '/');
		echo 'OK';
	} else {
		echo 'FAILED';
	}
	//TODO: upravi a ověřit jeslti ja zabezpečené
	//TODO:
	die();
}
