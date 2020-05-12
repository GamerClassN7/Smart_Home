<?php

class RoomsApi extends ApiController{

	public function default(){
		$this->requireAuth();
		$response = [];

		// TODO: process the request

		$this->response($response);
	}
}
