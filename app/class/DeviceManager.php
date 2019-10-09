<?php
class DeviceManager{
	public static $devices;

	function getAllDevices () {
		return Db::loadAll ("SELECT * FROM devices WHERE approved != ?", Array(2));
	}

	function getAllDevicesInRoom ($roomId = "") {
		return Db::loadAll ("SELECT * FROM devices WHERE room_id = ? AND approved != ?", Array($roomId, 2));
	}

	function getOtherDevices(){
		return Db::loadAll ("SELECT * FROM devices WHERE room_id IS NULL ");
	}

	function getDeviceByToken($deviceToken) {
		return Db::loadOne("SELECT * FROM devices WHERE token = ?", array($deviceToken));
	}

	function getDeviceById($deviceId) {
		return Db::loadOne("SELECT * FROM devices WHERE device_id = ?", array($deviceId));
	}

	public function create ($name, $token) {
		$device = array (
			'name' => $name,
			'token' => $token,
		);
		try {
			Db::add ('devices', $device);
			return Db::loadOne("SELECT device_id FROM devices WHERE token = ?", array($token))['device_id'];
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function edit ($deviceId, $values = []) {
		try {
			Db::edit ('devices', $values, 'WHERE device_id = ?', array($deviceId));
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
	public function assignRoom ($roomId, $deviceId) {
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
	public function delete ($deviceId) {
		Db::command ('DELETE FROM devices WHERE device_id=?', array ($deviceId));
	}

	public function registeret ($deviceToken) {
		return (count(Db::loadAll ("SELECT * FROM devices WHERE token=?", array($deviceToken))) == 1 ? true : false);
	}

	public function approved ($deviceToken) {
		return (count(Db::loadAll ("SELECT * FROM devices WHERE token=? AND approved = ?", array($deviceToken, 1))) == 1 ? true : false);
	}
}
?>
