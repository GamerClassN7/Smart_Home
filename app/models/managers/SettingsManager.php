<?php
class SettingsManager{
	static function getAllValues () {
		return Db::loadAll ("SELECT * FROM settings");
	}

	static function getByName($settingName, $group = '') {
		if ($group != '') return Db::loadOne("SELECT * FROM settings WHERE name = ? AND group = ?", array($settingName, $group));
		return Db::loadOne("SELECT * FROM settings WHERE name = ?", array($settingName));
	}

	static function getSettingGroup($group) {
		return Db::loadAll("SELECT * FROM settings WHERE group = ?", array($group));
	}

	public static function create ($name, $value, $group = '') {
		$setting = array (
			'name' => $name,
			'value' => $value,
			'group' => $group,
		);
		try {
			Db::add ('settings', $setting);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function update ($name, $value, $group = '') {
		if ($this.getByName($name)){
			$this->create($name, $value, $group);
		} else {
			try {
				Db::edit ('settings', [
					'value' => $value
				], 'WHERE name = ?', array($name));
			} catch(PDOException $error) {
				echo $error->getMessage();
				die();
			}
		}
	}
}
?>
