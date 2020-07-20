<?php
class Automation extends Template
{
	function __construct()
	{
		$userManager = new UserManager();
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$automations = [];
		$automationsData = AutomationManager::getAll();
		foreach ($automationsData as $automationKey => $automationData) {
			$doSomething = [];
			foreach (json_decode($automationData['do_something']) as $deviceId => $subDeviceState) {
				$subDeviceMasterDeviceData = DeviceManager::getDeviceById($deviceId);
				$doSomething[$deviceId] = [
					'name' => $subDeviceMasterDeviceData['name'],
					'state' => $subDeviceState,
				];
			}
			//TODO: Transaltion add
			$executionTime = 'never';
			if ($automationData['execution_time'] != '0000-00-00 00:00:00') {
				$executionTime = date(DATEFORMAT,strtotime($automationData['execution_time']));
			}
			$automations[$automationData['automation_id']] = [
				'name' => $automationData['name'],
				'owner_name' => $userManager->getUserId($automationData['owner_id'])['username'],
				'onDays' => json_decode($automationData['on_days']),
				'ifSomething' => $automationData['if_something'],
				'doSomething' => $doSomething,
				'active' => $automationData['active'],
				'execution_time' => $executionTime,
			];
		}

		$approvedSubDevices = [];
		$allDevicesData = DeviceManager::getAllDevices();
		foreach ($allDevicesData as $deviceKey => $deviceValue) {
			if (!$deviceValue['approved']) continue;
			$allSubDevicesData = SubDeviceManager::getAllSubDevices($deviceValue['device_id']);
			foreach ($allSubDevicesData as $subDeviceKey => $subDeviceValue) {
				$approvedSubDevices[$subDeviceValue['subdevice_id']] = [
					'name' => $allDevicesData[$deviceKey]['name'],
					'type' => $subDeviceValue['type'],
					'masterDevice' => $subDeviceValue['device_id'],
				];
			}
		}

		$template = new Template('automation');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Automation');
		$template->prepare('langMng', $langMng);
		$template->prepare('userManager', $userManager);

		$template->prepare('automations', $automations);
		$template->prepare('subDevices', $approvedSubDevices);

		$template->render();
	}
}
