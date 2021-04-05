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
			RecordManager::create($subDeviceData['device_id'], 'on/off', (int) !$lastValue, "vue-app");
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

	public function detail($subDeviceId, $period = "day")
	{
		//$this->requireAuth();

		$groupBy = [
			"year" => "month",
			"month" => "day",
			"day" => "hour",
			"hout" => "minute",
		];

		$response = null;
		$subDeviceData = SubDeviceManager::getSubdeviceDetailById($subDeviceId);


		//TODO: zeptat se @Patrik Je Graf Dobře Seřazený na DESC ?
		$events = RecordManager::getAllRecordForGraph($subDeviceId, $period, $groupBy[$period]);
		if ( count($events) == 0){
			throw new Exception("No Records", 404);
		}

		//Striping executed value from dataset if pasiv device such as Senzor ETC
		if ($subDeviceData['type'] != "on/off") {
			foreach ($events as $key => $event) {
				unset($events[$key]['execuded']);
			}
		}

		$LastRecordTime = new DateTime(reset($events)['time']);
		$niceTime = Utilities::ago($LastRecordTime);

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
			'room_id' => $subDeviceData['room_id'],
			'device_id' => $subDeviceData['device_id'],
			'lastConnectionTime' => (empty($niceTime) ? "00:00" : $niceTime),
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

		];

		//TODO: Make Cleaner
		if (isset(RANGES[$subDeviceData['type']])){
			$response['graph']['options']['scales']['yAxes'] = [[
				'ticks' => [
					'min' => RANGES[$subDeviceData['type']]['min'],
					'max' => RANGES[$subDeviceData['type']]['max'],
					'steps' => RANGES[$subDeviceData['type']]['scale'],
				]
			]];
		}

		$this->response($response);
	}

	private function getDeviceConfig($type){
		if (isset(RANGES[$type])){
			return RANGES[$type];
		}
		return RANGES[''];
	}

	public function edit($subDeviceId)
	{
		$this->requireAuth();
		$allow = ["icon", "name"];

		$response = null;
		$obj = $this->input;

		foreach ($obj as $key => $value) {
			if (!in_array($key, $allow)){
				unset($obj[$key]);
			}
		}

		$subDeviceData = SubDeviceManager::edit($subDeviceId, $obj);

		$response = [
			"value" => "OK"
		];
		
		$this->response($response);
	}


}
