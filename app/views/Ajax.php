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
				echo "test";
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
		}
	}
}
