<?php
class GoogleHomeApi {
	public static function response()
	{
		set_time_limit (5);
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		header('Content-Type: application/json');

		switch ($obj['inputs'][0]['intent']) {
			case 'action.devices.SYNC':
			self::sync($obj['requestId']);
			break;

			case 'action.devices.QUERY':
			self::query($obj['requestId'], $obj['inputs'][0]['payload']);
			break;

			case 'action.devices.EXECUTE':
			self::execute($obj['requestId'], $obj['inputs'][0]['payload']);
			break;
		}
	}

	static function query($requestId, $payload){

		foreach ($payload['devices'] as $deviceId) {
			$subDeviceData = SubDeviceManager::getSubDevice($deviceId['id']);
			if ($subDeviceData['type'] != "on/off") continue;

			$state = false;
			if (RecordManager::getLastRecord($deviceId['id'])['value'] == 1){
				$state = true;
			}

			$online = false;
			$status = 'SUCCESS';
			if (RecordManager::getLastRecord($deviceId['id'])['execuded'] == 1){
				$online = true;
				$status = 'ERROR';
			}

			$devices[] = [
				$deviceId['id'] => [
					'on' => $state,
					'online' => $online,
					'status'=> $status,
				]
			];
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
		foreach ($roomsData as $roomKey => $roomsData) {
			$devicesData = DeviceManager::getAllDevicesInRoom($roomsData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {
				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
					if ($subDeviceData['type'] != "on/off") continue;
					$devices[] = [
						'id' => $subDeviceData['subdevice_id'],
						'type' => 'action.devices.types.OUTLET',
						'traits' => [ 'action.devices.traits.OnOff' ],
						'name' => [
							'name' => [$deviceData['name']],
						],
						'willReportState' => false,
					];
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
						$commandTemp['status'] = "ERROR";
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
		"expires_in"=>600,
		"state"=>$_GET["state"],
	];

	echo $_GET["redirect_uri"] . '#' . http_build_query($get) ;
	echo '<a href="'.$_GET["redirect_uri"] . '#' . http_build_query($get) . '">FINISH</a>';
}
}
