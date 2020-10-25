<?php
if (!empty ($_POST)){
	if (
		isset($_POST['name']) &&
		$_POST['name'] != '' &&
		isset($_POST['actualStatus'])
	){
		if ($_POST['actualStatus']) {
			rename($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/' . $_POST['name'] . ".php", $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/!' . $_POST['name'] . ".php");
		} else {
			rename($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/!' . $_POST['name'] . ".php", $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/' . $_POST['name'] . ".php");
		}
		header('Location: ./plugins');
		die();
	}
}
