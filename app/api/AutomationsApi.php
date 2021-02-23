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
		//$this->requireAuth();
		$obj = $this->input;

		$response = [];
		$response = AutomationManager::create($obj['name'],$obj['days'], $obj['tasks'], $obj['conditions']);

		$this->response(['value'=>'OK']);
	}
}
