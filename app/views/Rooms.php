<?php


class Rooms extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR . 'login');
		}

		$template = new Template('rooms');

		$template->prepare('baseDir', BASEDIR);
			$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Rooms');
		$template->prepare('lang', $lang);

		$roomsItems = [];
		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomsData) {
			$devicesData = DeviceManager::getAllDevicesInRoom($roomsData['room_id']);
			$roomReading = [];
			if ($roomsData['device_count'] == 0) {
				continue;
			}

			$roomReadingCache = [];
			$roomControlsCache = [];
			foreach ($devicesData as $deviceKey => $deviceData) {
				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
					$subDeviceType = $subDeviceData['type'];
					$subDeviceUnit = $subDeviceData['unit'];
					$lastRecord = RecordManager::getLastRecord($subDeviceData['subdevice_id']);

					if (in_array($subDeviceType, ['on/off','battery','door'])) {
						if ($subDeviceType == 'on/off') {
							$roomControlsCache[$subDeviceKey] = [
								'type' => $subDeviceType,
								'name' => $deviceData['name'],
								'icon' => $deviceData['icon'],
								'value' => $lastRecord['value'],
							];
						}
						continue;
					}

					if (array_key_exists($subDeviceType, $roomReadingCache)) {
						$roomReadingCache[$subDeviceType] = [
							"value" => $roomReadingCache[$subDeviceType]['value'] + $lastRecord['value'],
							"count" => $roomReadingCache[$subDeviceType]['count'] + 1,
							"unit" => $subDeviceUnit,
						];
					} else {
						$roomReadingCache[$subDeviceType] = [
							"value" => $lastRecord['value'],
							"count" => 1,
							"unit" => $subDeviceUnit,
						];
					}
				}
			}

			// parsing
			foreach ($roomReadingCache as $type => $value) {
				$roomReading[$type] = $value["value"] / $value["count"];
				$roomReading[$type] .= @($value['unit']);
			}

			$roomsItems[$roomsData['room_id']] = [
				'name' => $roomsData['name'],
				'reading' => $roomReading,
				'controls' => $roomControlsCache,
				'deviceCount' => $roomsData['device_count'],
			];
		}

		$template->prepare('rooms', $roomsItems);

		$template->render();

	}
}
