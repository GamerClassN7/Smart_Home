<?php

class AutomationsApi extends ApiController
{

	public function	default()
	{
		//$this->requireAuth();
		$response = [];
		$response = AutomationManager::getAll(["automation_id","name","enabled"]);

		$this->response($response);
	}

	public function detail($automationId)
	{
		//$this->requireAuth();
		$response = [];
		$response = AutomationManager::getById($automationId, ["automation_id", "last_execution_time", "owner_id", "conditions", "tasks"]);

		$this->response($response);
	}

	public function create()
	{
		$this->requireAuth();
		$obj = $this->input;

		if (
			empty($obj['name']) ||
			!isset($obj['name']) ||
			!isset($obj['conditions']) ||
			!isset($obj['tasks']) ||
			!isset($obj['days'])
		) {
			throw new Exception("Invalid request payload", 400);
		}

		$response = [];
		$response = AutomationManager::create($obj['name'],json_encode($obj['days']), json_encode($obj['tasks']), json_encode($obj['conditions']));

		$this->response(['value'=>'OK']);
	}
}
