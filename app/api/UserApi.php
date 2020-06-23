<?php
class UsersApi extends ApiController{
	public function default(){
		//$this->requireAuth();
		$response = null;

		$users = UserManager::getUsers(["user_id", "username", "at_home"]);

		foreach ($users as $key => $user) {
			$response[] = [
				"userName" => $users['username'],
				"homeStatus" => ($users['at_home']) ? true : false,
				"avatarUrl" => getAvatarUrl($value['user_id']),
			];
		}

		$this->response($response);
	}
}
