<?php
class Setting extends Template
{
	function __construct()
	{
		global $userManager;
		global $lang;

		if (!$userManager->isLogin()){
			header('Location: ./login');
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
		$template->prepare('title', 'Automation');
		$template->prepare('lang', $lang);
		$template->prepare('automations', $automations);

		$template->render();
	}
}
