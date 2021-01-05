<?php


class Device extends Template
{
	function __construct($sortBy = null, $sortType = null)
	{
		$userManager = new UserManager();
		$deviceManager = new DeviceManager();
		$subDeviceManager = new SubDeviceManager();
		$recordManager = new RecordManager();
		$roomManager = new RoomManager();
		$langMng = new LanguageManager('en');

		if (!$userManager->isLogin()) {
			header('Location: ' . BASEURL . 'login');
		}

		$template = new Template('device');
		$template->prepare('title', $langMng->get("m_devices"));

		$sortWordBook = [
			"id" => "device_id",
			"name" => "name",
			"room" => "room_id",
			"ip" => "ip_address",
			"mac" => "mac",
			"token" => "token",
			"signal" => "signal",
			"firmware" => "firmware_hash",
			"icon" => "icon",
			"history" => "history",
		];

		$sortIcons = [
			"ASC" => "&#xf0de",
			"DESC" => "&#xf0dd",
		];

		$nextSort = [
			"ASC" => "DESC",
			"DESC" => "ASC",
		];

		$devices = $deviceManager->getAllDevices();

		if (empty($sortBy) && empty($sortType)) {
			$sortBy = "id";
			$sortType = "DESC";
		}
		$template->prepare('sortIcon', [$sortBy => $sortIcons[$sortType]]);

		foreach ($devices as $key => $device) {
			//Signal Stenght
			$subdevice = $subDeviceManager->getSubDeviceByMasterAndType($device['device_id'], "wifi");
			$subdeviceLocal = $subDeviceManager->getSubDeviceByMaster($device['device_id']);
			if (!empty ($subdeviceLocal)) {
				$devices[$key]['history'] = (!empty ($subdeviceLocal['history']) ? $subdeviceLocal['history'] : 0);
			} else {
				$devices[$key]['history'] = "null";
			}
			$devices[$key]['signal'] = "";
			if (!empty($subdevice['subdevice_id'])) {
				$record = $recordManager->getLastRecord($subdevice['subdevice_id']);
				if (!empty($record)) {
					$devices[$key]['signal'] = $record['value'] . " " . $subdevice['unit'];
				}
			}

			//Firmware Status
			if (empty($devices[$key]['mac'])) {
				$devices[$key]['firmware_hash'] = "";
				continue;
			}
			$localBinary = "../updater/" . str_replace(':', '', $device['mac']) . ".bin";
			$devices[$key]['firmware_hash'] = "";
			if (file_exists($localBinary)) {
				$hash = md5_file($localBinary);
				if ($hash == $device['firmware_hash']) {
					$devices[$key]['firmware_hash'] = "true";
				} else {
					$devices[$key]['firmware_hash'] = "need";
				}
			} else {
				$devices[$key]['firmware_hash'] = "false";
			}
		}

		$devices = Utilities::sortArrayByKey($devices, $sortWordBook[$sortBy], strtolower($sortType));

		$rooms = $roomManager->getAllRooms();

		$template->prepare('baseUrl', BASEURL);
		$template->prepare('baseDir', BASEDIR);

		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('logToLiveTime', LOGTIMOUT);
		$template->prepare('rooms', $rooms);
		$template->prepare('sortType', $nextSort[$sortType]);
		$template->prepare('devices', $devices);
		$template->prepare('langMng', $langMng);

		$template->render();
	}
}
