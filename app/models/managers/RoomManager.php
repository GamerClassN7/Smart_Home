<?php
class RoomManager{
	public static $rooms;

	static function getDefaultRoomId() {
		$defaultRoom = Db::loadOne("SELECT room_id FROM rooms WHERE 'default' = 1");
		return $defaultRoom['room_id'];
	}

	static function getAllRooms () {
		//TODO: ignore Widgets withoud data
		$allRoom = Db::loadAll ("SELECT rooms.*, COUNT(devices.device_id) as device_count FROM rooms LEFT JOIN devices ON (devices.room_id=rooms.room_id) GROUP BY rooms.room_id");
		return $allRoom;
	}

	static function getRoomsDefault () {
		//TODO: ignore Widgets withoud data
		$allRoom = Db::loadAll ("SELECT rooms.room_id, rooms.name, COUNT(devices.device_id) as device_count FROM rooms LEFT JOIN devices ON (devices.room_id=rooms.room_id) GROUP BY rooms.room_id;");
		return $allRoom;
	}

	public static function create ($name) {
		$room = array (
			'name' => $name,
		);
		try {
			Db::add ('rooms', $room);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function delete ($roomId) {
		Db::command ('DELETE FROM rooms WHERE room_id=?', array ($roomId));
	}
}
?>
