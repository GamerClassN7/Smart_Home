<?php
class UsersApi extends ApiController{
	public function default(){
		//$this->requireAuth();
		$response = null;

		$users = UserManager::getUsers(["user_id", "username", "at_home"]);

		foreach ($users as $key => $user) {
			$response[] = [
				"userName" => $user['username'],
				"homeStatus" => ($user['at_home']) ? true : false,
				"avatarUrl" => UserManager::getAvatarUrl($user['user_id']),
			];
		}

		$this->response($response);
	}
}
