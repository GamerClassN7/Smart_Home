<?php
class DatabaseBackup
{
	public function make()
	{
		try {
			$filenames = [];
			$backupWorker = new DatabaseBackup;
			$filenames[] = $backupWorker->scheme(); //Backup Database scheme
			$filenames[] = $backupWorker->data(); //Backup Database Data
			$filenames[] = $_SERVER['DOCUMENT_ROOT'] . 'config/config.php'; //Backup Configuration File
			$backupWorker->compress($_SERVER['DOCUMENT_ROOT'] . BASEDIR . '/backup/' . date("Y-m-d", time()) . '.zip', $filenames);
			return 'sucessful';
		} catch (Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}

	private function scheme()
	{
		$backupfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . "/backup/" . DBNAME . '_scheme_' . date("Y-m-d", time()) . '.sql';
		$command = "mysqldump --skip-comments --no-create-info -h localhost -u " . DBUSER . " -p" . DBPASS . " " . DBNAME . " -r $backupfile 2>&1";
		$this->executeCommand($command);
		return $backupfile;
	}

	private function data()
	{
		$backupfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . "/backup/" . DBNAME . '_data_' . date("Y-m-d", time()) . '.sql';
		$command = "mysqldump --skip-comments --no-data -h localhost -u " . DBUSER . " -p" . DBPASS . " " . DBNAME . " -r $backupfile 2>&1";
		$this->executeCommand($command);
		return $backupfile;
	}

	private function executeCommand($command)
	{
		ini_set('date.timezone', 'Europe/Prague');
		exec($command);
	}

	private function compress($filename, $files = [])
	{
		$zip = new ZipArchive();
		if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
			foreach ($files as $file) {
				$zip->addFile($file);
			}
			$zip->close();
			foreach ($files as $file) {
				unlink($file);
			}
		}
	}

	private function cleaningDir($dir, $seconds)
	{
		$todayFileName = date("Y-m-d") . '.zip';
		$logFiles = scandir($dir);
		foreach ($logFiles as $key => $file) {
			if (in_array($file, array(".", "..", ".gitkeep", $todayFileName))) {
				continue;
			}
			if (!is_dir($dir . $file)) {
				if (strtotime(str_replace(".zip", "", $file)) < (strtotime("now") - $seconds)) {
					unlink($dir . $file);
				}
			} else {
				$this->cleaningDir($dir . $file . "/", $seconds);
			}
		}
	}

	public function purge($days)
	{
		$seconds = $days * 86400;
		$this->cleaningDir('../backup/', $seconds);
	}
}
