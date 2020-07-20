<?php
class Login extends Template
{
	function __construct()
	{
		$userManager = new UserManager();
		global $lang;

		if ($userManager->isLogin()){
			header('Location: ' . BASEURL);
		}

		$template = new Template('login');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('title', 'Home');
		$template->prepare('lang', $lang);



		$template->render();
	}
}
