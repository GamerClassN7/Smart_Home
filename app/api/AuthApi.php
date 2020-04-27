<?php
class AuthApi {
	public function login(){
		$token = (new ApiManager)->getToken($this->input->username,$this->input->password);
		if (!$token) {
			throw new Exception("Auth failed", 401);
		}
		$this->response(['token' => $token]);
	}

	public function logout(){
		$authenticationBearrer = $_SERVER['HTTP_AUTHORIZATION'];
		if (!(new ApiManager)->deleteToken($authenticationBearrer)) {
			throw new Exception("logout Failed", 401);
		}
	}

	public function registration(){

	}

	public function restartPassword(){

	}
}
