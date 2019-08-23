<?php
class RoomManager{
	public static $rooms;

	function getAllRooms () {
		$allRoom = Db::loadAll ("SELECT rooms.*, COUNT(devices.device_id) as device_count FROM rooms LEFT JOIN devices ON (devices.room_id=rooms.room_id) GROUP BY rooms.room_id");
		return $allRoom;
	}

	public function create ($name) {
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

	public function delete ($roomId) {
		Db::command ('DELETE FROM rooms WHERE room_id=?', array ($roomId));
	}
}
?>
