<?php
class SceneManager{
	public static $scenes;

	public function create ($icon, $name, $doCode) {
		$scene = array (
			'icon' => $icon,
			'name' => $name,
			'do_something' => $doCode,
		);
		try {
			Db::add ('scenes', $scene);
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function getAllScenes () {
		return Db::loadAll ("SELECT * FROM scenes");
	}

	public function getScene ($sceneId) {
		return Db::loadOne("SELECT * FROM scenes WHERE scene_id = ?", array($sceneId));
	}

	public function execScene ($sceneId) {
		$sceneData = SceneManager::getScene($sceneId);
		$sceneDoJson = $sceneData['do_something'];
		$sceneDoArray = json_decode($sceneDoJson);
		foreach ($sceneDoArray as $deviceId => $deviceState) {
			RecordManager::create($deviceId, 'on/off', $deviceState);
		}
		return true;
	}

	public function delete($sceneId){
		Db::command ('DELETE FROM scenes WHERE scene_id=?', array ($sceneId));
	}
}
?>
