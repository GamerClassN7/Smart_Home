<?php
$files = scandir('app/class/');
$files = array_diff($files, array('.', '..'));
foreach($files as $file) {
	include_once 'app/class/'.  $file;
}

class Ajax extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./');
		}

		$is_ajax = 'XMLHttpRequest' == ( $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' );
		if (!$is_ajax){
			header('Location: ./');
		}

		if (isset($_POST['subDevice_id'])){
			$subDeviceId = $_POST['subDevice_id'];
			if (isset($_POST['lastRecord'])){
				echo RecordManager::getLastRecord($subDeviceId)['execuded'];
				die();
			}
			$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
			$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
			if ($subDeviceData['type'] == 'on/off'){
				//TODO: Pridelat kontrolu změnit stav pouze pokud se poslední [executed] stav != novému
				if (RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'] == 0){
					RecordManager::create($deviceId, 'on/off', 1);
					echo 'ON';
				}else{
					RecordManager::create($deviceId, 'on/off', 0);
					echo 'OFF';
				}
			}

		}  else if (isset($_POST['automation_id'])){
			$automationId = $_POST['automation_id'];
			if (isset($_POST['action']) && $_POST['action'] == 'delete') {
				AutomationManager::remove($automationId);
			}else {
				AutomationManager::deactive($automationId);
			}
		} else if (isset($_POST['subDevice']) && isset($_POST['action']) && $_POST['action'] == "chart") {
			//TODO lepe rozstrukturovat
			$subDeviceId = $_POST['subDevice'];
			$period = $_POST['period'];
			$groupBy = $_POST['group'];

			$subDevice = SubDeviceManager::getSubDevice($subDeviceId);
			$records = RecordManager::getAllRecordForGraph($subDeviceId, $period, $groupBy);

			$array = array_column($records, 'value');
			$arrayTime = array_column($records, 'time');
			$output = [];

			foreach ($array as $key => $value) {
				$output[$key]['y'] = $value;
				if ($subDevice['type'] == 'light'){
					if ($value > 810){
						$output[$key]['y'] = 1;
					} else {
						$output[$key]['y'] = 0;
					}
				}
				$timeStamp = new DateTime($arrayTime[$key]);
				$output[$key]['t'] = $timeStamp->format("Y-m-d") . 'T' . $timeStamp->format("H:i:s") . 'Z';
			}

			$data = json_encode($output);
			$data = $output;
			$arrayTimeStamps = array_column($records, 'time');
			foreach ($arrayTimeStamps as $key => $value) {
				$arrayTimeStamps[$key] = (new DateTime($value))->format(TIMEFORMAT);
			}

			$labels = json_encode($arrayTimeStamps);
			$range = RANGES[$subDevice['type']];
			$graphType = $range['graph'];

			header('Content-Type: application/json');

			echo Utilities::generateGraphJson($range['graph'], $data, $range);
			die();
		} else if (isset($_POST['action']) && $_POST['action'] == "getState") {
			//State Update
			$roomsData = RoomManager::getAllRooms();
			$subDevices = [];
			foreach ($roomsData as $roomKey => $roomsData) {
				$devicesData = DeviceManager::getAllDevicesInRoom($roomsData['room_id']);
				foreach ($devicesData as $deviceKey => $deviceData) {
					$subDevicesData = SubDeviceManager::getAllSubDevices($deviceData['device_id']);
					foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
						$lastRecord = RecordManager::getLastRecord($subDeviceData['subdevice_id']);
						$parsedValue = round($lastRecord['value']);
						//TODO: Předelat na switch snažší přidávání
						/*Value Parsing*/
						if ($subDeviceData['type'] == "on/off") {
							$parsedValue = ($parsedValue == 1 ? 'ON' : 'OFF');
						}
						if ($subDeviceData['type'] == "light") {
							$replacementTrue = 'Light';
							$replacementFalse = 'Dark';
							if ($parsedValue != 1){
								//Analog Reading
								$parsedValue = ($parsedValue <= 810 ? $replacementTrue : $replacementFalse);
							} else {
								//Digital Reading
								$parsedValue = ($parsedValue == 0 ? $replacementTrue : $replacementFalse);
							}
						}
						if ($subDeviceData['type'] == "door") {
							$replacementTrue = 'Closed';
							$replacementFalse = 'Opened';
							$parsedValue = ($parsedValue == 1 ? $replacementTrue : $replacementFalse);
						}
						$subDevices[$subDeviceData['subdevice_id']] = [
							'value' => $parsedValue .$subDeviceData['unit'],
							'time' => $lastRecord['time'],
						];
					}
				}
			}
			echo json_encode($subDevices);
			die();
		} else if (isset($_POST['scene_id'])) {
			$sceneId = $_POST['scene_id'];
			if (isset($_POST['action']) && $_POST['action'] == 'delete') {
				SceneManager::delete($sceneId);
			}else {
				echo SceneManager::execScene($sceneId);
			}
		}

		die();

	}
}

/*$JSON = '{
"type": "line",
"data": {
"labels": ' . $data . ',
"datasets": [{
"data": ' . $data . ',
"backgroundColor": "#7522bf",
"lineTension": 0,
"radius": 5
}]
},
"options": {
"legend": {
"display": false
},
"scales": {
"xAxes": [{
"type": "time",
"time": {
"unit": "hour"
}
}],
"yAxes": [{
"ticks": {
"min": ' . $range['min'] . ',
"max": ' . $range['max'] . ',
"steps": ' . $range['scale'] . '
}
}]
},
"tooltips": {
"enabled": false
},
"hover": {
"mode": null
}
}
}';*/
