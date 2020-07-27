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
		$result = $this->logFinder ('../logs/', $result);

		$template->prepare('baseDir', BASEDIR);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('logToLiveTime', LOGTIMOUT);
		$template->prepare('title', 'Logy');
		$template->prepare('logsFiles', $result);
		$template->prepare('langMng', $langMng);

		$template->render();
	}

	private function logFinder ($dir, $result) {
		$logFiles = scandir ($dir);
		foreach ($logFiles as $key => $file) {
			if (in_array ($file,array (".", "..", ".gitkeep")))
			{
				continue;
			}
			if (!is_dir($dir . $file)) {
				$result[$dir][] = $file;
			} else {
				$result = $this->logFinder ($dir . $file . "/", $result);
			}
		}
		return $result;
	}
}
