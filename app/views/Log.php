<?php


class Log extends Template
{
	//TODO: to server manager
	function getSystemMemInfo()
	{
		$data = explode("\n", file_get_contents("/proc/meminfo"));
		$meminfo = array();
		foreach ($data as $line) {
			$data = explode(":", $line);
			if (count($data)!=2) continue;
			$meminfo[$data[0]] = trim($data[1]);
		}
		return $meminfo;
	}

	function __construct()
	{
		global $userManager;
		global $langMng;

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
		$template->prepare('CPU', sys_getloadavg()[0]);
		$template->prepare('UPTIME', shell_exec('uptime -p'));
		$template->prepare('ramFree', $this->getSystemMemInfo()["MemFree"]);
		$template->prepare('ramTotal', $this->getSystemMemInfo()["MemTotal"]);
		$template->prepare('diskTotal', disk_total_space("/"));
		$template->prepare('serverTime', date('m. d. Y H:i:s - e'));







		$template->render();

	}
}
