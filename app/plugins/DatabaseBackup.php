<?php
class DatabaseBackup {
	public function scheme(){
		$backupfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR ."/backup/" . DBNAME.'_scheme_'.date("Y-m-d", time()).'.sql';
		$command = "mysqldump --skip-comments --no-create-info -h localhost -u ". DBUSER . " -p" . DBPASS ." ". DBNAME ." -r $backupfile 2>&1";
		$this->executeCommand($command);
		return $backupfile;
	}

	public function data(){
		$backupfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR ."/backup/" . DBNAME.'_data_'.date("Y-m-d", time()).'.sql';
		$command = "mysqldump --skip-comments --no-data -h localhost -u ". DBUSER . " -p" . DBPASS ." ". DBNAME ." -r $backupfile 2>&1";
		$this->executeCommand($command);
		return $backupfile;
	}

	private function executeCommand($command){
		ini_set('date.timezone', 'Europe/Prague');
		exec($command);
	}

	public function compress($filename, $files = []) {
		$zip = new ZipArchive();
		if($zip->open($filename,ZipArchive::CREATE|ZipArchive::OVERWRITE)) {
			foreach ($files as $file) {
				$zip->addFile($file);
			}
			echo $zip->close();
			foreach ($files as $file) {
				unlink($file);
			}
		}
	}
}
