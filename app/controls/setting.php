<?php
if (isset($_POST) && !empty($_POST)){
	if (isset($_POST['submitPasswordChange']) && $_POST['submitPasswordChange'] != "") {
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword1'];
		$newPassword2 = $_POST['newPassword2'];
		UserManager::changePassword($oldPassword, $newPassword, $newPassword2);
		//TODO: pridat odhlášení
	}
}
