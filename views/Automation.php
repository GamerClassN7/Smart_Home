<?php
$files = scandir('class/');
$files = array_diff($files, array('.', '..'));
foreach($files as $file) {
	include_once 'class/'.  $file;
}

class Automation extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./login');
		}

		$automations = [];
		$automationsData = AutomationManager::getAll();
		foreach ($automationsData as $automationKey => $automationData) {
			$automations[$automationData['automation_id']] = [
				'name' => '',
				'onDays' => implode(', ',json_decode($automationData['on_days'])),
				'ifSomething' => $automationData['if_something'],
				'doSomething' => $automationData['do_something'],
				'active' => $automationData['active'],
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
				];
			}
		}

		$template = new Template('automation');
		$template->prepare('title', 'Automation');
		$template->prepare('lang', $lang);
		$template->prepare('automations', $automations);
		$template->prepare('subDevices', $approvedSubDevices);

		$template->render();
	}
}
