<?php
class Setting extends Template
{
	function __construct()
	{

		global $userManager;
		global $langMng;

		if (!$userManager->isLogin()){
			header('Location: ' . BASEDIR . 'login');
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
		$template->prepare('debugMod', DEBUGMOD);
		$template->prepare('title', 'Automation');
		$template->prepare('langMng', $langMng);
		$template->prepare('automations', $automations);

		$users = $userManager->getUsers();
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


		$template->render();
	}
}
