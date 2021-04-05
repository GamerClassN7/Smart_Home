<?php

class RoomsApi extends ApiController
{

	public function	default()
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
				//Type Handling
				$type = null;
				if (strpos($subDevicesData[$subDeviceKey][$key]['type'], '-') !== false) {
					$type = $subDevicesData[$subDeviceKey][$key]['type'];
				} else if (strpos(SubDeviceManager::getSubDeviceMaster($subDevicesData[$subDeviceKey][$key]['subdevice_id'])['type'], '-') !== false) {
					$type = SubDeviceManager::getSubDeviceMaster($subDevicesData[$subDeviceKey][$key]['subdevice_id'])['type'];
				}

				//Connection Error Creation
				$connectionError = true;
				$LastRecordTime = new DateTime($subDevicesData[$subDeviceKey][$key]['heartbeat']);
				$interval = $LastRecordTime->diff(new DateTime());


				$lastSeen = $interval->days * 24 * 60;
				$lastSeen += $interval->h * 60;
				$lastSeen += $interval->i;


				//$lastSeen = ($interval->format('%h') * 60 + $interval->format('%i'));

				if ($lastSeen < ($subDevicesData[$subDeviceKey][$key]['sleep_time'] == 0 ? 15 : $subDevicesData[$subDeviceKey][$key]['sleep_time'])) {
					$connectionError = false;
				}
				$subDevicesData[$subDeviceKey][$key]['connection_error'] = $connectionError;

				//Record Translation
				$cammelCaseClass = "";
				foreach (explode('-', $type) as $word) {
					$cammelCaseClass .= ucfirst($word);
				}
				if (class_exists($cammelCaseClass)) {
					$deviceClass = new $cammelCaseClass;
					if (method_exists($deviceClass, 'translate')) {
						$subDevicesData[$subDeviceKey][$key]['value'] = $deviceClass->translate($subDevicesData[$subDeviceKey][$key]['value']);
					}
				}
			}
		}

		foreach ($roomsData as $roomKey => $roomData) {
			if ($roomData['device_count'] == 0) continue;
			$subDevicesSorted = isset($subDevicesData[$roomData['room_id']]) ? Utilities::sortArrayByKey($subDevicesData[$roomData['room_id']], 'connection_error', 'asc') : [];
			if (count($subDevicesSorted) == 0) continue;
			$response[] = [
				'room_id' => $roomData['room_id'],
				'name' => $roomData['name'],
				'widgets' => $subDevicesSorted,
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
