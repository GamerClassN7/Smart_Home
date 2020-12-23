<?php
class SettingsManager{
	static function getAllValues () {
		return Db::loadAll ("SELECT * FROM settings");
	}

	static function getByName($settingName, $type = '') {
		if ($type != '') return Db::loadOne("SELECT * FROM settings WHERE name = ? AND type = ?", array($settingName, $type));
		return Db::loadOne("SELECT * FROM settings WHERE name = ?", array($settingName));
	}

	static function getSettingGroup($type) {
		return Db::loadAll("SELECT * FROM settings WHERE type=?", array($type));
	}

	public static function create ($name, $value, $type = '') {
		if (!self::getByName($name)){
			$setting = array (
				'name' => $name,
				'value' => $value,
				'type' => $type,
			);
			try {
				Db::add ('settings', $setting);
			} catch(PDOException $error) {
				echo $error->getMessage();
				die();
			}
		}
	}

	public static function update ($name, $value, $type = '') {
		if (!self::getByName($name)){
			self::create($name, $value, $type);
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
