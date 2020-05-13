<?php
class GoogleHome {
	static function sync($requestId){
		$devices = [];
		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomData) {
			$devicesData = DeviceManager::getAllDevicesInRoom($roomData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {
				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
					if ($subDeviceData['type'] != "on/off" && $subDeviceData['type'] != "temp_cont") continue;

					//Google Compatibile Action Type
					$actionType = GoogleHomeDeviceTypes::getAction($subDeviceData['type']);
					$tempDevice = [
						'id' => (string) $subDeviceData['subdevice_id'],
						'type' => $actionType,
						'name' => [
							'name' => $deviceData['name'],
						],
						'willReportState' => false,
						'roomHint' => $roomData['name']
					];

					//traids & Attributes
					$devices[] = GoogleHomeDeviceTypes::getSyncObj($tempDevice, $actionType);
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
		$apiLogManager->write("[API][$requestId] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
		echo json_encode($response);
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

			$tempDevice = [
				$deviceId['id'] => [
					'online' => $online,
					'status'=> $status,
				]
			];

			if ($subDeviceData['type'] == "temp_cont"){
				$tempDevice[$deviceId['id']]['thermostatMode'] = 'off';
				if (RecordManager::getLastRecord($deviceId['id'])['value'] != 0) {
					$tempDevice[$deviceId['id']]['thermostatMode'] = 'heat';
					$tempDevice[$deviceId['id']]['thermostatTemperatureAmbient'] = RecordManager::getLastRecord($deviceId['id'])['value'];
					$tempDevice[$deviceId['id']]['thermostatTemperatureSetpoint'] = RecordManager::getLastRecord($deviceId['id'])['value'];
				}
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

				$subDeviceId = $device['id'];

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
		$apiLogManager->write("[API][EXECUTE][$requestId]\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);

		echo json_encode($response);
	}

	static function executeSwitch($subDeviceId, $executionCommand){
		$value = 0;
		$status = 'SUCCESS';
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
		} else {
			$status = "OFFLINE";
		}

		$commandTemp = [
			'ids' => [$subDeviceId],
			'status' => $status,
			'states' => [
				'on' => $executionCommand['params']['on'],
			],
		];
		return $commandTemp;
	}

	static function executeTermostatValue($subDeviceId, $executionCommand){
		$value = 0;
		$status = 'SUCCESS';

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
		} else {
			$status = "OFFLINE";
		}

		$commandTemp = [
			'ids' => [$subDeviceId],
			'status' => $status,
			'states' => [
				'thermostatMode' => 'heat',
				'thermostatTemperatureSetpoint' => $value,
				'thermostatTemperatureAmbient' => $value,
				//ambient z dalšího zenzoru v roomu
			],
		];

		if ($timeout >= 5){
			$commandTemp['status'] = "OFFLINE";
		}
		return $commandTemp;
	}

	static function executeTermostatMode($subDeviceId, $executionCommand){
		$mode = "off";
		$value = 0;
		$status = "SUCCESS";

		if (isset($executionCommand['params']['thermostatMode']) && $executionCommand['params']['thermostatMode'] != 'off') {
			$mode = $executionCommand['params']['thermostatMode'];
			$value = RecordManager::getLastRecordNotNull($subDeviceId)['value'];
		}

		RecordManager::createWithSubId($subDeviceId, $value);

		$executed = 0;
		$waiting = 0;
		foreach (RecordManager::getLastRecord($deviceId['id'], 4) as $key => $value) {
			if ($value['execuded'] == 1){
				$executed++;
			} else {
				$waiting++;
			}
		}
		if ($waiting < $executed){
			$status = "PENDING";
		}

		$commandTemp = [
			'ids' => [$subDeviceId],
			'status' => $status,
			'states' => [
				'thermostatMode' => $mode
			],
		];

		return $commandTemp;
	}
}
