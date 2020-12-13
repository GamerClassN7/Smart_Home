<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['submitPasswordChange']) && $_POST['submitPasswordChange'] != "") {
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword1'];
		$newPassword2 = $_POST['newPassword2'];
		UserManager::changePassword($oldPassword, $newPassword, $newPassword2);
		header('Location: ' . BASEDIR . 'logout');
		die();
	} else if (isset($_POST['submitCreateUser']) && $_POST['submitCreateUser'] != "") {
		$userName = $_POST['userName'];
		$password = $_POST['userPassword'];
		UserManager::createUser($userName, $password);
		header('Location: ' . BASEDIR . 'setting');
		die();
	}
}
