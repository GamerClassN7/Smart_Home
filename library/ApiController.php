<?php
class ApiObject {
	private $data = [];
    public $httpCode = 200;
    public $autenticated = false;


	function __construct($objectName, $httpHeaders) {
		$this->table = $objectName;
		$this->headers = $httpHeaders;
    }

	function requireAuth(){
        if (isset($this->headers['HTTP_AUTHORIZATION'])) {
			$this->autenticated = $this->apiManager->validateToken(explode(' ', $this->headers['HTTP_AUTHORIZATION'])[1]);
		} else {
			$error = new ApiError();
            $error->code = "missing_token_header";
            $error->message = "Missing Token in Header";
            $error->hint = "check paiload header for 'token'";
            echo json_encode($error);
            die();
		}
    }

	function response(){
        http_response_code($this->httpCode);
        echo json_encode($this->data);
        die();
	}
}