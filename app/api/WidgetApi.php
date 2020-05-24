<?php
class WidgetApi extends ApiController{

	public function default($subDeviceId){
		//$this->requireAuth();
		$response = null;

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		if ($subDeviceData['type'] == 'on/off'){
			$lastValue = RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'];
			RecordManager::create($subDeviceData['device_id'], 'on/off', !$lastValue);
			$response = !$lastValue;
		}

		$this->response($response);
	}
}
