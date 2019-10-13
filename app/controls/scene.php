<?php

if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['submitFinal']) && $_POST['submitFinal'] != "") {
		SceneManager::create($_POST['sceneIcon'], $_POST['sceneName'], json_encode($_POST['devices']));
		header('Location: ' . BASEDIR . strtolower(basename(__FILE__, '.php')));
		die();
	}

	//Debug
	if (DEBUGMOD == 1) {
		echo '<pre>';
		var_dump($_POST);
		echo '</pre>';
		echo '<a href="/' . BASEDIR . strtolower(basename(__FILE__, '.php')).'">CONTINUE</a>';
		die();
	}
}
