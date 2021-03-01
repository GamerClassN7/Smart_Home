<?php
class PluginManager
{
	public function load(){
        $dir = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . '/backup/';

        $pluginsFiles = scandir ($dir);
		foreach ($pluginsFiles as $key => $pluginFile) {
            $className = str_replace(".zip", "", $pluginsFiles);
            if(class_exists($className)){
                (new $className)->make();
            }
        }

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
