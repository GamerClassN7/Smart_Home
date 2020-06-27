<?php
class GoogleHome {
	static function sync($requestId){
		$devices = [];
		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomData) {
			$devicesData = DeviceManager::getAllDevicesInRoom($roomData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {
				$traids = [];
				$attributes = null;

				//Google Compatibile Action Type
				$actionType = GoogleHomeDeviceTypes::getAction($deviceData['type']);
				if ($actionType == "") continue;

				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
					$deviceTraid = GoogleHomeDeviceTypes::getTraid($subDeviceData['type']);
					if ($deviceTraid != "") {
						$traids[] = $deviceTraid;
					}

					$deviceAttributes = GoogleHomeDeviceTypes::getAttribute($subDeviceData['type']);
					if ($deviceAttributes != "") {
						$attributes = $deviceAttributes;
					}
				}

				$tempDevice = [
					'id' => (string) $deviceData['device_id'],
					'type' => $actionType,
					'traits' => $traids,
					'attributes' => $attributes,
					'name' => [
						'name' => $deviceData['name'],
					],

					'willReportState' => false,
					'roomHint' => $roomData['name']
				];
				if ($tempDevice['attributes'] == null) unset($tempDevice['attributes']);

				//traids & Attributes
				$devices[] = $tempDevice;
			}
		}


		$response = [
			'requestId' => $requestId,
			'payload' => [
				'agentUserId'=>'651351531531',
				'devices' => array_values( $devices ),
			],
		];

		$apiLogManager = new LogManager('../logs/google-home/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API][$requestId] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
		echo json_encode($response);
	}

	static function query($requestId, $payload){
		$devices = [];
		foreach ($payload['devices'] as $deviceId) {
			$subDevicesData = SubDeviceManager::getAllSubDevices($deviceId['id']);

			$tempDevice = [
				$deviceId['id'] => [
					'online' => false,
					'status'=> 'OFFLINE',
					]
				];

			foreach ($subDevicesData as $key => $subDeviceData) {
				$lastRecord = RecordManager::getLastRecord($subDeviceData['subdevice_id']);
				if ($lastRecord['execuded'] == 1){
					$tempDevice[$deviceId['id']]['online'] = true;
					$tempDevice[$deviceId['id']]['status'] = 'SUCCESS';
				} else {
					$executed = 0;
					$waiting = 0;
					foreach (RecordManager::getLastRecord($deviceId['id'], 6) as $key => $value) {
						if ($value['execuded'] == 1){
							$executed++;
						} else {
							$waiting++;
						}
					}
					if ($waiting < $executed){
						$tempDevice[$deviceId['id']]['online'] = true;
					}
				}

				switch ($subDeviceData['type']) {
					case 'temp_cont':
						$tempDevice[$deviceId['id']]['thermostatMode'] = 'off';
						if ($lastRecord['value'] != 0) {
							$tempDevice[$deviceId['id']]['thermostatMode'] = 'heat';
						}
						$tempDevice[$deviceId['id']]['thermostatTemperatureAmbient'] = $lastRecord['value'];
						$tempDevice[$deviceId['id']]['thermostatTemperatureSetpoint'] = $lastRecord['value'];
					break;
					case 'vol_cont':
						$tempDevice[$deviceId['id']]['currentVolume'] = $lastRecord['value'];
					break;
					default:
						$tempDevice[$deviceId['id']]['on'] = ($lastRecord['value'] == 1 ? true : false);
						break;
			}
		}



		if ($lastRecord['execuded'] == 1){
			$online = true;
			$status = 'SUCCESS';
		} else {
			$executed = 0;
			$waiting = 0;
			foreach (RecordManager::getLastRecord($deviceId['id'], 6) as $key => $value) {
				if ($value['execuded'] == 1){
					$executed++;
				} else {
					$waiting++;
				}
			}
			if ($waiting < $executed){
				$status = "PENDING";
				$online = true;
			}
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

	$apiLogManager = new LogManager('../logs/google-home/'. date("Y-m-d").'.log');
	$apiLogManager->write("[API][$requestId] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
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

			$deviceType = GoogleHomeDeviceTypes::getType($executionCommand['command']);
			$subDeviceId = SubDeviceManager::getSubDeviceByMasterAndType($device['id'], $deviceType)['subdevice_id'];

			switch ($executionCommand['command']) {
				case 'action.devices.commands.OnOff':
					$commands[] = self::executeSwitch($subDeviceId, $executionCommand);
				break;

				case 'action.devices.commands.ThermostatTemperatureSetpoint':
					$commands[] = self::executeTermostatValue($subDeviceId, $executionCommand);
				break;

				case 'action.devices.commands.ThermostatSetMode':
					$commands[] = self::executeTermostatMode($subDeviceId, $executionCommand);
				break;

				case 'action.devices.commands.setVolume':
					$commands[] = self::executeVolume($subDeviceId, $executionCommand);
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

	$apiLogManager = new LogManager('../logs/google-home/'. date("Y-m-d").'.log');
	$apiLogManager->write("[API][EXECUTE][$requestId]\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);

	echo json_encode($response);
}

static function executeSwitch($subDeviceId, $executionCommand){
	$value = 0;
	$status = 'OFFLINE';
	$online = false;

	if ($executionCommand['params']['on']) $value = 1;

	RecordManager::createWithSubId($subDeviceId, $value);

	$executed = 0;
	$waiting = 0;
	foreach (RecordManager::getLastRecord($subDeviceId, 4) as $key => $value) {
		if ($value['execuded'] == 1){
			$executed++;
		} else {
			$waiting++;
		}
	}

	if ($waiting < $executed){
		$status = "PENDING";
		$online = true;
	}

	$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
	$commandTemp = [
		'ids' => [(string) $deviceId],
		'status' => $status,
		'states' => [
			'on' => $executionCommand['params']['on'],
			'online' => $online,
		],
	];
	return $commandTemp;
}

static function executeTermostatValue($subDeviceId, $executionCommand){
	$value = 0;
	$status = 'OFFLINE';
	$online = false;

	if (isset($executionCommand['params']['thermostatTemperatureSetpoint'])) {
		$value = $executionCommand['params']['thermostatTemperatureSetpoint'];
	}

	RecordManager::createWithSubId($subDeviceId, $value);

	$executed = 0;
	$waiting = 0;
	foreach (RecordManager::getLastRecord($subDeviceId, 4) as $key => $lastValue) {
		if ($lastValue['execuded'] == 1){
			$executed++;
		} else {
			$waiting++;
		}
	}

	if ($waiting < $executed){
		$status = "PENDING";
		$online = true;;
	}

	$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
	$commandTemp = [
		'ids' => [(string) $deviceId],
		'status' => $status,
		'states' => [
			'thermostatMode' => 'heat',
			'thermostatTemperatureSetpoint' => $value,
			'thermostatTemperatureAmbient' => $value,
			'online' => $online,
			//ambient z dalšího zenzoru v roomu
		],
	];
	return $commandTemp;
}

static function executeTermostatMode($subDeviceId, $executionCommand){
	$mode = "off";
	$value = 0;
	$status = 'OFFLINE';
	$online = false;

	if (isset($executionCommand['params']['thermostatMode']) && $executionCommand['params']['thermostatMode'] != 'off') {
		$mode = $executionCommand['params']['thermostatMode'];
		$value = RecordManager::getLastRecordNotNull($subDeviceId)['value'];
	}

	RecordManager::createWithSubId($subDeviceId, $value);

	$executed = 0;
	$waiting = 0;
	foreach (RecordManager::getLastRecord($subDeviceId, 4) as $key => $value) {
		if ($value['execuded'] == 1){
			$executed++;
		} else {
			$waiting++;
		}
	}

	if ($waiting < $executed){
		$status = "PENDING";
		$online = true;
	}

	$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
	$commandTemp = [
		'ids' => [(string) $deviceId],
		'status' => $status,
		'states' => [
			'thermostatMode' => $mode,
			'online' => $online,
		],
	];

	return $commandTemp;
}

static function executeVolume($subDeviceId, $executionCommand){
	echo $executionCommand['params']['volumeLevel'];
	$status = 'OFFLINE';
	$online = false;

	$currentVolume = RecordManager::getLastRecord($subDeviceId)['value'];

	if (isset($executionCommand['params']['volumeLevel'])) {
		RecordManager::createWithSubId($subDeviceId, $executionCommand['params']['volumeLevel']);
		$executed = 0;
		$waiting = 0;
		foreach (RecordManager::getLastRecord($subDeviceId, 4) as $key => $value) {
			if ($value['execuded'] == 1){
				$executed++;
			} else {
				$waiting++;
			}
		}
		if ($waiting < $executed){
			$status = "PENDING";
			$online = true;
			$currentVolume = $executionCommand['params']['volumeLevel'];
		}
	}

	$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
	$commandTemp = [
		'ids' => [(string) $deviceId],
		'status' => $status,
		'states' => [
			'currentVolume' => $currentVolume,
			'online' => $online,
		],
	];

	return $commandTemp;
}
}
