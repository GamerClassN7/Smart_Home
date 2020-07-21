<?php 
class CronApi extends ApiController {
    public function clean(){
        $logKeeper = new LogMaintainer();
        $logKeeper->purge(LOGTIMOUT);
    }
}