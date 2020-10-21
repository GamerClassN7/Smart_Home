<?php
class CronApi extends ApiController {

    public function clean(){
        $logKeeper = new LogMaintainer();
        $logKeeper->purge(LOGTIMOUT);
        $this->response(['Value' => 'OK']);
    }

    public function fetch(){
		  //echo (new VirtualDeviceManager)->fetch('');
		  echo (new Covid)->fetch('');
		  echo (new OpenWeatherMap)->fetch('');
		  echo (new UsaElection)->fetch('');
		  echo (new AirQuality)->fetch('');

			//	Database Backup
		  $filenames = [];
		  $backupWorker = new DatabaseBackup;
		  $filenames[] = $backupWorker->scheme();
		  $filenames[] = $backupWorker->data();
		  $backupWorker->compress($_SERVER['DOCUMENT_ROOT'] . BASEDIR . '/backup/'.date("Y-m-d", time()).'.zip', $filenames);

        $this->response(['Value' => 'OK']);
    }
}
