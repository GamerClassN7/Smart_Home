<?php


class Log extends Template
{
	function __construct()
	{
		$userManager = new UserManager();
		$langMng = new LanguageManager('en');

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$template = new Template('log');
		$template->prepare('title', 'Log');

		$result = array();
		$cdir = scandir('../logs/');
		foreach ($cdir as $key => $value)
		{
			if (!in_array($value,array(".","..", ".gitkeep")))
			{
		
				$result[$value] = $value;
			}
		}

		$template->prepare('baseDir', BASEDIR);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('logToLiveTime', LOGTIMOUT);
		$template->prepare('title', 'Logy');
		$template->prepare('logsFiles', $result);
		$template->prepare('langMng', $langMng);

		$template->render();
	}
}
