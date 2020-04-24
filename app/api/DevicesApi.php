<?php

class DevicesApi extends ApiController{

	public function getAllDevices(){
		$this->requireAuth();
		$response = [];

		// TODO: process the request

		$this->response($response);
	}

	public function getDevicesByRoom($roomId){

	}
}
