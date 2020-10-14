<?php
class VirtualDeviceManager
{
	public function fetchEnabled($deviceId = null, $subDeviceId = null){
		$sleepTime = DeviceManager::getDeviceById($deviceId)['sleep_time'];

		$LastRecordTime = new DateTime(RecordManager::getLastRecord($subDeviceId, 1)['time']);
		$interval = $LastRecordTime->diff(new DateTime());
		$hours   = $interval->format('%h');
		$minutes = $interval->format('%i');
		$lastSeen = ($hours * 60 + $minutes);

		if ($lastSeen > $sleepTime || $sleepTime == 0) {
			return true;
		}

		return false;
	}
}
