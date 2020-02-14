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
		$allDevicesData = DeviceManager::getAllDevices();
		foreach ($allDevicesData as $deviceKey => $deviceValue) {
			$allSubDevicesData = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
			foreach ($allSubDevicesData as $subDeviceKey => $subDeviceValue) {
				if (!isset($this->deviceDefinitions[$subDeviceValue['type']]["fallBack"])) {
					continue;
				}

				$lastRecord = RecordManager::getLastRecord($subDeviceValue['subdevice_id']);
				if ($lastRecord["value"] == $this->deviceDefinitions[$subDeviceValue['type']]["fallBack"]) {
					return;
				}

				$minutes = (strtotime($lastRecord['time']) - time()) / 60;
				if ( $minutes > 3){
					RecordManager::create($deviceValue['device_id'], $subDeviceValue['type'], $this->deviceDefinitions[$subDeviceValue['type']]["fallBack"]);
				}
			}
		}
	}
}
