<?php
class ServerApi extends ApiController {
    private function getSystemMemInfo()
	{
		$data = explode("\n", file_get_contents("/proc/meminfo"));
		$meminfo = array();
		foreach ($data as $line) {
			$data = explode(":", $line);
			if (count($data)!=2) continue;
			$meminfo[$data[0]] = trim($data[1]);
		}
		return $meminfo;
    }

    private function getProcessorUsage(){
		$loads=sys_getloadavg();
		$core_nums=trim(shell_exec("grep -P '^physical id' /proc/cpuinfo|wc -l"));
		$load = round($loads[0]/($core_nums + 1)*100, 2);
		return $load;
	}

    public function default(){
        //$this->requireAuth();
        $response = [
            "cpu_load" => $this->getProcessorUsage(),
            "uptime" => shell_exec('uptime -p'),
            "ramFree" => $this->getSystemMemInfo()["MemFree"],
            "ramTotal" => $this->getSystemMemInfo()["MemTotal"],
            "diskFree" => disk_free_space("/"),
            "diskTotal" => disk_total_space("/"),
            "serverTime" => date('m. d. Y H:i:s'),
            "serverTimeZone" => date('e'),
        ];
        $this->response($response);
    }

    public function logStatus()
    {
        $logKeeper = new LogMaintainer();
        $response = $logKeeper::getStats();
        $this->response($response);
    }
}
