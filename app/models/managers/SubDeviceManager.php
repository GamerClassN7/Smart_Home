<?php
class SubDeviceManager
{
	public static $devices;

	public static function getAllSubDevices($deviceId = null)
	{
		if ($deviceId == null){
			return Db::loadAll("SELECT * FROM subdevices");
		}
		return Db::loadAll("SELECT * FROM subdevices WHERE device_id = ?", array($deviceId));
	}

	public static function getSubDeviceMaster($subDeviceId)
	{
		return Db::loadOne("SELECT * FROM devices WHERE device_id = (SELECT device_id FROM subdevices WHERE subdevice_id = ?)", array($subDeviceId));
	}

	public static function getSubDeviceByMaster($deviceId, $subDeviceType = null)
	{
		if ($subDeviceType == null) {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
		} else {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
		}
	}

	public static function getSubDeviceByMasterAndType($deviceId, $subDeviceType = null)
	{
		if (!empty($subDeviceType)) {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
		} else {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
		}
	}

	public static function getSubDevice($subDeviceId)
	{
		return Db::loadOne("SELECT * FROM subdevices WHERE subdevice_id = ?;", array($subDeviceId));
	}

	public static function getSubDevicesTypeForMater($deviceId)
	{
		$parsedTypes = [];
		$types = Db::loadAll("SELECT type FROM subdevices WHERE device_id = ?;", array($deviceId));
		foreach ($types as $orderNum => $type) {
			$parsedTypes[$orderNum] = $type['type'];
		}
		return $parsedTypes;
	}

	//check if dubdevice exist

	public static function create($deviceId, $type, $unit)
	{
		$record = array(
			'device_id' => $deviceId,
			'type' => $type,
			'unit' => $unit,
		);
		try {
			Db::add('subdevices', $record);
		} catch (PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function remove($subDeviceId)
	{
		RecordManager::cleanSubdeviceRecords($subDeviceId);
		return Db::loadAll("DELETE FROM subdevices WHERE subdevice_id = ?", array($subDeviceId));
	}

	public static function getSubdevicesByRoomIds($roomIds = NULL) {
		if(empty($roomIds)) return NULL;

		$rows = Db::loadAll("
			SELECT d.room_id, sd.subdevice_id, sd.device_id, d.name, sd.type, sd.unit, r.value FROM subdevices sd
			JOIN devices d ON sd.device_id = d.device_id
			JOIN records r ON r.subdevice_id = sd.subdevice_id
			WHERE d.room_id IN (".str_repeat("?,", count($roomIds)-1)."?)
			AND r.record_id IN (
				SELECT MAX(record_id)
				FROM records
				GROUP BY subdevice_id
		  	)
		", $roomIds);

		$ret = [];
		foreach($rows as $row){
			$ret[$row['room_id']][] = $row;
		}

		return $ret;
	}
}
