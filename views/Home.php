<?php


class Home extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./login');
		}

		$template = new Template('home');

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


					$lastRecord = RecordManager::getLastRecord($subDeviceData['subdevice_id']);
					$parsedValue = round($lastRecord['value']);

					/*Value Parsing*/
					if ($subDeviceData['type'] == "on/off") {
						$parsedValue = ($parsedValue == 1 ? 'ON' : 'OFF');
					}
					if ($subDeviceData['type'] == "door") {
						$replacementTrue = 'Closed';
						$replacementFalse = 'Opened';
						foreach ($events as $key => $value) {
							$events[$key]['value'] = ($value['value'] == 1 ? $replacementTrue : $replacementFalse);
						}
						$parsedValue = ($parsedValue == 1 ? $replacementTrue : $replacementFalse);
					}

					if ($subDeviceData['type'] == "light") {
						$replacementTrue = 'Light';
						$replacementFalse = 'Dark';
						foreach ($events as $key => $value) {
							if ($parsedValue != 1){
								//Analog Reading
								$events[$key]['value'] = ($value['value'] <= 810 ? $replacementTrue : $replacementFalse);
							} else {
								//Digital Reading
								$events[$key]['value'] = ($value['value'] == 0 ? $replacementTrue : $replacementFalse);
							}
						}
						if ($parsedValue != 1){
							//Analog Reading
							$parsedValue = ($parsedValue <= 810 ? $replacementTrue : $replacementFalse);
						} else {
							//Digital Reading
							$parsedValue = ($parsedValue == 0 ? $replacementTrue : $replacementFalse);
						}
					}

					$subDevice = SubDeviceManager::getSubDevice($subDeviceData['subdevice_id']);
					$records = RecordManager::getAllRecordForGraph($subDeviceData['subdevice_id']);

					$array = array_column($records, 'value');
					foreach ($array as $key => $value) {
						$array[$key] = $value . $subDevice['unit'];
					}

					$data = json_encode($array);
					$labels = json_encode($array);


					$date2 = new DateTime($lastRecord['time']);

					$niceTime = $this->ago($date2);

					$connectionError = false;

					$startDate = date_create($lastRecord['time']);
					$interval = $startDate->diff(new DateTime());
					$hours   = $interval->format('%h');
					$minutes = $interval->format('%i');
					$lastSeen = ($hours * 60 + $minutes);

					if ($lastSeen > $deviceData['sleep_time'] && $subDeviceData['type'] != "on/off") {
						$connectionError = true;
					}

					$subDevices[$subDeviceData['subdevice_id']] = [
						'data' => $data,
						'events'=> $events,
						'labels' => $labels,
						'range' => RANGES[$subDevice['type']],
						'type' => $subDeviceData['type'],
						'unit' => $subDeviceData['unit'],
						'comError' => $connectionError,
						'lastRecort' =>  [
							'value' => $parsedValue,
							'time' => $lastRecord['time'],
							'niceTime' => $niceTime,
						],
					];
				}

				$permissionArray = json_decode($deviceData['permission']);

				$userIsDeviceAdmin = false;
				if($permissionArray[1] == 3) {
					$userIsDeviceAdmin = true;
				} else if ($permissionArray[0] == 3) {
					if ( $deviceData['owner'] == $userManager->getUserData('user_id')) {
						$userIsDeviceAdmin = true;
					}
				}

				$devices[$deviceData['device_id']] = [
					'name' => $deviceData['name'],
					'icon' => $deviceData['icon'],
					'room' => $deviceData['room_id'],
					'token' => $deviceData['token'],
					'sleepTime' => $deviceData['sleep_time'],
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

		if (isset($_POST['deviceId'])){
			$deviceData = DeviceManager::getDeviceById($_POST['deviceId']);

			$subDevices = [];
			$subDevicesData = SubDeviceManager::getAllSubDevices($_POST['deviceId']);

			foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
				$subDevices[$subDeviceData['subdevice_id']] = [
					'type' => $subDeviceData['type'],
					'unit' => $subDeviceData['unit'],
				];
			}

			$device = [
				'id' => $deviceData['device_id'],
				'name' => $deviceData['name'],
				'token' => $deviceData['token'],
				'icon' => $deviceData['icon'],
				'subDevices' => $subDevices,
			];
			$template->prepare('deviceData', $device);
		}

		$users = UserManager::getUsers();
		$template->prepare('users', $users);

		$rooms = RoomManager::getAllRooms();
		$template->prepare('rooms', $rooms);
		$template->prepare('title', 'Home');
		$template->prepare('lang', $lang);
		$template->prepare('data', $roomsItems);

		$template->render();

	}

	function ago( $datetime )
	{
		$interval = date_create('now')->diff( $datetime );
		$suffix = ( $interval->invert ? ' ago' : '' );
		if ( $v = $interval->y >= 1 ) return $this->pluralize( $interval->m, 'month' ) . $suffix;
		if ( $v = $interval->d >= 1 ) return $this->pluralize( $interval->d, 'day' ) . $suffix;
		if ( $v = $interval->h >= 1 ) return $this->pluralize( $interval->h, 'hour' ) . $suffix;
		if ( $v = $interval->i >= 1 ) return $this->pluralize( $interval->i, 'minute' ) . $suffix;
		return $this->pluralize( $interval->s, 'second' ) . $suffix;
	}

	function pluralize( $count, $text )
	{
		return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
	}
}
