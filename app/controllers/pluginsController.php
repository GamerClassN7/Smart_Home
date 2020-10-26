<?php
if (!empty ($_POST)){
	if (
		isset($_POST['name']) &&
		$_POST['name'] != ''
	){
		if ($_POST['status'] == "true") {
			if (file_exists ($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/!' . $_POST['name'] . ".php")) {
				rename($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/!' . $_POST['name'] . ".php", $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/' . $_POST['name'] . ".php");
			}
		} else {
			if (file_exists ($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/' . $_POST['name'] . ".php")) {
				rename($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/' . $_POST['name'] . ".php", $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/!' . $_POST['name'] . ".php");
			}
		}
		header('Location: ./plugins');
		die();
	}
}
