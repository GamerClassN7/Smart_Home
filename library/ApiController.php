<?php
class ApiController {
	private $input;
	private $authenticated;

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

	private function requireAuth(){
		if (isset($this->headers['HTTP_AUTHORIZATION'])) {
			// TODO: call appropriate class/method
			$authManager = new AuthManager();
			$this->authenticated = $authManager>validateToken($this->headers['HTTP_AUTHORIZATION']);
			if(!$this->authenticated){
				throw new Exception("Auth required", 401);
			}
		} else {
			throw new Exception("Auth required", 401);
		}
	}

	private function response($data = [], $httpCode = '200'){
		http_response_code($httpCode);
		echo json_encode($data);
	}
}
