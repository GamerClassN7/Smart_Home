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
			'if_something' => $doCode,
			'do_something' => $ifCode,
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
		global $dateTimeOffset;
		$automations = Db::loadAll ("SELECT * FROM automation");
		$dayNameNow = strtolower (date('D', time()));
		foreach ($automations as $automation) {
			$onValue = $automation['if_something'];
			$sceneDoJson = $automation['do_something'];
			$actionDays = json_decode($automation['on_days'], true);
			$value = time();

			if ($automation['active'] != 0){
				if (in_array($dayNameNow, $actionDays)){
					switch ($onValue) {
						case 'sunSet':
						$value = date_sunset($value, SUNFUNCS_RET_TIMESTAMP, 50.0755381 , 14.4378005, 90, $dateTimeOffset);
						break;
						case 'sunRise':
						$value = date_sunrise($value, SUNFUNCS_RET_TIMESTAMP, 50.0755381 , 14.4378005, 90, $dateTimeOffset);
						break;
						case 'time':
						$onValue = explode(':',$onValue);
						$today = date_create('now');
						$onValue = $today->setTime($onValue[0], $onValue[1]);
						$value = $today->getTimestamp();
						break;
						default:
						break;
					}

					if (time() > $value){
						if ($automation['executed'] == 0){
							$sceneDoArray = json_decode($sceneDoJson);
							foreach ($sceneDoArray as $deviceId => $deviceState) {
								RecordManager::create($deviceId, 'on/off', $deviceState);
							}
							Db::edit('automation', array('executed' => 1), 'WHERE automation_id = ?', array($automation['automation_id']));
						}
					} elseif (time() < $value && $automation['executed'] = 1) { //recovery realowing of automation
						Db::edit('automation', array('executed' => 0), 'WHERE automation_id = ?', array($automation['automation_id']));
					}
				}
			}
		}
	}
}
