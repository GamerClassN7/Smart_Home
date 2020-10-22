<?php

class RoomsApi extends ApiController
{

	public function
	default()
	{
		//$this->requireAuth();
		$response = [];
		$roomIds = [];
		$roomsData = RoomManager::getRoomsDefault();

		foreach ($roomsData as $roomKey => $room) {
			$roomIds[] = $room['room_id'];
		}

		//Translation Of Numeric Walues
		$subDevicesData = SubDeviceManager::getSubdevicesByRoomIds($roomIds);
		foreach ($subDevicesData as $subDeviceKey => $subDevice) {
			foreach ($subDevice as $key => $value) {
				if (strpos($subDevicesData[$subDeviceKey][$key]['type'], '-') !== false) {
					$type = "";
					foreach (explode('-', $subDevicesData[$subDeviceKey][$key]['type']) as $word) {
						$type .= ucfirst($word);
					}
					if (!class_exists($type)) {
						continue;
					}
					$deviceClass = new $type;
					if (!method_exists($deviceClass, 'translate')) {
						continue;
					}
					$subDevicesData[$subDeviceKey][$key]['value'] = $deviceClass->translate($subDevicesData[$subDeviceKey][$key]['value']);
				}
			}
		}

		foreach ($roomsData as $roomKey => $roomData) {
			if ($roomData['device_count'] == 0) continue;
			$response[] = [
				'room_id' => $roomData['room_id'],
				'name' => $roomData['name'],
				'widgets' => isset($subDevicesData[$roomData['room_id']]) ? $subDevicesData[$roomData['room_id']] : [],
			];
		}
		$this->response($response);
	}

	public function update($roomId)
	{
		//$this->requireAuth();

		$subDevicesData = SubDeviceManager::getSubdevicesByRoomIds([$roomId]);
		$this->response($subDevicesData);
	}
}
