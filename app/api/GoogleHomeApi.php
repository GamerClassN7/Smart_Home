<?php
class GoogleHomeApi {
	public static function response()
	{
		set_time_limit (20);
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');

		header('Content-Type: application/json');

		switch ($obj['inputs'][0]['intent']) {
			case 'action.devices.SYNC':
			self::sync($obj['requestId']);
			$apiLogManager->write("[Google Home] action.devices.SYNC", LogRecordType::INFO);
			break;

			case 'action.devices.QUERY':
			self::query($obj['requestId'], $obj['inputs'][0]['payload']);
			$apiLogManager->write("[Google Home] action.devices.QUERY", LogRecordType::INFO);
			break;


			case 'action.devices.EXECUTE':
			self::execute($obj['requestId'], $obj['inputs'][0]['payload']);
			$apiLogManager->write("[Google Home] action.devices.EXECUTE", LogRecordType::INFO);
			break;
		}
	}

	static function query($requestId, $payload){
		$devices = [];
		foreach ($payload['devices'] as $deviceId) {
			$subDeviceData = SubDeviceManager::getSubDevice($deviceId['id']);
			if ($subDeviceData['type'] != "on/off" && $subDeviceData['type'] != "temp_cont") continue;

			$state = false;
			if (RecordManager::getLastRecord($deviceId['id'])['value'] == 1){
				$state = true;
			}

			$online = false;
			$status = 'OFFLINE';
			if (RecordManager::getLastRecord($deviceId['id'])['execuded'] == 1){
				$online = true;
				$status = 'SUCCESS';
			}

			$tempDevice = [
				$deviceId['id'] => [
					'online' => $online,
					'status'=> $status,
				]
			];

			if ($subDeviceData['type'] == "temp_cont"){
				$tempDevice[$deviceId['id']]['thermostatMode'] = 'heat';
				$tempDevice[$deviceId['id']]['thermostatTemperatureAmbient'] = RecordManager::getLastRecord($deviceId['id'])['value'];
				$tempDevice[$deviceId['id']]['thermostatTemperatureSetpoint'] = RecordManager::getLastRecord($deviceId['id'])['value'];
			} else {
					$tempDevice[$deviceId['id']]['on'] = $state;
			}
			$devices = $tempDevice;
			if (count($devices)> 1){
				$devices[] = $tempDevice;
			}
		}


		$response = [
			'requestId' => $requestId,
			'payload' => [
				'devices' => $devices,
			],
		];

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
		echo json_encode($response);
	}
	static function sync($requestId){
		$devices = [];

		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomData) {
			$devicesData = DeviceManager::getAllDevicesInRoom($roomData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {
				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
					if ($subDeviceData['type'] != "on/off" && $subDeviceData['type'] != "temp_cont") continue;

					$tempDevice = [
						'id' => (string) $subDeviceData['subdevice_id'],
						'type' => GoogleHomeDeviceTypes::getEquivalent($subDeviceData['type']),
						'name' => [
							'name' => $deviceData['name'],
						],
						'willReportState' => false,
						'roomHint' => $roomData['name']
					];

					//traids
					switch ($subDeviceData['type']) {
						case 'on/off':
						$tempDevice['traits'] = [ 'action.devices.traits.OnOff' ];
						break;

						case 'temp_cont':
						$tempDevice['attributes'] = [
							"availableThermostatModes" => "off,heat,on",
							"thermostatTemperatureRange" => [
								'minThresholdCelsius' => 5,
								'maxThresholdCelsius' => 15,
							],
							"thermostatTemperatureUnit" => "C",
							"commandOnlyTemperatureSetting" => false,
							"queryOnlyTemperatureSetting" => false,
							"bufferRangeCelsius" => 0,
						];
						$tempDevice['traits'] = [
							'action.devices.traits.TemperatureSetting',
						];
						break;
					}


						$devices[] = $tempDevice;

				}
			}


		}


		$response = [
			'requestId' => $requestId,
			'payload' => [
				'agentUserId'=>'651351531531',
				'devices' => $devices,
			],
		];
		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
		echo json_encode($response);
	}

	static function execute($requestId, $payload){
		$commands = [];

		foreach ($payload['commands'] as $key => $command) {
			foreach ($command['devices'] as $key => $device) {
				$executionCommand = $command['execution'][0];
				if (isset($command['execution'][$key])) {
					$executionCommand = $command['execution'][$key];
				}

				$subDeviceId = $device['id'];

				switch ($executionCommand['command']) {
					case 'action.devices.commands.OnOff':
					$value = 0;
					if ($executionCommand['params']['on']) $value = 1;

					RecordManager::createWithSubId($subDeviceId, $value);

					$timeout = 0;
					while(RecordManager::getLastRecord($subDeviceId)['execuded'] == 0 && $timeout < 5 ){
						sleep(1);
						$timeout++;
					}

					$commandTemp = [
						'ids' => [$subDeviceId],
						'status' => 'SUCCESS',
						'states' => [
							'on' => $executionCommand['params']['on'],
						],
					];

					if ($timeout >= 5){
						$commandTemp['status'] = "OFFLINE";
					}
					$commands[] = $commandTemp;

					break;

					case 'action.devices.commands.ThermostatTemperatureSetpoint':
					$value = 0;
					if (isset($executionCommand['params']['thermostatTemperatureSetpoint'])) $value = $executionCommand['params']['thermostatTemperatureSetpoint'];

					RecordManager::createWithSubId($subDeviceId, $value);

					$timeout = 0;
					while(RecordManager::getLastRecord($subDeviceId)['execuded'] == 0 && $timeout < 5 ){
						sleep(1);
						$timeout++;
					}

					$commandTemp = [
						'ids' => [$subDeviceId],
						'status' => 'SUCCESS',
						'states' => [
							'thermostatMode' => 'heat',
							'thermostatTemperatureSetpoint' => $value,
							'thermostatTemperatureAmbient' => $value,
						],
					];

					if ($timeout >= 5){
						$commandTemp['status'] = "OFFLINE";
					}
					$commands[] = $commandTemp;

					break;
				}
			}
		}

		$response = [
			'requestId' => $requestId,
			'payload' => [
				'commands' => $commands,
			],
		];
		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);

		echo json_encode($response);
	}

	function autorize(){
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);

		$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordType::INFO);

		//tel: zemanová 607979429

		/*echo json_encode(array (
		'access_token' => '',
		'token_type' => 'bearer',
		'expires_in' => 3600,
		'refresh_token' => '',
		'scope' => 'create',
	));*/

	$get = [
		"access_token"=>"23165133",
		"token_type"=>"Bearer",
		"state"=>$_GET["state"],
	];

	echo $_GET["redirect_uri"] . '#' . http_build_query($get) ;
	echo '<a href="'.$_GET["redirect_uri"] . '#' . http_build_query($get) . '">FINISH</a>';
}
}
