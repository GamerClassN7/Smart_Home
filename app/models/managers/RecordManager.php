<?php
class RecordManager{
	public static $records;

	public static function createWithSubId ($subDeviceId, $value, $origin = false) {
		try {
			$record = [
				'execuded' => 1,
			];

			Db::edit ('records', $record, 'WHERE subdevice_id = ?', array ($subDeviceId));
			$record = array (
				'subdevice_id' => $subDeviceId,
				'value' => $value,
			);

			if ($origin != false)
				$record['Origin'] = $origin;

			return Db::add ('records', $record);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function create ($deviceId, $type, $value, $origin = false) {
		$subDeviceId = Db::loadOne('SELECT * FROM subdevices WHERE device_id = ? AND type = ?;', array($deviceId, $type))['subdevice_id'];
		if ($subDeviceId == '') {
			return false;
		};

		//Ochrana proti duplicitním hodnotám zapisují se jen změny
		$lastRecord = self::getLastRecord($subDeviceId, 1);

		if (isset($lastRecord['value']) && $lastRecord['value'] == $value){
			return false;
		}

		try {
			$record = [
				'execuded' => 1,
			];
			Db::edit ('records', $record, 'WHERE subdevice_id = ?', array ($subDeviceId));

			$record = array (
				'subdevice_id' => $subDeviceId,
				'value' => $value,
			);

			if ($origin != false)
				$record['Origin'] = $origin;

			return Db::add ('records', $record);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function setExecuted($recordId) {
		try {
			Db::edit ('records', ['execuded' => 1], 'WHERE record_id = ?', array($recordId));
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function getRecordById($recordId) {
		return Db::loadOne('SELECT * FROM records WHERE record_id = ?;', array($recordId));
	}

	public static function getLastInsertedRecordId() {
		return Db::insertId();
	}

	public static function getLastRecord($subDeviceId, $num = 1) {
		if ($num == 1)
		return Db::loadOne('SELECT * FROM records WHERE subdevice_id = ? AND value != ? ORDER BY time DESC;', array($subDeviceId, 999));
		return Db::loadAll('SELECT * FROM records WHERE subdevice_id = ? AND value != ? ORDER BY time DESC LIMIT ?;', array($subDeviceId, 999, $num));
	}

	public static function getLastRecordNotNull($subDeviceId) {
		return Db::loadOne('SELECT * FROM records WHERE subdevice_id = ? AND value != ? ORDER BY time DESC;', array($subDeviceId, 0));
	}

	public static function getAllRecord($subDeviceId, $timeFrom, $timeTo) {
		return Db::loadAll('SELECT * FROM records WHERE subdevice_id = ? AND time >= ? AND time <= ? AND value != ? ORDER BY time;', array($subDeviceId, $timeFrom, $timeTo, 999));
	}

	//TODO: Zeptat se @Patrik jestli je secure pak používat periodu přímo do SQL a pak pře url SQL Injection
	public static function getAllRecordForGraph($subDeviceId, $period = "day", $groupBy = "hour") {
		$periodLocal = '- 1' . strtoupper($period);
		$dateTime = new DateTime();
		$dateTime = $dateTime->modify($periodLocal);
		$dateTime = $dateTime->format('Y-m-d H:i:s');
		$groupBy = strtoupper($groupBy).'(time)';
		$sql = 'SELECT value, time, execuded, origin FROM records
		WHERE
		subdevice_id = ?
		AND
		value != 999
		AND
		time > ?
		GROUP BY '.$groupBy.'
		ORDER BY time Desc';
		//TODO: Prasárna Opravit
		return Db::loadAll($sql, array($subDeviceId, $dateTime));
	}

	public static function clean ($day) {
		if (isset($day)) {
			Db::command ('DELETE FROM records WHERE `time` < ADDDATE(NOW(), INTERVAL ? DAY);', array($day));
		}
	}

	//TODO: zkontrolovat jestli neco nezbilo po smazaní
	public static function cleanSubdeviceRecords ($subDeviceId) {
		Db::command ('DELETE FROM records WHERE subdevice_id = ?);', array($subDeviceId));
	}

	public static function setHistory($subDeviceId){
		$history = SubDeviceManager::getSubDevice($subDeviceId)['history'];
		if ($history > 0) self::clean(-abs($history));
	}
}
?>
