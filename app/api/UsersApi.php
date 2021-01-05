<?php
class UsersApi extends ApiController{
	public function default(){
		//$this->requireAuth();
		$response = null;

		$users = UserManager::getUsers(["user_id", "username", "at_home"]);

		foreach ($users as $key => $user) {
			$response[] = [
				"userName" => $user['username'],
				"homeStatus" => ($user['at_home'] == 'true') ? true : false,
				"avatarUrl" => UserManager::getAvatarUrl($user['user_id']),
			];
		}

		$this->response($response);
	}

	public function status(){
		//$this->requireAuth();
		$response = null;
		$obj = $this->input;
		$atHome = $obj['atHome'];

		$user = UserManager::getUser($obj['user']);
		$userAtHome = $user['at_home'];
		$userId = $user['user_id'];

		if (!empty($user)) {
			if($userAtHome != $atHome){
				UserManager::atHome($userId, $atHome);
			}
		}
		$this->response(['value'=>'OK']);
	}

	public function subscribe(){
		//$this->requireAuth();
		$bearer = $_SERVER['HTTP_AUTHORIZATION'];
		$authManager = new AuthManager();
		$userId = $authManager->getUserId($bearer);

		NotificationManager::addSubscriber($userId, $this->input['pushtoken']);
		$this->response(['value'=>'OK']);
	}
}
