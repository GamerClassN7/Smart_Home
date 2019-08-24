<?php
/** Includes **/
include_once('./config.php');

//Autoloader
foreach (["class", "views"] as $dir) {
	$files = scandir('./'.$dir.'/');
	$files = array_diff($files, array('.', '..'));
	foreach ($files as $file) {
		include_once './'.$dir.'/'.  $file;
	}
}

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//Filtrování IP adress
/*if (DEBUGMOD != 1) {
if (!in_array($_SERVER['REMOTE_ADDR'], HOMEIP)) {
echo json_encode(array(
'state' => 'unsuccess',
'errorMSG' => "Using API from your IP insn´t alowed!",
));
header("HTTP/1.1 401 Unauthorized");
exit();
}
}*/



//Read API data
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

if (isset($obj['user']) && $obj['user'] != ''){
	//user at home
	$user = UserManager::getUser($obj['user']);
	$userId = $user['user_id'];
	UserManager::atHome($userId, $obj['location']);
	die();
}

//automationExecution
AutomationManager::executeAll();

//Record Cleaning
RecordManager::clean(RECORDTIMOUT);

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
	DeviceManager::create($token, $token);
	header("HTTP/1.1 401 Unauthorized");
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Device not registeret",
	));
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
		die();
	} else {
		//Vypis
		//TODO: doděla uložení výpisu jinými slovy zda li byl comman vykonán
		$device = DeviceManager::getDeviceByToken($token);
		$deviceId = $device['device_id'];

		if (count(SubDeviceManager::getAllSubDevices($deviceId)) == 0) {
			SubDeviceManager::create($deviceId, 'on/off', UNITS[$key]);
			RecordManager::create($deviceId, 'on/off', 0);
		}

		$subDeviceId = SubDeviceManager::getAllSubDevices($deviceId)[0]['subdevice_id'];

		$subDeviceLastReord = RecordManager::getLastRecord($subDeviceId);
		$subDeviceLastReordValue = $subDeviceLastReord['value'];

		RecordManager::setExecuted($subDeviceLastReord['record_id']);

		echo json_encode(array(
			'device' => [
				'hostname' => $device['name'],
				'sleepTime' => $device['sleep_time'],
				],
				'state' => 'succes',
				'value' => $subDeviceLastReordValue
			));
			header("HTTP/1.1 200 OK");
			die();
		}
