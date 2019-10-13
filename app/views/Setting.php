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
		$template->prepare('title', 'Automation');
		$template->prepare('langMng', $langMng);
		$template->prepare('automations', $automations);

		$users = $userManager->getUsers();
		$template->prepare('users', $users);


		$template->render();
	}
}
