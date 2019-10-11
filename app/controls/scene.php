<?php

if (isset($_POST) && !empty($_POST)){
	if (sset($_POST['devices']) && $_POST['devices'] != '') {
		SceneManager::create($_POST['sceneIcon'], $_POST['sceneName'], json_encode($_POST['devices']));
	}

	//Debug
	if (DEBUGMOD == 1) {
		echo '<pre>';
		var_dump($_POST);
		echo '</pre>';
		echo '<a href="/' . BASEDIR . strtolower(basename(__FILE__, '.php')).'">CONTINUE</a>';
		die();
	}

	header('Location: ' . BASEDIR . strtolower(basename(__FILE__, '.php')));
	die();
}
