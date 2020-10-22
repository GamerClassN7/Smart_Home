<?php
class Spotify extends VirtualDeviceManager
{
	private $token = "";
	private $client_id = '76840e2199e34dcd903d19877bd726dd'; // Your client id
	private $redirect_uri = 'https://dev.steelants.cz/vasek/home-update/plugins/spotify/callback'; // Your redirect uri

	public function oAuth()
	{
		$client_secret = 'CLIENT_SECRET'; // Your secret
		$scopes = 'user-read-private user-read-email';

		header('Location: https://accounts.spotify.com/authorize?client_id=' . $this->client_id . '&response_type=token&redirect_uri=' . urlencode($this->redirect_uri) . '&scope=user-read-playback-state');
	}

	private function setToken($token)
	{
		$this->token = $token;
	}

	public function callback()
	{
		var_dump($_REQUEST);
		(new SettingsManager)->create('spotify_token', $token);
	}

	public function autorize()
	{

		$client_secret = '0f94ed2c0bd64bf791ea13b7e6310ba3';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            'https://accounts.spotify.com/api/token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     'grant_type=client_credentials&scope=user-read-playback-state');
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Basic ' . base64_encode($this->client_id . ':' . $client_secret)));

		$result = curl_exec($ch);

		$this->setToken(json_decode($result, true)['access_token']);
		echo $result;
	}

	private function getPlayerData()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            'https://api.spotify.com/v1/me/player');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Bearer ' . (new SettingsManager)->getByName('spotify_token')['value']));

		$result = curl_exec($ch);
		echo $result;
	}

	// function make()
	// {
	// 	try {
	// 		//$this->autorize();d
	// 		//$this->getPlayerData();
	// 		return 'sucessful';
	// 	} catch (Exception $e) {
	// 		return 'exception: ' . $e->getMessage();
	// 	}
	// }
}
