<?php
class Spotify extends VirtualDeviceManager {
	private $token = "";

	private function setToken($token){
		$this->token = $token;
	}

	private function autorize(){
		$client_id = '76840e2199e34dcd903d19877bd726dd';
		$client_secret = '0f94ed2c0bd64bf791ea13b7e6310ba3';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            'https://accounts.spotify.com/api/token' );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS,     'grant_type=client_credentials&scope=user-read-playback-state' );
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Basic '.base64_encode($client_id.':'.$client_secret)));

		$result=curl_exec($ch);

		$this->setToken(json_decode($result, true)['access_token']);
		echo $result;
	}

	private function getPlayerData(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            'https://api.spotify.com/v1/users/byzolscj4vc1p0xcjqykbesn8' );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Bearer ' . $this->token));

		$result=curl_exec($ch);
		echo $result;
	}

	function fetch($url = 'true')
	{
		$this->autorize();
		$this->getPlayerData();
	}
}
