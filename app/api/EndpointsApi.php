<?php
class EndpointsApi extends ApiController{
	public function default(){
		// $this->requireAuth();
		$obj = $this->input;

		//variables Definition
		$command = "null";

		//Log
		$logManager = new LogManager();
		$apiLogManager = new LogManager('../logs//api/'. date("Y-m-d").'.log');

		//Token Checks
		if ($obj['token'] == null || !isset($obj['token'])) {
			$this->response([
				'state' => 'unsuccess',
				'errorMSG' => "Missing Value Token in JSON payload",
			], 401);
			die();
		}

		//Vstupní Checky
		if (!DeviceManager::registeret($obj['token'])) {
			//Notification data setup
			$notificationMng = new NotificationManager;
			$notificationData = [
				'title' => 'Info',
				'body' => 'New device Detected Found',
				'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
			];

			//Subdevice Registration
			$deviceId = DeviceManager::create($obj['token'], $obj['token']);
			foreach ($obj['values'] as $key => $value) {
				if (!SubDeviceManager::getSubDeviceByMaster($deviceId, $key)) {
					SubDeviceManager::create($deviceId, $key, UNITS[$key]);
				}
			}

			//Notification for newly added Device
			if ($notificationData != []) {
				$subscribers = $notificationMng::getSubscription();
				foreach ($subscribers as $key => $subscriber) {
					$logManager->write("[NOTIFICATION] SENDING TO" . $subscriber['id'] . " ", LogRecordType::INFO);
					$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
				}
			}

			$logManager->write("[API] Registering Device", LogRecordType::INFO);
			$this->response([
				'state' => 'unsuccess',
				'errorMSG' => "Device not registeret",
			], 401);
		}

		if (!DeviceManager::approved($obj['token'])) {
			$this->response([
				'state' => 'unsuccess',
				'errorMSG' => "Unaproved Device",
			], 401);
		}

		//Diagnostic/Log Data Save
		if (isset($obj['settings'])){
			$data = ['mac' => $obj['settings']["network"]["mac"], 'ip_address' => $obj['settings']["network"]["ip"]];
			if (array_key_exists("firmware_hash", $obj['settings'])) {
				$data['firmware_hash'] = $obj['settings']["firmware_hash"];
			}
			DeviceManager::editByToken($obj['token'], $data);
			$this->response([
				'state' => 'succes',
				'command' => $command,
			], 200);
		}

		// Issuing command
		if ($command == "null"){
			$device = DeviceManager::getDeviceByToken($obj['token']);
			$deviceId = $device['device_id'];
			$deviceCommand = $device["command"];
			if ($deviceCommand != '' && $deviceCommand != null && $deviceCommand != "null")
			{
				$command = $deviceCommand;
				$data = [
					'command'=>'null'
				];
				DeviceManager::editByToken($obj['token'], $data);
				$logManager->write("[API] Device_ID " . $deviceId . " executing command " . $command, LogRecordType::INFO);
			}
		}

		$jsonAnswer = [];
		$subDeviceLastReordValue = [];
		$device = DeviceManager::getDeviceByToken($obj['token']);
		$deviceId = $device['device_id'];

		if (isset($obj['values'])) {
			//ZAPIS
			foreach ($obj['values'] as $key => $value) {
				if (!SubDeviceManager::getSubDeviceByMaster($deviceId, $key)) {
					SubDeviceManager::create($deviceId, $key, UNITS[$key]);
				}
				$subDeviceLastReordValue[$key] = $value['value'];
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


			//upravit format na setings-> netvork etc

			$subDevicesTypeList = SubDeviceManager::getSubDeviceSTypeForMater($deviceId);
			if (!in_array($subDevicesTypeList, ['on/off', 'door', 'water'])) {
				$jsonAnswer['device']['sleepTime'] = $device['sleep_time'];
			}
		} else {
			if (count(SubDeviceManager::getAllSubDevices($deviceId)) == 0) {
				SubDeviceManager::create($deviceId, 'on/off', UNITS[$key]);
				//RecordManager::create($deviceId, 'on/off', 0);
			}

			$subDevicesData = SubDeviceManager::getAllSubDevices($deviceId);

			foreach ($subDevicesData as $key => $subDeviceData) {
				$subDeviceId = $subDeviceData['subdevice_id'];
				$subDeviceLastReord = RecordManager::getLastRecord($subDeviceId);
				$subDeviceLastReordValue[$subDeviceData['type']] = $subDeviceLastReord['value'];

				if ($subDeviceLastReord['execuded'] == 0){
					$logManager->write("[API] subDevice_ID ".$subDeviceId . " executed comand with value " . json_encode($subDeviceLastReordValue) ." executed " . $subDeviceLastReord['execuded'], LogRecordType::INFO);
					RecordManager::setExecuted($subDeviceLastReord['record_id']);
				}
			}
		}

		$hostname = "";
		$hostname = strtolower($device['name']);
		$hostname = str_replace(' ', '_', $hostname);

		$jsonAnswer['device']['hostname'] = $hostname;
		$jsonAnswer['state'] = 'succes';
		$jsonAnswer['values'] = $subDeviceLastReordValue;
		$jsonAnswer['command'] = $command;

		$this->response($jsonAnswer);
		// this method returns response as json
	}

	private function sendFile($path)	{
		header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK', true, 200);
		header('Content-Type: application/octet-stream', true);
		header('Content-Disposition: attachment; filename=' . basename($path));
		header('Content-Length: ' . filesize($path), true);
		header('x-MD5: ' . md5_file($path), true);
		readfile($path);
	}

	public function update() {
		$logManager = new LogManager();

		header('Content-type: text/plain; charset=utf8', true);

		//Filtrování IP adress
		if (DEBUGMOD != 1) {
			if (!in_array($_SERVER['REMOTE_ADDR'], HOMEIP)) {
				echo json_encode(array(
					'state' => 'unsuccess',
					'errorMSG' => "Using API from your IP insnt alowed!",
				));
				header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
				$logManager->write("[Updater] acces denied from " . $_SERVER['REMOTE_ADDR'], LogRecordType::WARNING);
				exit();
			}
		}

		$macAddress = $_SERVER['HTTP_X_ESP8266_STA_MAC'];
		$localBinary = "./app/updater/" . str_replace(':', '', $macAddress) . ".bin";
		$logManager->write("[Updater] url: " . $localBinary, LogRecordType::INFO);
		$logManager->write("[Updater] version: " . $_SERVER['HTTP_X_ESP8266_SKETCH_MD5'], LogRecordType::INFO);
		if (file_exists($localBinary)) {
			$logManager->write("[Updater] version PHP: \n" . md5_file($localBinary), LogRecordType::INFO);
			if ($_SERVER['HTTP_X_ESP8266_SKETCH_MD5'] != md5_file($localBinary)) {
				sendFile($localBinary);
				//get device data
				$device = DeviceManager::getDeviceByMac($macAddress);
				$deviceName = $device['name'];
				$deviceId = $device['device_id'];
				//logfile write
				$logManager->write("[Device] device_ID " . $deviceId . " was just updated to new version", LogRecordType::WARNING);
				$logManager->write("[Device] version hash: \n" . md5_file($localBinary), LogRecordType::INFO);
				//notification
				$notificationMng = new NotificationManager;
				$notificationData = [
					'title' => 'Info',
					'body' => $deviceName.' was just updated to new version',
					'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
				];
				if ($notificationData != []) {
					$subscribers = $notificationMng->getSubscription();
					foreach ($subscribers as $key => $subscriber) {
						$logManager->write("[NOTIFICATION] SENDING TO " . $subscriber['id'] . " ", LogRecordType::INFO);
						$answer = $notificationMng->sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
					}
				}
			} else {
				header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
			}
		} else {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		}
		die();
	}
}
