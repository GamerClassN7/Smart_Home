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

	public function detail($subDeviceId){
		//$this->requireAuth();
		$response = null;
		$connectionError = true;

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		$deviceData = DeviceManager::getDeviceById($deviceId);
		$events = RecordManager::getLastRecord($subDeviceId, 5);

		$LastRecordTime = new DateTime($$events[4]['time']);
		$niceTime = Utilities::ago($LastRecordTime);

		$interval = $LastRecordTime->diff(new DateTime());
		$hours   = $interval->format('%h');
		$minutes = $interval->format('%i');
		$lastSeen = ($hours * 60 + $minutes);

		if (
			$lastSeen < $deviceData['sleep_time'] ||
			$subDeviceData['type'] == "on/off" ||
			$subDeviceData['type'] == "door"
		) {
			$connectionError = false;
		}

		$response = [
			'records'=> $events,
			'comError' => $connectionError,
			'lastConnectionTime' => (empty($niceTime) ? "00:00" : $niceTime),
		];

		$this->response($response);
	}
}
