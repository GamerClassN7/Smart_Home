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
	echo $_POST['username'];
	$userPassword = $_POST['password'];
	$state = $_POST["state"];
	$clientId = $_POST["clientId"];
	$ota = $userManager->haveOtaEnabled($userName);
	if ($ota == "") {
		$token = (new AuthManager)->getToken($userName,$userPassword, $clientId);
		if (!$token) {
			throw new Exception("Auth failed", 401);
		}

		$get = [
			"access_token"=>$token,
			"token_type"=>"Bearer",
			"state"=>$state,
		];

		header('Location: ' . $_POST["redirectUrl"] . '#' . http_build_query($get));
		die();
	}

	$_SESSION['USERNAME'] = $userName;
	$_SESSION['PASSWORD'] = $userPassword;
	$_SESSION['OTA'] = $ota;
	$_SESSION['STATE'] = $state;
	$_SESSION['REDIRECT'] = $_POST["redirectUrl"];
	$_SESSION['CLIENT'] = $clientId;


} else if (
	isset($_POST['otaCode']) &&
	$_POST['otaCode'] != ''
) {
	$otaCode = $_POST['otaCode'];
	$otaSecret = $_POST['otaSecret'];

	$userName = $_SESSION['USERNAME'];
	$userPassword = $_SESSION['PASSWORD'];
	$ota = $_SESSION['OTA'];
	$oauthState = $_SESSION['STATE'];
	$oauthRedirect = $_SESSION['REDIRECT'];
	$oauthClientId = $_SESSION['CLIENT'];

	$ga = new PHPGangsta_GoogleAuthenticator();
	$checkResult = $ga->verifyCode($otaSecret, $otaCode, 2);    // 2 = 2*30sec clock tolerance
	if ($checkResult) {
		$token = (new AuthManager)->getToken($userName,$userPassword, $oauthClientId);
		if (!$token) {
			throw new Exception("Auth failed", 401);
		}

		$get = [
			"access_token"=>$token,
			"token_type"=>"Bearer",
			"state"=>$oauthState,
		];

		header('Location: ' . $oauthRedirect . '#' . http_build_query($get));
		echo 'OK';
	} else {
		echo 'FAILED';
	}
	die();
}
