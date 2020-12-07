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
				} else {
					continue;
				}
				
				//Record Translation
				$cammelCaseClass = "";
				foreach (explode('-', $type) as $word) {
					$cammelCaseClass .= ucfirst($word);
				}
				if (!class_exists($cammelCaseClass)) {
					continue;
				}
				$deviceClass = new $cammelCaseClass;
				if (!method_exists($deviceClass, 'translate')) {
					continue;
				}
				$subDevicesData[$subDeviceKey][$key]['value'] = $deviceClass->translate($subDevicesData[$subDeviceKey][$key]['value']);

				//Connection Error Creation
				$niceTime = Utilities::ago($LastRecordTime);

				$interval = $LastRecordTime->diff(new DateTime());
				$hours   = $interval->format('%h');
				$minutes = $interval->format('%i');
				$lastSeen = ($hours * 60 + $minutes);
		
				if (
					$lastSeen < $subDevicesData[$subDeviceKey][$key]['sleep_time'] ||
					$subDevicesData[$subDeviceKey][$key]['type'] == "on/off" ||
					$subDevicesData[$subDeviceKey][$key]['type'] == "door" ||
					$subDevicesData[$subDeviceKey][$key]['type'] == "wather"
				) {
					$connectionError = false;
				}
				$subDevicesData[$subDeviceKey][$key]['connection_error'] =  $connectionError

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
