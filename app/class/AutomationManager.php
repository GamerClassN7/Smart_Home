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

	public function create ($name, $onDays, $doCode, $ifCode) {
		$scene = array (
			'name' => $name,
			'on_days' => $onDays,
			'if_something' => $ifCode,
			'do_something' => $doCode,
		);
		try {
			Db::add ('automation', $scene);
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

			if ($automation['active'] != 0){
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

						/*
						Echo "Spouštění Automatizace";
						echo "Aktual" . date( "HH:mm", strtotime(time()));
						echo "Run At" . date( "HH:mm", strtotime($value));
						*/

						if (time() > $value){
							if ($automation['executed'] == 0){
								$run = true;
							} else if (time() < $value && $automation['executed'] = 1) { //recovery realowing of automation
								$restart = true;
							}
						}
					} else if ($onValue['type'] == 'outHome') {

					} else if ($onValue['type'] == 'inHome') {

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
						/*echo "Someone Home". '<br>';
						echo "at home" . $membersHome. '<br>';
						echo "run" . $run. '<br>';
						echo "restart" . $restart. '<br>';*/

					}

					//finalization
					if ($run) {
						$sceneDoArray = json_decode($sceneDoJson);
						foreach ($sceneDoArray as $deviceId => $deviceState) {
							RecordManager::create($deviceId, 'on/off', $deviceState);
						}
						$logManager->write("[AUTOMATIONS] automation id ". $automation['automation_id'] . " was executed");
						Db::edit('automation', array('executed' => 1), 'WHERE automation_id = ?', array($automation['automation_id']));
					} else if ($restart) {
						$logManager->write("[AUTOMATIONS] automation id ". $automation['automation_id'] . " was restarted");
						Db::edit('automation', array('executed' => 0), 'WHERE automation_id = ?', array($automation['automation_id']));
					}
				}
			}
		}
	}
}