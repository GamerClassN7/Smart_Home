<?php
class GoogleHomeApi {
	function response()
	{
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');

		$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
		$apiLogManager->write("[API] POST  body\n" . json_encode($_POST, JSON_PRETTY_PRINT), LogRecordType::INFO);
		$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordType::INFO);

		header('Content-Type: application/json');
		switch ($obj['inputs'][0]['intent']) {
			case 'action.devices.SYNC':
				self::sync($obj['requestId']);
			break;

			case 'action.devices.QUERY':

			break;

			case 'action.devices.EXECUTE':
				self::execute($obj['requestId'], $obj['inputs'][0]['payload']);
			break;
		}
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
				'agentUserId'=>'simple-Home',
				'devices' => $devices,],
		];

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');

		$apiLogManager->write("[API] request response\n" . json_encode($response, JSON_PRETTY_PRINT), LogRecordType::INFO);
		echo json_encode($response);
	}

	static function execute($subdeviceId, $payload){
		$commands = [
			'ids' => 
		];
		foreach ($payload['commands'] as $key => $command) {
			foreach ($command['devices'] as $key => $device) {
				$executionCommand = $command['execution'][$key];
				$subDeviceId = $device['id'];

				switch ($executionCommand) {
					case 'action.devices.commands.OnOff':
						if ($executionCommand['on'] == true){
							//turn ddeivce on
							
						}
						break;
					default:
						# code...
						break;
				}

			}
		}

		$response = [
			'requestId' => $requestId,
			'payload' => [
				'commands' => $commands,],
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
