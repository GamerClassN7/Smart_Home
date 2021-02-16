<?php

class AutomationsApi extends ApiController
{

	public function	default()
	{
		//$this->requireAuth();
		$response = [];
		$automationsData = AutomationManager::getAll();

		foreach ($automationsData as $automationKey => $automation) {
			$response[] = [
				"automation_id" => $automation['automation_id'],
				"name" => $automation['name'],
				"enabled" => $automation['enabled'],
			];
		}

		$this->response($response);
	}

	public function detail($automationId)
	{
		//$this->requireAuth();

		$response = null;
		$automationData = AutomationManager::getById($automationId);

		$response = [
			'automation_id' => $automationData['automation_id'],
			'last_execution_time' => $automationData['last_execution_time'],
			'owner' => $automationData['owner_id'],
			'conditions' => $automationData['conditions'],
			'tasks' => $automationData['tasks'],
		];

		$this->response($response);
	}

}
