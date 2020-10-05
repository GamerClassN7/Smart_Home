<?php
class WidgetApi extends ApiController{

	public function run($subDeviceId){
		//$this->requireAuth();

		$response = null;
		if (RecordManager::getLastRecord($subDeviceId)['execuded'] === 0) {
			throw new Exception("Unreachable", 409);
		}

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		if ($subDeviceData['type'] == 'on/off'){
			$lastValue = RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'];
			RecordManager::create($subDeviceData['device_id'], 'on/off', (int) !$lastValue);
			$response = !$lastValue;
		} else {
			throw new Exception("Bad Request", 403);
		}

		$i = 0;
		$timeout = 20;
		while (RecordManager::getLastRecord($subDeviceId)['execuded'] == 0){
			if ($i == $timeout) {
				throw new Exception("Timeout", 444);
			}
			$i++;
			usleep(250000);
		}
		$this->response(['value' => $response]);
	}

	public function detail($subDeviceId){
		//$this->requireAuth();
		$response = null;
		$connectionError = true;

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		$deviceData = DeviceManager::getDeviceById($subDeviceData['device_id']);
		$events = RecordManager::getLastRecord($subDeviceId, 5);

		$LastRecordTime = new DateTime($events[4]['time']);
		$niceTime = Utilities::ago($LastRecordTime);

		$interval = $LastRecordTime->diff(new DateTime());
		$hours   = $interval->format('%h');
		$minutes = $interval->format('%i');
		$lastSeen = ($hours * 60 + $minutes);

		if (
			$lastSeen < $deviceData['sleep_time'] ||
			$subDeviceData['type'] == "on/off" ||
			$subDeviceData['type'] == "door" ||
			$subDeviceData['type'] == "wather"
			) {
				$connectionError = false;
			}

			$labels = [];
			$values = [];
			foreach ($events as $key => $event) {
				$labels[] = (new DateTime($event['time']))->format('H:i');
				$values[] = [
					$event['time'],
					$event['value'],
				];
			}

			$response = [
				'records'=> $events,
				'graph'=> [
					'labels' => $labels,
					'values' => $values,
					'min' => RANGES[$subDeviceData['type']]['min'],
					'max' => RANGES[$subDeviceData['type']]['max'],
					'scale' => RANGES[$subDeviceData['type']]['scale'],
					'graph' => RANGES[$subDeviceData['type']]['graph'],
				],
				'comError' => $connectionError,
				'lastConnectionTime' => (empty($niceTime) ? "00:00" : $niceTime),
			];

			$this->response($response);
		}
	}
