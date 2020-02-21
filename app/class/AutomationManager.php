<?php

class AutomationManager{
	public static $automation;

	public function remove($automationId) {
		return Db::command ('DELETE FROM automation WHERE automation_id=?', array ($automationId));
	}

	public function deactive($automationId) {
		$automation = Db::loadOne ("SELECT * FROM automation WHERE automation_id=?" , array ($automationId));
		$flipedValue = ($automation['active'] == 1 ? 0 : 1);
		return Db::command ('UPDATE automation SET active = ? WHERE automation_id=?', array ($flipedValue,$automationId));
	}

	public function restart($automationId) {
		return Db::command ('UPDATE automation SET executed = 0 WHERE automation_id=?', array ($automationId));
	}

	public function create ($name, $onDays, $doCode, $ifCode, $automationId = "") {
		$userId = UserManager::getUserData('user_id');
		$scene = array (
			'name' => $name,
			'owner_id' => $userId,
			'on_days' => $onDays,
			'if_something' => $ifCode,
			'do_something' => $doCode,
		);
		try {
			if ($automationId == "") {
				Db::add ('automation', $scene);
			} else {
				Db::edit ('automation', $scene, 'WHERE automation_id = ?', array ($automationId));
			}
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function getAll(){
		return Db::loadAll ("SELECT * FROM automation");

	}

	public function executeAll(){
		global $logManager;

		$automations = Db::loadAll ("SELECT * FROM automation");
		$dayNameNow = strtolower (date('D', time()));

		foreach ($automations as $automation) {
			$onValue = json_decode($automation['if_something'], true);
			$sceneDoJson = $automation['do_something'];
			$actionDays = json_decode($automation['on_days'], true);
			$value = time();
			$run = false;
			$restart = false;

			if ($automation['active'] == 1 && $automation['locked'] != 1){
				Db::edit('automation', array('locked' => 1), 'WHERE automation_id = ?', array($automation['automation_id']));
				if (in_array($dayNameNow, $actionDays)){
					if (in_array($onValue['type'], ['sunSet', 'sunRise', 'time','now'])) {
						if ($onValue['type'] == 'sunSet') {
							$value = date_sunset($value, SUNFUNCS_RET_TIMESTAMP, 50.0755381 , 14.4378005, 90);
						} else if ($onValue['type'] == 'sunRise') {
							$value = date_sunrise($value, SUNFUNCS_RET_TIMESTAMP, 50.0755381 , 14.4378005, 90);
						} else if ($onValue['type'] == 'time') {
							$onValue = explode(':',$onValue['value']);
							$today = date_create('now');
							$onValue = $today->setTime($onValue[0], $onValue[1]);
							$value = $today->getTimestamp();
						}

						if (time() > $value && $automation['executed'] == 0){
							$run = true;
						} else if (time() < $value && $automation['executed'] == 1) { //recovery realowing of automation
							$restart = true;
						}

					} else if ($onValue['type'] == 'outHome') {
						//TODO: Add Ovner to automation
						$userHomeStatus = UserManager::getUserData('at_home', $onValue['value']);
						if ($userHomeStatus == 'false' && $automation['executed'] == 0) {
							$run = true;
						} else if ($userHomeStatus == 'true' &&  $automation['executed'] == 1) {
							$restart = true;
						}
					} else if ($onValue['type'] == 'inHome') {
						//TODO: Add Ovner to automation
						$userHomeStatus = UserManager::getUserData('at_home', $onValue['value']);
						if ($userHomeStatus == 'true' && $automation['executed'] == 0) {
							$run = true;
						} else if ($userHomeStatus == 'false' &&  $automation['executed'] == 1) {
							$restart = true;
						}
					} else if ($onValue['type'] == 'noOneHome') {
						$users = UserManager::getUsers();
						$membersHome = 0;
						foreach ($users as $key => $user) {
							if ($user['at_home'] == 'true'){
								$membersHome++;
							}
						}
						if ($membersHome == 0 && $automation['executed'] == 0) {
							$run = true;
						} else if ($membersHome > 0 && $automation['executed'] == 1){
							$restart = true;
						}
					} else if ($onValue['type'] == 'someOneHome') {
						$users = UserManager::getUsers();
						$membersHome = 0;
						foreach ($users as $key => $user) {
							if ($user['at_home'] == 'true'){
								$membersHome++;
							}
						}
						if ($membersHome == 0 && $automation['executed'] == 1) {
							$restart = true;
						} else if ($membersHome > 0 && $automation['executed'] == 0){
							$run = true;
						}
					} else if ($onValue['type'] == 'atDeviceValue') {

						$subDeviceId = SubDeviceManager::getSubDeviceByMaster($onValue['value']['deviceID'], $onValue['value']['type'])["subdevice_id"];
						$lastValue = RecordManager::getLastRecord($subDeviceId);

						if ($lastValue['value'] == $onValue['value']['value'] && $automation['executed'] == 0) {
							$run = true;

						} else if ($lastValue['value'] != $onValue['value']['value'] && $automation['executed'] == 1){
							$restart = true;

						}
					}

					//finalization
					if ($run) {
						$body = '';

						$sceneDoArray = json_decode($sceneDoJson);
						foreach ($sceneDoArray as $deviceId => $deviceState) {
							RecordManager::create($deviceId, 'on/off', $deviceState);
						}

						$subscribers = NotificationManager::getSubscription();
						$i = 0;

						$notificationMng = new NotificationManager;
						$notificationData = [
							'title' => 'Automatization',
							'body' => 'Automatization '.$automation['name']." was just executed",
							'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
						];

						if ($notificationData != []) {
							$subscribers = $notificationMng::getSubscription();
							foreach ($subscribers as $key => $subscriber) {
								$logManager->write("[NOTIFICATION/AUTOOMATION] SENDING TO" . $subscriber['id'] . " ");
								$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
							}
						}

						$logManager->write("[AUTOMATIONS] automation id ". $automation['automation_id'] . " was executed");
						Db::edit('automation', array('executed' => 1, 'execution_time' => date("Y-m-d H:i:s")), 'WHERE automation_id = ?', array($automation['automation_id']));
					} else if ($restart) {
						$logManager->write("[AUTOMATIONS] automation id ". $automation['automation_id'] . " was restarted");
						Db::edit('automation', array('executed' => 0), 'WHERE automation_id = ?', array($automation['automation_id']));
					}
					Db::edit('automation', array('locked' => 0), 'WHERE automation_id = ?', array($automation['automation_id']));
				}
			}
		}
	}
}
