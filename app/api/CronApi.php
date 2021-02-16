<?php
class CronApi extends ApiController
{

	public function clean()
	{
		//Log Cleaning
		$logKeeper = new LogMaintainer();
		$logKeeper->purge(LOGTIMOUT);

		//Database Backup Cleanup
		$backupWorker = new DatabaseBackup();
		$backupWorker->purge(5);

		//Old Records Cleanup
		foreach (SubDeviceManager::getAllSubDevices() as $key => $value) {
			RecordManager::setHistory($value['subdevice_id']);
		}

		$this->response(['Value' => 'OK']);
	}

	public function fetch(){
		//Run Plugins
		$result = [];
		$dir = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/';
		$pluginsFiles = array_diff(scandir($dir), ['..', '.']);
		foreach ($pluginsFiles as $key => $pluginFile) {
			if (strpos($pluginFile, "!") === false) {
				$className = str_replace(".php", "", $pluginFile);
				if (strpos($pluginFile, '_') === true) {
					continue;
				}
				if (!class_exists($className)) {
					continue;
				}
				$pluginMakeClass = new $className;
				if (!method_exists($pluginMakeClass, 'make')) {

					continue;
				}
				$result[$className] = $pluginMakeClass->make();
			} else {
				$className = str_replace("!", "", str_replace(".php", "", $pluginFile));
				if (strpos($pluginFile, '_') === true) {
					continue;
				}
				if (!class_exists($className)) {
					continue;
				}
				$pluginMakeClass = new $className;
				if (!method_exists($pluginMakeClass, 'disable')) {
					continue;
				}
				$result[$className] = $pluginMakeClass->disable();
			}
		}

		//Print Result
		$this->response($result);
	}

	public function automations(){
		AutomationManager:executeAll();
		$this->response(['Value' => 'OK']);
	}
}
