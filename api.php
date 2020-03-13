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

if (defined(DEBUGMOD) && DEBUGMOD == 1) {
	$logManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
}

//zabespecit proti Ddosu
if (isset($obj['user']) && $obj['user'] != ''){
	//user at home
	$user = UserManager::getUser($obj['user']);
	if (!empty($user)) {
		$userId = $user['user_id'];
		$atHome = $obj['atHome'];
		UserManager::atHome($userId, $atHome);
		$logManager->write("[Record] user " . $userId . " changet his home state to " . $atHome . " " . RECORDTIMOUT , LogRecordType::INFO);
		echo 'Saved: ' . $atHome;
		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
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
		header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
		$logManager->write("[API] acces denied from " . $_SERVER['REMOTE_ADDR'], LogRecordType::WARNING);
		exit();
	}
}

//automationExecution
try {
	AutomationManager::executeAll();
	$fallbackManager = new FallbackManager(RANGES);
	$fallbackManager->check();
	//LogKeeper::purge(LOGTIMOUT);
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
$settings = null;
$command = "null";

if (isset($obj['values'])) {
	$values = $obj['values'];
}

if (isset($obj['settings'])) {
	$settings = $obj['settings'];
}

//Checks
if ($token == null || $token == "") {
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Missing Value Token in JSON payload",
	));
	header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
	die();
}

//Vstupní Checky
if (!DeviceManager::registeret($token)) {
	$notificationMng = new NotificationManager;
	$notificationData = [];
	$notificationData = [
		'title' => 'Info',
		'body' => 'New device Detected Found',
		'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
	];
	$deviceId = DeviceManager::create($token, $token);
	foreach ($values as $key => $value) {
		if (!SubDeviceManager::getSubDeviceByMaster($deviceId, $key)) {
			SubDeviceManager::create($deviceId, $key, UNITS[$key]);
		}

		if ($notificationData != []) {
			$subscribers = $notificationMng::getSubscription();
			foreach ($subscribers as $key => $subscriber) {
				$logManager->write("[NOTIFICATION] SENDING TO" . $subscriber['id'] . " ", LogRecordType::INFO);
				$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
			}
		}
	}

	//Notification for newly added Device
	$subscribers = $notificationMng::getSubscription();
	foreach ($subscribers as $key => $subscriber) {
		$logManager->write("[NOTIFICATION] SENDING TO" . $subscriber['id'] . " ", LogRecordType::INFO);
		$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
	}

	header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Device not registeret",
	));
	$logManager->write("[API] Registering Device", LogRecordType::INFO);
	exit();
}

if (!DeviceManager::approved($token)) {
	header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
	echo json_encode(array(
		'state' => 'unsuccess',
		'errorMSG' => "Unaproved Device",
	));
	exit();
}

// Diagnostic Data Write to DB
if ($settings != null || $settings != ""){
	$data = ['mac' => $settings["network"]["mac"], 'ip_address' => $settings["network"]["ip"]];
	if (array_key_exists("firmware_hash", $settings)) {
		$data['firmware_hash'] = $settings["firmware_hash"];
	}
	DeviceManager::editByToken($token, $data);
}

// Issuing command
if ($command == "null"){
	$device = DeviceManager::getDeviceByToken($token);
	$deviceId = $device['device_id'];
	$deviceCommand = $device["command"];
	if ($deviceCommand != '' || $deviceCommand != null)
	{
		$command = $deviceCommand;
	} 

	$data = [
		'command'=>'null'
	];
	DeviceManager::editByToken($token, $data);
	$logManager->write("[API] Device_ID " . $deviceId . " executing command " . $command, LogRecordType::INFO);
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
		RecordManager::create($deviceId, $key, round($value['value'],3));
		$logManager->write("[API] Device_ID " . $deviceId . " writed value " . $key . ' ' . $value['value'], LogRecordType::INFO);

		//notification
		if ($key == 'door' || $key == 'water') {
			$notificationMng = new NotificationManager;
			$notificationData = [];

			switch ($key) {
				case 'door':
					$notificationData = [
						'title' => 'Info',
						'body' => 'Someone just open up '.$device['name'],
						'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
					];

				break;
				case 'water':
					$notificationData = [
						'title' => 'Alert',
						'body' => 'Wather leak detected by '.$device['name'],
						'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
					];
				break;
			}
			if (DEBUGMOD) $notificationData['body'] .= ' value='.$value['value'];
			if ($notificationData != []) {
				$subscribers = $notificationMng::getSubscription();
				foreach ($subscribers as $key => $subscriber) {
					$logManager->write("[NOTIFICATION] SENDING TO" . $subscriber['id'] . " ", LogRecordType::INFO);
					$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
				}
			}
		}
	}

	$hostname = strtolower($device['name']);
	$hostname = str_replace(' ', '_', $hostname);
	//upravit format na setings-> netvork etc
	$jsonAnswer = [
		'device' => [
			'hostname' => $hostname,
			'ipAddress' => $device['ip_address'],
			'subnet' => $device['subnet'],
			'gateway' => $device['gateway'],
		],
		'state' => 'succes',
		'command' => $command,
	];

	$subDevicesTypeList = SubDeviceManager::getSubDeviceSTypeForMater($deviceId);
	if (!in_array($subDevicesTypeList, ['on/off', 'door', 'water'])) {
		$jsonAnswer['device']['sleepTime'] = $device['sleep_time'];
	}
	echo json_encode($jsonAnswer);
	header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
} else {
	//Vypis
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
			'ipAddress' => $device['ip_address'],
			'subnet' => $device['subnet'],
			'gateway' => $device['gateway'],
		],
		'state' => 'succes',
		'value' => $subDeviceLastReordValue,
		'command' => $command,
	));
	header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
}

unset($logManager);
Db::disconect();
die();
