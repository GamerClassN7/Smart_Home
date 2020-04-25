<?php
class ApiController {
	protected $input;
	protected $authenticated;

	function __construct() {
		$this->authenticated = false;

		$input = file_get_contents('php://input');
		if(empty($input)){
			$this->input = NULL;
		}else{
			$this->input = json_decode($input, true);
			if(json_last_error() != JSON_ERROR_NONE){
				throw new Exception("Invalid request payload", 400);
			}
		}
	}

	protected function requireAuth(){
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			// TODO: call appropriate class/method
			$authManager = new AuthManager();
			$this->authenticated = $authManager>validateToken($_SERVER['HTTP_AUTHORIZATION']);
			if(!$this->authenticated){
				throw new Exception("Authorization required", 401);
			}
		} else {
			throw new Exception("Authorization required", 401);
		}
	}

	protected function response($data = [], $httpCode = '200'){
		http_response_code($httpCode);
		echo json_encode($data);
	}
}
