<?php
class WidgetApi extends ApiController
{

	public function run($subDeviceId)
	{
		//$this->requireAuth();

		$response = null;
		if (RecordManager::getLastRecord($subDeviceId)['execuded'] === 0) {
			throw new Exception("Unreachable", 409);
		}

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		if ($subDeviceData['type'] == 'on/off') {
			$lastValue = RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'];
			RecordManager::create($subDeviceData['device_id'], 'on/off', (int) !$lastValue);
			$response = !$lastValue;
		} else {
			throw new Exception("Bad Request", 403);
		}

		$i = 0;
		$timeout = 20;
		while (RecordManager::getLastRecord($subDeviceId)['execuded'] == 0) {
			if ($i == $timeout) {
				throw new Exception("Timeout", 444);
			}
			$i++;
			usleep(250000);
		}
		$this->response(['value' => $response]);
	}

	public function detail($subDeviceId)
	{
		//$this->requireAuth();
		$response = null;
		$connectionError = true;

		$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
		$deviceData = DeviceManager::getDeviceById($subDeviceData['device_id']);

		//TODO: zeptat se @Patrik Je Graf Dobře Seřazený na DESC ?
		$events = RecordManager::getAllRecordForGraph($subDeviceId);
		if ( count($events) == 0){
			throw new Exception("No Records", 404);
		}

		$LastRecordTime = new DateTime(reset($events)['time']);
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
			$recordDatetime = new DateTime($event['time']);
			if ($key == 0){
				$labels[] = 'now';
			} else {
				$labels[] = $recordDatetime->format('H:i');
			}
			$values[] = [
				'y' => $event['value'],
				't' => $recordDatetime->getTimestamp() * 1000,
			];
		}

		$response = [
			'records' => $events,
			'graph' => [
				'type' => $this->getDeviceConfig($subDeviceData['type'])['graph'],
				'data' => [
					'labels' => $labels,
					'datasets' => [[
						//'label' => 'FUCK you',
						'data' => $values,
					]],
				],
				'options' => [
					'scales' => [
						'xAxis' => [[
							'type' => 'time',
							'distribution' => 'linear',
						]],
						'yAxes' => [[
							'ticks' => [
								'min' => $this->getDeviceConfig($subDeviceData['type'])['min'],
								'max' => $this->getDeviceConfig($subDeviceData['type'])['max'],
								'steps' => $this->getDeviceConfig($subDeviceData['type'])['scale'],
							]
						]]
					],
					'legend' => [
						'display' => false
					],
					'tooltips' => [
						'enabled' => true
					],
					'hover' => [
						'mode' => true
					],
				],
			],
			'comError' => $connectionError,
			'lastConnectionTime' => (empty($niceTime) ? "00:00" : $niceTime),
		];

		$this->response($response);
	}

	private function getDeviceConfig($type){
		if (isset(RANGES[$type])){
			return RANGES[$type];
		}
		return RANGES[''];
	}
}
