<?php
class Plugins extends Template
{
	function __construct()
	{
		$userManager = new UserManager();
		$langMng = new LanguageManager('en');

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$dir = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/';
		$pluginsFiles = array_diff(scandir($dir), ['..', '.']);

		$plugins = array();

		foreach ($pluginsFiles as $key => $pluginFile) {
			$status = (strpos($pluginFile, "!") !== false ? false : true);
			$plugins[$key]['name'] = str_replace("!", "", str_replace(".php", "", $pluginFile));
			$plugins[$key]['status'] = $status;
		}

		$plugins = Utilities::sortArrayByKey($plugins, 'status', "desc");

		$template = new Template('plugins');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('baseUrl', BASEURL);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Plugins');
		$template->prepare('langMng', $langMng);
		$template->prepare('plugins', $plugins);



		$template->render();
	}
}
