<?php
class Setting extends Template
{
	function __construct()
	{
		$userManager = new UserManager();
		$langMng = new LanguageManager('en');

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$automations = [];
		$automationsData = AutomationManager::getAll();
		foreach ($automationsData as $automationKey => $automationData) {
			$automations[$automationData['automation_id']] = [
				'name' => '',
				'onDays' => $automationData['on_days'],
				'ifSomething' => $automationData['if_something'],
				'doSomething' => $automationData['do_something'],
			];
		}

		$template = new Template('setting');
		$template->prepare('baseDir', BASEDIR);
		$template->prepare('baseUrl', BASEURL);
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Automation');
		$template->prepare('langMng', $langMng);
		$template->prepare('automations', $automations);

		$users = $userManager->getUsers();
		foreach ($users as $key => $value) {
			$users[$key]['gavatar_url'] = $userManager->getAvatarUrl($value['user_id']);
		}
		$template->prepare('users', $users);

		$template->prepare('userName', $userManager->getUserData('username'));
		$template->prepare('userEmail', $userManager->getUserData('email'));
		$template->prepare('userAvatarUrl', $userManager->getAvatarUrl());

		if ($userManager->getUserData('ota') == ''){
			$ga = new PHPGangsta_GoogleAuthenticator();
			$otaSecret = $ga->createSecret();
			$qrCodeUrl = $ga->getQRCodeGoogleUrl('Smart Home', $otaSecret);
			$oneCode = $ga->getCode($otaSecret);
			$template->prepare('qrUrl', $qrCodeUrl);
			$template->prepare('otaSecret', $otaSecret);
			$template->prepare('otaCode', $oneCode);

			// echo "Secret is: ".$secret."\n\n";
			// echo "Google Charts URL for the QR-Code: ".$qrCodeUrl."\n\n";
			// echo "Checking Code '$oneCode' and Secret '$otaSecret':\n";
		}

		$rooms = RoomManager::getAllRooms();
		$template->prepare('rooms', $rooms);

		$settingsManager = new SettingsManager();
		$dir = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/';
		$pluginsFiles = array_diff (scandir ($dir), ['..', '.']);

		$plugins = array ();

		foreach ($pluginsFiles as $key => $pluginFile) {
			$status = (strpos ($pluginFile, "!") !== false ? false : true);
			if ($status) {
				$plugins[$key]['name'] = str_replace ("!", "", str_replace (".php", "", $pluginFile));
				$plugins[$key]['slug'] = strtolower ($plugins[$key]['name']);
				$result = $settingsManager->getSettingGroup($plugins[$key]['slug']);
				if (count ($result) > 0) {
					$plugins[$key]['settings'] = $result;
				} else {
					unset($plugins[$key]);
				}
			}
		}

		$plugins = Utilities::sortArrayByKey($plugins, 'slug', "desc");

		$template->prepare('pluginsSettings', $plugins);

		$template->render();
	}
}
