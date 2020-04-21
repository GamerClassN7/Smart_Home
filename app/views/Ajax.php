<?php

class Ajax extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR);
		}

		$is_ajax = 'XMLHttpRequest' == ( $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' );
		if (!$is_ajax){
			header('Location: '  . BASEDIR);
		}

		if (
			isset($_POST['automation_id']) &&
			$_POST['automation_id'] != '' &&
			isset($_POST['action']) &&
			$_POST['action'] != ''
			) {
				$automationId = $_POST['automation_id'];
				//Automation Editation of Automations from Buttons/Details
				switch ($_POST['action']) {
					case 'delete':
						AutomationManager::remove($automationId);
						die();
					break;

					case 'deactive':
						AutomationManager::deactive($automationId);
						die();
					break;

					case 'restart':
						AutomationManager::restart($automationId);
						die();
					break;

					default:
					echo 'no action detected';
				break;
			}
		} else if (
			isset($_POST['subDevice_id']) &&
			$_POST['subDevice_id'] != '' &&
			isset($_POST['action']) &&
			$_POST['action'] != ''
			) {
				$subDeviceId = $_POST['subDevice_id'];
				switch ($_POST['action']) {
					case 'chart':
						$period = $_POST['period'];
						$groupBy = $_POST['group'];
						header('Content-Type: application/json');
						$graphData = ChartManager::generateChartData($subDeviceId, $period, $groupBy);
						echo Utilities::generateGraphJson($graphData['graphType'], $graphData['graphData'], $graphData['graphRange']);
						die();
					break;

					//Change On/Off Device State of Device Button
					case 'change':
						$subDeviceData = SubDeviceManager::getSubDevice($subDeviceId);
						$deviceId = SubDeviceManager::getSubDeviceMaster($subDeviceId)['device_id'];
						if ($subDeviceData['type'] == 'on/off'){
							$lastValue = RecordManager::getLastRecord($subDeviceData['subdevice_id'])['value'];
							RecordManager::create($deviceId, 'on/off', !$lastValue);
							echo (!$lastValue ? 'ON' : 'OFF');
						}
						die();
					break;

					//Waitin for execution of Changet walue for Device Button
					case 'executed':
						echo RecordManager::getLastRecord($subDeviceId)['execuded'];
						die();
					break;

					case 'set':
						$value = $_POST['value'];
						$subDevice = SubDeviceManager::getSubDevice($subDeviceId);
						RecordManager::create($subDevice['device_id'], $subDevice['type'], $value);
						echo 'test id' . $subDevice['device_id'] .$subDevice['type'] . $value ;
						die();
					break;

					default:
					echo 'no action detected';
				break;
			}
		} else if (
			isset($_POST['scene_id']) &&
			$_POST['scene_id'] != '' &&
			isset($_POST['action']) &&
			$_POST['action'] != ''
			) {
				$sceneId = $_POST['scene_id'];
				switch ($_POST['action']) {
					case 'delete':
						SceneManager::delete($sceneId);
						die();
					break;

					case 'execute':
						echo SceneManager::execScene($sceneId);
						die();
					break;

					default:
					echo 'no action detected';
				break;
			}
		} else if (
			isset($_POST['notification']) &&
			$_POST['notification'] != '' &&
			isset($_POST['action']) &&
			$_POST['action'] != ''
			) {
				switch ($_POST['action']) {
					//add suscription to database
					case 'subscribe':
						$subscriptionToken = $_POST['token'];
						NotificationManager::addSubscriber($_SESSION['user']['id'], $subscriptionToken);
						die();
					break;

					case 'sendTest':
						$notificationData = [
							'title' => 'Alert',
							'body' => 'test notification',
							'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
						];
						$notificationMng = new NotificationManager;
						$subscribers = $notificationMng::getSubscription();
						foreach ($subscribers as $key => $subscriber) {
							echo $subscriber['user_id'];
							if ($subscriber['user_id'] != $_SESSION['user']['id']) continue;
							echo $notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
						}
						die();
					break;

					default:
					echo 'no action detected';
				break;
			}
		} else if	(
			isset($_POST['action']) &&
			$_POST['action'] != ''
			) {
				$updateData = [];
				$allDevicesData = DeviceManager::getAllDevices();
				foreach ($allDevicesData as $deviceKey => $deviceValue) {
					$allSubDevices = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
					foreach ($allSubDevices as $key => $subDevicesData) {

						$lastRecord = RecordManager::getLastRecord($subDevicesData['subdevice_id']);
						$parsedValue = $lastRecord['value'] . $subDevicesData['unit'];

						//TODO: udělat parser a ten použít jak v houmu tak zde
						switch ($subDevicesData['type']) {
							case 'on/off':
								$replacementTrue = 'On';
								$replacementFalse = 'Off';
								$operator = '==';
								$breakValue = 1;
							break;

							case 'door':
								$replacementTrue = 'Closed';
								$replacementFalse = 'Open';
								$operator = '==';
								$breakValue = 1;
							break;

							case 'light':
								$replacementTrue = 'Light';
								$replacementFalse = 'Dark';
								$operator = '==';
								$breakValue = 1;
								if ($lastRecord['value'] != 1 && $lastRecord['value'] != 0) { //Digital Light Senzor
									$operator = '<';
									$breakValue = 810;
								}
							break;

							case 'water':
								$replacementTrue = 'Wet';
								$replacementFalse = 'Dry';
								$operator = '==';
								$breakValue = 1;
							break;

							default:
							$replacementTrue = '';
							$replacementFalse = '';
						break;
					}

					if ($replacementTrue != '' && $replacementFalse != '') {
						//parsing last values
						$parsedValue = $replacementFalse;

						if (Utilities::checkOperator($lastRecord['value'], $operator, $breakValue)) {
							$parsedValue = $replacementTrue;
						}
					}

					$updateData[$subDevicesData['subdevice_id']] = [
						'time' => $lastRecord['time'],
						'value' => $parsedValue,
					];
				}
			}

			//TODO: PRO JS VRACET DATA
			echo json_encode($updateData, JSON_PRETTY_PRINT);
		}
	}
}
