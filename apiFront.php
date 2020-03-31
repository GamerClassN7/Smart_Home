<?php
/** Includes **/
include_once('./config.php');

//Autoloader
$files = scandir('./app/class/');
$files = array_diff($files, array(
	'.',
	'..',
	'app',
	'ChartJS.php',
	'ChartJS_Line.php',
	'ChartManager.php',
	'DashboardManager.php',
	'Partial.php',
	'Form.php',
	'Route.php',
	'Template.php',
	'Ajax.php',
));

foreach($files as $file) {
	include './app/class/'.  $file;
}

//Log
$apiLogManager = new LogManager('./app/logs/apiFront/'. date("Y-m-d").'.log');

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//Read API data
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

//Log RAW api request
if (API_DEBUGMOD == 1) {
	$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
}

$apiManager = new ApiManager();
echo $apiManager->generateToken($obj['username'],$obj['password']);
die();

/*
if (
	isset($obj['username']) &&
	$obj['username'] != '' &&
	isset($obj['password']) &&
	$obj['password'] != ''
){
	$ota = false;
	$userName = $_POST['username'];
	$userPassword = $_POST['password'];
	$rememberMe = (isset ($_POST['remember']) ? $_POST['remember'] : "");
	$ota = $userManager->haveOtaEnabled($userName);
	if ($ota == "") {
		$landingPage = $userManager->login($userName, $userPassword, $rememberMe);
		header('Location: ' . BASEDIR . $landingPage);
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
		header('Location: ' . BASEDIR . $landingPage);
		echo 'OK';
	} else {
		echo 'FAILED';
	}
	//TODO: upravi a ověřit jeslti ja zabezpečené
	//TODO:
	die();
}
*/


/*unset($logManager);
Db::disconect();
die();*/
