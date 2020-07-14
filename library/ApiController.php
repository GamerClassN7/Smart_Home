<?php
class ApiController {
	protected $input;
	protected $authenticated = false;

	function __construct() {
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
			$this->authenticated = $authManager->validateToken($_SERVER['HTTP_AUTHORIZATION']);
			if(!$this->authenticated){
				throw new Exception("Authorization required", 401);
			}
		} else {
			throw new Exception("Authorization required", 401);
		}
	}

	protected function response($data = [], $httpCode = '200', $contentType = 'application/json', $jsonEncode = true){
		header('Content-Type: ' . $contentType);
		http_response_code($httpCode);
		if ($jsonEncode) {
			echo json_encode($data, JSON_UNESCAPED_UNICODE);
		} else {
			echo $data;
		}
	}
}
