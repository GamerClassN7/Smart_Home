<?php


class Server extends Template
{
	//TODO: to server manager
	private function getSystemMemInfo()
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
		$userManager = new UserManager();
		$langMng = new LanguageManager('en');

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$template = new Template('server');
		$template->prepare('title', 'Server');

		$template->prepare('baseDir', BASEDIR);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('langMng', $langMng);

		$template->prepare('UPTIME', shell_exec('uptime -p'));
		$template->prepare('serverTime', date('m. d. Y H:i:s - e'));
		$template->prepare('ip', $_SERVER['SERVER_ADDR']);
		$template->prepare('name', $_SERVER['SERVER_NAME']);

		$template->prepare('CPU', sys_getloadavg()[0]);
		$template->prepare('ramFree', $this->getSystemMemInfo()["MemFree"]);
		$template->prepare('ramTotal', $this->getSystemMemInfo()["MemTotal"]);
		$template->prepare('diskFree', disk_free_space("/"));
		$template->prepare('diskTotal', disk_total_space("/"));

		$template->render();
	}
}
