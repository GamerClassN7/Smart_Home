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


//Allow acces only wia Curl, Ajax ETC
$restAcess = 'XMLHttpRequest' == ( $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' );
if (!$restAcess){
	header('Location: ./');
}

//Log
$logManager = new LogManager();

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//Read API data
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

//zabespecit proti Ddosu
if (isset($obj['user']) && $obj['user'] != ''){
	//user at home
	$user = UserManager::getUser($obj['user']);
	if (!empty($user)) {
		$userId = $user['user_id'];
		$atHome = $obj['atHome'];
		UserManager::atHome($userId, $atHome);
		$logManager->write("[Record] user " . $userId . "changet his home state to " . $atHome . RECORDTIMOUT , LogRecordType::WARNING);
		echo 'Saved: ' . $atHome;
		header("HTTP/1.1 200 OK");
		die();
	}
}

//Filtrování IP adress
if (DEBUGMOD != 1) {
	if (!in_array($_SERVER['REMOTE_ADDR'], HOMEIP)) {
		echo json_encode(array(
			'state' => 'unsuccess',
			'errorMSG' => "Using API from your IP insnt alowed!",
		));
		header("HTTP/1.1 401 Unauthorized");
		$logManager->write("[API] acces denied from " . $_SERVER['REMOTE_ADDR'], LogRecordType::WARNING);
		exit();
	}
}

//automationExecution
try {
	AutomationManager::executeAll();
} catch (\Exception $e) {
	$logManager->write("[Automation] Something happen during automation execution", LogRecordType::ERROR);
}

//Record Cleaning
try {
	RecordManager::clean(RECORDTIMOUT);
} catch (\Exception $e) {
	$logManager->write("[Record] cleaning record older that " . RECORDTIMOUT , LogRecordType::ERROR);

}
//Variables
$token = $obj['token'];
$values = null;

if (isset($obj['values'])) {
	$values = $obj['values'];
}

//Checks
if ($token == null || $token == "") {
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Missing Value Token in JSON payload",
	));
	header("HTTP/1.1 401 Unauthorized");
	die();
}

//Vstupní Checky
if (!DeviceManager::registeret($token)) {
	$deviceId = DeviceManager::create($token, $token);
	foreach ($values as $key => $value) {
		if (!SubDeviceManager::getSubDeviceByMaster($deviceId, $key)) {
			SubDeviceManager::create($deviceId, $key, UNITS[$key]);
		}
	}
	header("HTTP/1.1 401 Unauthorized");
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Device not registeret",
	));
	$logManager->write("[API] Registering Device", LogRecordType::INFO);
	exit();
}

if (!DeviceManager::approved($token)) {
	header("HTTP/1.1 401 Unauthorized");
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Unaproved Device",
	));
	exit();
}

// Subdevices first data!
if ($values != null || $values != "") {

	//ZAPIS
	$device = DeviceManager::getDeviceByToken($token);
	$deviceId = $device['device_id'];
	foreach ($values as $key => $value) {
		if (!SubDeviceManager::getSubDeviceByMaster($deviceId, $key)) {
			SubDeviceManager::create($deviceId, $key, UNITS[$key]);
		}
		RecordManager::create($deviceId, $key, round($value['value'],2));
		$logManager->write("[API] Device_ID " . $deviceId . " writed value " . $key . ' ' . $value['value'], LogRecordType::INFO);
	}

	$hostname = strtolower($device['name']);
	$hostname = str_replace(' ', '_', $hostname);
	echo json_encode(array(
		'device' => [
			'hostname' => $hostname,
			'sleepTime' => $device['sleep_time'],
			],
			'state' => 'succes',
		));
		header("HTTP/1.1 200 OK");
	} else {
		//Vypis
		//TODO: doděla uložení výpisu jinými slovy zda li byl comman vykonán
		$device = DeviceManager::getDeviceByToken($token);
		$deviceId = $device['device_id'];

		if (count(SubDeviceManager::getAllSubDevices($deviceId)) == 0) {
			SubDeviceManager::create($deviceId, 'on/off', UNITS[$key]);
			//RecordManager::create($deviceId, 'on/off', 0);
		}

		$subDeviceId = SubDeviceManager::getAllSubDevices($deviceId)[0]['subdevice_id'];
		$subDeviceLastReord = RecordManager::getLastRecord($subDeviceId);
		$subDeviceLastReordValue = $subDeviceLastReord['value'];

		if ($subDeviceLastReord['execuded'] == 0){
			$logManager->write("[API] subDevice id ".$subDeviceId . " executed comand with value " .$subDeviceLastReordValue . " record id " . $subDeviceLastReord['record_id'] . " executed " . $subDeviceLastReord['execuded']);
			RecordManager::setExecuted($subDeviceLastReord['record_id']);
		}

		echo json_encode(array(
			'device' => [
				'hostname' => $device['name'],
				'sleepTime' => $device['sleep_time'],
				],
				'state' => 'succes',
				'value' => $subDeviceLastReordValue
			));
			header("HTTP/1.1 200 OK");
		}

		unset($logManager);
		Db::disconect();
		die();
