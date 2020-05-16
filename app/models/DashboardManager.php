<?php
class DashboardManager{
	public static $devices;


	static function getAllDashboards ($userId) {
		return Db::loadAll ("SELECT * FROM dashboard WHERE user_id=?", array($userId));
	}

	static function getAllSubDevices ($userId) {
		return Db::loadAll ("SELECT * FROM subdevices WHERE subdevice_id IN (SELECT subdevice_id FROM dashboard WHERE user_id=?)", array($userId));
	}

	static function getSubDevice ($userId, $subDeviceId) {
		return Db::loadOne ("SELECT * FROM subdevices WHERE subdevice_id = (SELECT subdevice_id FROM dashboard WHERE user_id=? AND subdevice_id = ? )", array($userId, $subDeviceId));
	}

	static function Add ($subDeviceId) {
		if (self::getSubDevice(UserManager::getUserData('user_id'), $subDeviceId) == null){

			// to do: pokud existuje nepridej
			//
			//
			$dashboardItem = array (
				'user_id' => UserManager::getUserData('user_id'),
				'subdevice_id' => $subDeviceId,
			);
			try {
				Db::add ('dashboard', $dashboardItem);
			} catch(PDOException $error) {
				echo $error->getMessage();
				die();
			}
		}
	}

	static function Remove ($subDeviceId){
		$userId = UserManager::getUserData('user_id');
		Db::command ('DELETE FROM dashboard WHERE subdevice_id=? AND user_id = ?', array ($subDeviceId, $userId));
	}
}
