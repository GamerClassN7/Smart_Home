<?php
class Login extends Template
{
	function __construct()
	{
		$userManager = new UserManager();


		if ($userManager->isLogin()){
			header('Location: ' . BASEURL);
		}

		$template = new Template('login');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('baseUrl', BASEURL);
		$template->prepare('title', 'Home');



		$template->render();
	}
}
