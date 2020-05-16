<?php
class Oauth extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		$template = new Template('oauth');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('title', 'Home');

		$template->prepare('lang', $lang);

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


		$template->render();
	}
}
