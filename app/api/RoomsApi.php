<?php

class RoomsApi extends ApiController{

	public function default(){
		//$this->requireAuth();
		$response = [];
		$roomIds = [];
		$roomsData = RoomManager::getRoomsDefault();

		foreach ($roomsData as $roomKey => $room) {
			$roomIds[] = $room['room_id'];
		}

		$subDevicesData = SubDeviceManager::getSubdevicesByRoomIds($roomIds);

		foreach ($roomsData as $roomKey => $roomData) {
			$response[] = [
				'room_id' => $roomData['room_id'],
				'name' => $roomData['name'],
				'widgets' => isset($subDevicesData[$roomData['room_id']]) ? $subDevicesData[$roomData['room_id']] : [],
			];
		}
		$this->response($response);
	}
}
