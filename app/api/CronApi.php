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

		$this->response(['Value' => 'OK']);
	}

	public function fetch()
	{
		//Run Plugins
		$result = [];
		$dir = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'app/plugins/';
		$pluginsFiles = array_diff(scandir($dir), ['..', '.']);
		foreach ($pluginsFiles as $key => $pluginFile) {
			$className = str_replace(".php", "", $pluginFile);
			if (!class_exists($className)) {
				continue;
			}
			$pluginMakeClass = new $className;
			if (!method_exists($pluginMakeClass, 'make')) {

				continue;
			}
			$result[$className] = $pluginMakeClass->make();
		}

		//Print Result
		$this->response($result);
	}
}
