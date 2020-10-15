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

        $this->response(['Value' => 'OK']);
    }
}
