<?php
class Scene extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./');
		}

		$template = new Template('scene');
		$template->prepare('title', 'Scény');
		$template->prepare('lang', $lang);

		$scenes = [];
		foreach (SceneManager::getAllScenes() as $sceneId => $sceneData) {
			$doSomething = [];
			foreach (json_decode($sceneData['do_something']) as $subdeviceId => $subDeviceState) {
				$subDeviceMasterDeviceData = SubDeviceManager::getSubDeviceMaster($subdeviceId);
				$doSomething[$subdeviceId] = [
					'name' => $subDeviceMasterDeviceData['name'],
					'state' => $subDeviceState,
				];
			}
			$scenes[$sceneData['scene_id']] = [
				"name" => $sceneData['name'],
				"icon" => $sceneData['icon'],
				"do_something" => $doSomething,

			];
		}

		$template->prepare('scenes', $scenes);

		$approvedSubDevices = [];
		$allDevicesData = DeviceManager::getAllDevices();
		foreach ($allDevicesData as $deviceKey => $deviceValue) {
			if (!$deviceValue['approved']) continue;
			$allSubDevicesData = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
			foreach ($allSubDevicesData as $subDeviceKey => $subDeviceValue) {
				if ($subDeviceValue['type'] != 'on/off') continue;
				$approvedSubDevices[$subDeviceValue['subdevice_id']] = [
					'name' => $allDevicesData[$deviceKey]['name'],
				];
			}
		}
		$template->prepare('subDevices', $approvedSubDevices);

		$approvedSubDevices = [];
		$allDevicesData = DeviceManager::getAllDevices();
		foreach ($allDevicesData as $deviceKey => $deviceValue) {
			if (!$deviceValue['approved']) continue;
			$allSubDevicesData = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
			foreach ($allSubDevicesData as $subDeviceKey => $subDeviceValue) {
				if ($subDeviceValue['type'] != 'on/off') continue;
				$approvedSubDevices[$subDeviceValue['subdevice_id']] = [
					'name' => $allDevicesData[$deviceKey]['name'],
				];
			}
		}
		$template->prepare('subDevices', $approvedSubDevices);

		if (isset($_POST['devices'])){
			$devices = $_POST['devices'];
			$devicesOBJ = [];
			foreach ($devices as $deviceId) {
				$deviceData = DeviceManager::getDeviceById($deviceId);
				$subdeviceData = SubDeviceManager::getSubDeviceByMaster($deviceId, 'on/off');
				$devicesOBJ[$deviceId] = [
					'name' => $deviceData['name'],
					'setableSubDevices' => $subdeviceData['subdevice_id'],
				];
			}
			$template->prepare('setStateFormDevices', $devicesOBJ);
			$template->prepare('sceneName', $_POST['sceneName']);
			$template->prepare('sceneIcon', $_POST['sceneIcon']);
		}
		$template->render();
	}
}
