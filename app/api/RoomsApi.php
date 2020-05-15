<?php

class RoomsApi extends ApiController{

	public function default(){
		//$this->requireAuth();
		$rooms = [];
		$roomsData = RoomManager::getAllRooms();
		foreach ($roomsData as $roomKey => $roomData) {

			$widgets = [];
			$devicesData = DeviceManager::getAllDevicesInRoom($roomData['room_id']);
			foreach ($devicesData as $deviceKey => $deviceData) {

				$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
				foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {

					$lastRecord = RecordManager::getLastRecord($subDeviceData['subdevice_id']);
					$widgets[] = [
						'subdevice_id' => $subDeviceData['subdevice_id'],
						'device_id' =>  $deviceData['device_id'],
						'name' => $deviceData['name'],
						'type' => $subDeviceData['type'],
						'icon' => $deviceData['icon'],
						'value' => $lastRecord['value'],
						'unit' => $subDeviceData['unit'],
					];
				}
			}

			$rooms[] = [
				'room_id' => $roomData['room_id'],
				'name' => $roomData['name'],
				'widgets' => $widgets,
			];
		}
		$this->response($rooms);
	}
}
