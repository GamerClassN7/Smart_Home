<?php
//https://developers.google.com/oauthplayground/
//https://developer.okta.com/blog/2018/04/10/oauth-authorization-code-grant-type

class Oauth
{
	function default()
	{
		//Log
		$logManager = new LogManager(__DIR__ . '/../../logs/auth/' . date("Y-m-d") . '.log');
		$logManager->setLevel(LOGLEVEL);
		$logManager->write("[OAUTH] GET  " . json_encode($_GET), LogRecordTypes::WARNING);
		$logManager->write("[OAUTH] DATA " . file_get_contents('php://input'), LogRecordTypes::WARNING);
		$logManager->write("[OAUTH] URL  " . $_SERVER['REQUEST_URI'], LogRecordTypes::WARNING);

		$userManager = new UserManager();
		$langMng = new LanguageManager('en');

		$template = new Template('oauth');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('baseUrl', BASEURL);
		$template->prepare('title', 'Simple Home - Oauth');

		if (isset($_GET['response_type']) && $_GET['response_type'] == 'code') {
			$template->prepare('responseType', $_GET['response_type']);
			$template->prepare('redirectUrl', $_GET['redirect_uri']);
			$template->prepare('clientId', $_GET['client_id']);
			$template->prepare('scope', $_GET['scope']);
			$template->prepare('state', $_GET['state']);
		} else {
			if (isset($_GET['redirect_uri'])) {
				$template->prepare('responseType', $_GET['response_type']);
				$template->prepare('redirectUrl', $_GET['redirect_uri']);
				$template->prepare('clientId', $_GET['client_id']);
				$template->prepare('state', $_GET['state']);
			} else {
				$template->prepare('responseType', $_POST['responseType']);
				$template->prepare('redirectUrl', $_POST['redirectUrl']);
				$template->prepare('clientId', $_POST['clientId']);
				$template->prepare('state', $_POST['state']);
			}
		}

		$template->render();
	}

	function token()
	{
		//Log
		$logManager = new LogManager(__DIR__ . '/../../logs/auth/' . date("Y-m-d") . '.log');
		$logManager->setLevel(LOGLEVEL);
		$logManager->write("[OAUTH] GET  " . json_encode($_GET), LogRecordTypes::WARNING);
		$logManager->write("[OAUTH] POST " . json_encode($_POST), LogRecordTypes::WARNING);
		$logManager->write("[OAUTH] DATA " . file_get_contents('php://input'), LogRecordTypes::WARNING);
		$logManager->write("[OAUTH] URL  " . $_SERVER['REQUEST_URI'], LogRecordTypes::WARNING);

		// $template = new Template('oauth');
		// $template->prepare('baseDir', BASEDIR);
		// $template->prepare('baseUrl', BASEURL);
		// $template->prepare('title', 'Simple Home - Oauth');
		// $template->render();

		$token =  $_POST["code"];
		$get = [
			"access_token" => $token,
			"token_type" => "bearer",
			"refresh_token" => $token,
			"scope" => 'user',
		];

		$logManager->write("[OAUTH] Response  " . json_encode($get), LogRecordTypes::WARNING);
		echo json_encode($get);
		die();
	}

	function httpPost($url, $data)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}
}
