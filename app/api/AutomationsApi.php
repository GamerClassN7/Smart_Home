<?php

class AutomationsApi extends ApiController
{

	public function	default()
	{
		//$this->requireAuth();
		$response = [];
		$automationData = AutomationManager::getAll();

		foreach ($automationData as $automationKey => $automation) {
			$response[] = [
				"automation_id" => $automation['automation_id'],
				"name" => $automation['name'],
				"enabled" => $automation['enabled'],
			];
		}

		$this->response($response);
	}
}
