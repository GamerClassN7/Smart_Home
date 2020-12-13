<?php
class SubDeviceManager
{
	public static $devices;

	public function getAllSubDevices($deviceId)
	{
		return Db::loadAll("SELECT * FROM subdevices WHERE device_id = ?", array($deviceId));
	}

	public function getSubDeviceMaster($subDeviceId)
	{
		return Db::loadOne("SELECT * FROM devices WHERE device_id = (SELECT device_id FROM subdevices WHERE subdevice_id = ?)", array($subDeviceId));
	}

	public function getSubDeviceByMaster($deviceId, $subDeviceType = null)
	{
		if ($subDeviceType == null) {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
		} else {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
		}
	}

	public function getSubDeviceByMasterAndType($deviceId, $subDeviceType = null)
	{
		if (!empty($subDeviceType)) {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ?;", array($deviceId));
		} else {
			return Db::loadOne("SELECT * FROM subdevices WHERE device_id = ? AND type = ?;", array($deviceId, $subDeviceType));
		}
	}

	public function getSubDevice($subDeviceId)
	{
		return Db::loadOne("SELECT * FROM subdevices WHERE subdevice_id = ?;", array($subDeviceId));
	}

	public function getSubDevicesTypeForMater($deviceId)
	{
		$parsedTypes = [];
		$types = Db::loadAll("SELECT type FROM subdevices WHERE device_id = ?;", array($deviceId));
		foreach ($types as $orderNum => $type) {
			$parsedTypes[$orderNum] = $type['type'];
		}
		return $parsedTypes;
	}

	//check if dubdevice exist

	public function create($deviceId, $type, $unit)
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

	public function remove($subDeviceId)
	{
		RecordManager::cleanSubdeviceRecords($subDeviceId);
		return Db::loadAll("DELETE FROM subdevices WHERE subdevice_id = ?", array($subDeviceId));
	}
}
