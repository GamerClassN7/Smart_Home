<?php
class SettingsManager{
	static function getAllValues () {
		return Db::loadAll ("SELECT * FROM settings");
	}

	static function getByName($settingName) {
		return Db::loadOne("SELECT * FROM settings WHERE name = ?", array($settingName));
	}

	public static function create ($name, $value) {
		$setting = array (
			'name' => $name,
			'value' => $value,
		);
		try {
			Db::add ('settings', $setting);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public static function update ($name, $value) {
		try {
			Db::edit ('settings', ['value' => $value], 'WHERE name = ?', array($name));
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}
}
?>
