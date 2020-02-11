<?php
/**
*
*/
class FallbackManager
{
	public $deviceDefinitions = "";

	function __construct($deviceDefinition)
	{
		$this->deviceDefinitions = $deviceDefinition;
	}

	function check(){
		//TODO: FIX IT
		/*$allDevicesData = DeviceManager::getAllDevices();
		foreach ($allDevicesData as $deviceKey => $deviceValue) {
			$allSubDevicesData = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
			foreach ($allSubDevicesData as $subDeviceKey => $subDeviceValue) {
				if (!isset($this->deviceDefinitions[$subDeviceValue['type']]["fallBack"])) {
					continue;
				}

				$lastRecord = RecordManager::getLastRecord($subDeviceValue['subdevice_id']);
				$minutes = (time() - $lastRecord['time']) / 60;
				echo $minutes;
				if ( $minutes > 2){
					RecordManager::create($deviceValue['device_id'], $subDeviceValue['type'], $this->deviceDefinitions[$subDeviceValue['type']]["fallBack"]);
				}
			}
		}*/
	}
}
