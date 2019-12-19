<?php


class Home extends Template
{
	function __construct()
	{
		global $userManager;
		global $langMng;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR . 'login');
		}

		$template = new Template('home');

		//users instantialize
		$users = UserManager::getUsers();
		$template->prepare('users', $users);

		//Users at home Info
		$usersAtHome = '';
		$i = 0;
		foreach ($users as $user) {
			if ($user['at_home'] == 'true') {
				$i++;
				$usersAtHome .= $user['username'];
				if ($usersAtHome != "" && isset($users[$i + 1]) && $users[$i + 1]['at_home'] == 'true'){
					$usersAtHome .= ', ';
				}
			}
		}
		$template->prepare('usersAtHome', $usersAtHome);


		$roomsItems = [];
		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomsData) {
			$devices = [];
			$devicesData = DeviceManager::getAllDevicesInRoom($roomsData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {
				$subDevices = [];
				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {

					$events = RecordManager::getLastRecord($subDeviceData['subdevice_id'], 5);
					$eventsRaw = $events;

					$connectionError = true;
					$parsedValue = "";
					$niceTime = "";

					if (sizeof($events) > 1) {
						$lastRecord = $events[0];
						$lastValue = round($lastRecord['value']);
						$parsedValue = $lastValue;

						/*Value Parsing*/
						//Last Value Parsing
						switch ($subDeviceData['type']) {
							case 'on/off':
							$replacementTrue = 'On';
							$replacementFalse = 'Off';
							$operator = '==';
							$breakValue = 1;
							break;

							case 'door':
							$replacementTrue = 'Closed';
							$replacementFalse = 'Open';
							$operator = '==';
							$breakValue = 1;
							break;

							case 'light':
								$replacementTrue = 'Light';
								$replacementFalse = 'Dark';
								$operator = '==';
								$breakValue = 1;
								if ($lastValue != 1 && $lastValue != 0) { //Digital Light Senzor
									$operator = '<';
									$breakValue = 810;
								}
							break;

							case 'water':
							$replacementTrue = 'Wet';
							$replacementFalse = 'Dry';
							$operator = '==';
							$breakValue = 1;
							break;

							default:
							$replacementTrue = '';
							$replacementFalse = '';
							break;
						}

						if ($replacementTrue != '' && $replacementFalse != '') {
							//parsing last values
							$parsedValue = $replacementFalse;

							if (Utilities::checkOperator($lastValue, $operator, $breakValue)) {
								$parsedValue = $replacementTrue;
							}


							//parsing last events values
							foreach ($events as $key => $value) {
								$events[$key]['value'] = $replacementFalse;
								if (Utilities::checkOperator($value['value'], $operator, $breakValue)) {
									$events[$key]['value'] = $replacementTrue;
								}
							}
						}

						$LastRecordTime = new DateTime($lastRecord['time']);
						$niceTime = Utilities::ago($LastRecordTime);

						$interval = $LastRecordTime->diff(new DateTime());
						$hours   = $interval->format('%h');
						$minutes = $interval->format('%i');
						$lastSeen = ($hours * 60 + $minutes);

						if ($lastSeen < $deviceData['sleep_time'] || $subDeviceData['type'] == "on/off") {
							$connectionError = false;
						}
					}

					$subDevices[$subDeviceData['subdevice_id']] = [
						'events'=> $events,
						'eventsRaw'=> $eventsRaw,
						'type' => $subDeviceData['type'],
						'unit' => $subDeviceData['unit'],
						'comError' => $connectionError,
						'lastRecort' =>  [
							'value' => (empty($parsedValue) ? 0 : $parsedValue),
							'time' => (empty($lastRecord['time']) ? "00:00" : $lastRecord['time']),
							'niceTime' => (empty($niceTime) ? "00:00" : $niceTime),
						],
					];
				}

				$permissionArray = json_decode($deviceData['permission']);
				$userIsDeviceAdmin = false;
				if($permissionArray[1] == 3) {
					$userIsDeviceAdmin = true;
				} else if ($permissionArray[0] == 3) {
					if ( $deviceData['owner'] == $_SESSION['user']['id']) {
						$userIsDeviceAdmin = true;
					}
				}

				$devices[$deviceData['device_id']] = [
					'name' => $deviceData['name'],
					'icon' => $deviceData['icon'],
					'room' => $deviceData['room_id'],
					'token' => $deviceData['token'],
					'type' => $deviceData['type'],
					'ip' => $deviceData['ip_address'],
					'subnet' => $deviceData['subnet'],
					'gateway' => $deviceData['gateway'],
					'sleepTime' => $deviceData['sleep_time'],
					'approved' => $deviceData['approved'],
					'permission' => $permissionArray,
					'owner' => $deviceData['owner'],
					'userIsAdmin' => $userIsDeviceAdmin,
					'subDevices' => $subDevices,
				];
			}
			$roomsItems[$roomsData['room_id']] = [
				'name' => $roomsData['name'],
				'deviceCount' => $roomsData['device_count'],
				'devices' => $devices,
			];
		}

		$rooms = RoomManager::getAllRooms();
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('title', 'Home');
		$template->prepare('rooms', $rooms);
		$template->prepare('langMng', $langMng);
		$template->prepare('data', $roomsItems);

		$template->render();

	}
}
