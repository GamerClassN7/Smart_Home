<?php

class ExampleApi extends ApiController{

	public function example(){
		// if this function should be accessible only for logged users uncomment next line
		// $this->requireAuth();
		// if user is logged in, next lines will be processed
		// otherwise script get terminated with 401 UNAUTHORIZED


		// input data are stored in $this->input
		// in this example we just copy input to response
		$response = $this->input;


		// this method returns response as json
		$this->response($response);
		// you can specify returned http code by second optional parameter
		// default value is 200
		// $this->response($response, $httpCode);
	}

}
