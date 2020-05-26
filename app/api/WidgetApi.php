<?php
class WidgetApi extends ApiController{

	public function run($subDeviceId){
		//$this->requireAuth();
		$response = null;

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		if ($subDeviceData['type'] == 'on/off'){
			$lastValue = RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'];
			RecordManager::create($subDeviceData['device_id'], 'on/off', !$lastValue);
			$response = !$lastValue;
		}

		$this->response(['value' => $response]);
	}

	public function check($subDeviceId){
		//$this->requireAuth();
		$response = null;
		$lastRecord = RecordManager::getLastRecord($subDeviceId);

		$response = [
			'executet' => $lastRecord['execuded'],
			'value' => $lastRecord['value'],
		];

		$this->response($response);
	}
}
