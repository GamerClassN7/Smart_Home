<?php
class Login extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if ($userManager->isLogin()){
			header('Location: ./');
		}

		$template = new Template('login');
		$template->prepare('title', 'Home');
		$template->prepare('lang', $lang);

		$template->render();
	}
}
