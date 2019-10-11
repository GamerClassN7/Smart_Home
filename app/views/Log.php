<?php


class Log extends Template
{
	function __construct()
	{
		global $userManager;
		global $langMng;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR . 'login');
		}

		$template = new Template('log');
		$template->prepare('title', 'Log');

		$result = array();
		$cdir = scandir('./app/logs/');
		foreach ($cdir as $key => $value)
		{
			if (!in_array($value,array(".","..", ".gitkeep")))
			{
				$result[$value] = $value;
			}
		}

		$template->prepare('baseDir', BASEDIR);
		$template->prepare('title', 'Logy');
		$template->prepare('logsFiles', $result);
		$template->prepare('langMng', $langMng);

		$template->render();

	}
}
