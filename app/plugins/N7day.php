<?php
class N7Day extends VirtualDeviceManager
{
	private $virtual_device_name = "N7 Day";
	private $device_type = "day-count";
	private $subdevice_type = "day-count";


	function make()
	{
		try {
			if (DeviceManager::registeret($this->virtual_device_name)) {
				$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
				if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, $this->subdevice_type)) {
					SubDeviceManager::create($deviceId, $this->subdevice_type, 'days');
					sleep(1);
					$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($this->subdevice_type));
				}
				
				//if (!$this->fetchEnabled($deviceId,$subDevice['subdevice_id'])) die();

				//Days Until N7 day
				$now = time(); // or your date as well
				if (strtotime(date("Y") . "-11-07") < $now){
					$your_date = strtotime((date("Y") + 1) . "-11-07");
				} else {
					$your_date = strtotime(date("Y") . "-11-07");
				}
				$datediff = $now - $your_date;
				$daysUntilN7Day = round($datediff / (60 * 60 * 24));
				
				RecordManager::create($deviceId, $this->subdevice_type, $daysUntilN7Day, 'plugin');
			} else {
				DeviceManager::create($this->virtual_device_name, $this->virtual_device_name, $this->device_type);
				DeviceManager::approved($this->virtual_device_name);
			}
			return 'sucessful';
		} catch(Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}
}
