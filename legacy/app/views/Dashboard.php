<?php
class Dashboard extends Template
{
	function __construct()
	{
		global $userManager;
		global $langMng;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR . 'login');
		}

		$template = new Template('dashboard');

		$dashboard = [];
		$dashboardData = DashboardManager::getAllDashboards($userManager->getUserData('user_id'));
		foreach ($dashboardData as $dashboardItemKey => $dashboardItemValue) {
			$subDeviceData = SubDeviceManager::getSubDevice($dashboardItemValue['subdevice_id']);
			$deviceData = SubDeviceManager::getSubDeviceMaster($dashboardItemValue['subdevice_id']);

			$lastRecord = RecordManager::getLastRecord($dashboardItemValue['subdevice_id']);
			$parsedValue = $lastRecord['value'];

			//TODO: Opravit aby to bylo stejné parsování jako na HOME
			if ($subDeviceData['type'] == "on/off") {
				$parsedValue = ($parsedValue == 1 ? 'ON' : 'OFF');
			}
			if ($subDeviceData['type'] == "light") {
				$parsedValue = ($parsedValue == 1 ? 'Light' : 'Dark');
			}

			$dashboard[$dashboardItemValue['dashboard_id']] = [
				'icon' => $deviceData['icon'],
				'id' => $subDeviceData['subdevice_id'],
				'masterId' => $deviceData['device_id'],
				'name' => $deviceData['name'],
				'type' => $subDeviceData['type'],
				'unit' => $subDeviceData['unit'],
				'lastRecord' => [
					'value' => $parsedValue,
				],
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


		if (isset($_POST['deviceId'])){

			$deviceData = DeviceManager::getDeviceById($_POST['deviceId']);

			$subDevices = [];
			$subDevicesData = SubDeviceManager::getAllSubDevices($_POST['deviceId']);

			foreach ($subDevicesData as $subDeviceKey => $subDeviceData) {
				$subDevices[$subDeviceData['subdevice_id']] = [
					'type' => $subDeviceData['type'],
					'unit' => $subDeviceData['unit'],
				];
			}

			$device = [
				'id' => $deviceData['device_id'],
				'name' => $deviceData['name'],
				'token' => $deviceData['token'],
				'icon' => $deviceData['icon'],
				'subDevices' => $subDevices,
			];
			$template->prepare('deviceData', $device);
		}

		$template->prepare('baseDir', BASEDIR);
			$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Nástěnka');
		$template->prepare('langMng', $langMng);
		$template->prepare('dashboard', $dashboard);
		$template->prepare('subDevices', $approvedSubDevices);

		$template->render();
	}
}
