<?php
class DeviceManager{
	public static $devices;

	static function getAllDevices () {
		return Db::loadAll ("SELECT devices.* FROM devices
		WHERE approved != ?", Array(2));
	}

	static function getAllDevicesInRoom ($roomId = "") {
		return Db::loadAll ("SELECT * FROM devices WHERE room_id = ? AND approved != ?", Array($roomId, 2));
	}

	static function getOtherDevices(){
		return Db::loadAll ("SELECT * FROM devices WHERE room_id IS NULL ");
	}

	static function getDeviceByToken($deviceToken) {
		return Db::loadOne("SELECT * FROM devices WHERE token = ?", array($deviceToken));
	}

	static function getDeviceByMac($deviceMac) {
		return Db::loadOne("SELECT * FROM devices WHERE mac = ?", array($deviceMac));
	}

	static function getDeviceById($deviceId) {
		return Db::loadOne("SELECT * FROM devices WHERE device_id = ?", array($deviceId));
	}

	static function getAllDevicesSorted ($sort, $sortType = "ASC") {
		return Db::loadAll ("SELECT devices.* FROM devices
			LEFT JOIN rooms ON (devices.room_id = rooms.room_id)
		WHERE devices.approved != ? ORDER BY $sort $sortType", Array(2));
	}

	public static function create ($name, $token) {
		$defaultRoom = RoomManager::getDefaultRoomId();
		$device = array (
			'name' => $name,
			'token' => $token,
			'room_id' => $defaultRoom,
		);
		try {
			Db::add ('devices', $device);
			return Db::loadOne("SELECT device_id FROM devices WHERE token = ?", array($token))['device_id'];
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function edit ($deviceId, $values = []) {
		try {
			Db::edit ('devices', $values, 'WHERE device_id = ?', array($deviceId));
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function editByToken ($token, $values = []) {
		try {
			Db::edit ('devices', $values, 'WHERE token = ?', array($token));
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	/**
	* [assignRoom Přiřazení zařízení do třídy]
	* @param  [type] $roomId   [číslo místnosti do kter se má zařízení přiřadit]
	* @param  [type] $deviceId [Číslo zařízení které chcete přiřadit do místnosti]
	*/
	public static function assignRoom ($roomId, $deviceId) {
		$device = array (
			'room_id' => $roomId,
		);
		try {
			Db::edit ('devices', $device, 'WHERE device_id = ?', array($deviceId));
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	/**
	* [delete Smazání zařízení]
	* @param  [type] $deviceId [Id zařízení ke smazání]
	*/
	public static function delete ($deviceId) {
		Db::command ('DELETE FROM devices WHERE device_id=?', array ($deviceId));
	}

	public static function registeret ($deviceToken) {
		return (count(Db::loadAll ("SELECT * FROM devices WHERE token=?", array($deviceToken))) == 1 ? true : false);
	}

	public static function approved ($deviceToken) {
		return (count(Db::loadAll ("SELECT * FROM devices WHERE token=? AND approved = ?", array($deviceToken, 1))) == 1 ? true : false);
	}
}
?>
