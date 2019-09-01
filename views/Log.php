<?php


class Log extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./login');
		}

		$template = new Template('log');
		$template->prepare('title', 'Log');

		$result = array();
		$cdir = scandir('./logs/');
		foreach ($cdir as $key => $value)
		{
			if (!in_array($value,array(".","..")))
			{
				$result[$value] = $value;
			}
		}

		$template->prepare('logsFiles', $result);
		$template->prepare('lang', $lang);

		$template->render();

	}
}
