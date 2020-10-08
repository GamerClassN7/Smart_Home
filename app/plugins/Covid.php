<?php
class Covid extends VirtualDeviceManager {
	private $country_sluig = "czech-republic";
	private $api_uri = 'https://api.covid19api.com/live/country/%s/status/confirmed'; // Your redirect uri
	private $virtual_device_name = "Covid";
	private $fetch_iterval = "";

	function fetch($url = 'true')
	{
		//todo fetch interval limit from sleep time
		$finalUrl = sprintf($this->api_uri, $this->country_sluig);
		$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true);

		if (DeviceManager::registeret($this->virtual_device_name)) {
			$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
			$dataItems = ['Confirmed','Deaths','Recovered','Active'];
			foreach ($dataItems as $dataItem) {
				if (!SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($dataItem))) {
					SubDeviceManager::create($deviceId, strtolower($dataItem), 'Cases');
				}
				RecordManager::create($deviceId, strtolower($dataItem), $json[0][$dataItem]);
			}
		} else {
			DeviceManager::create($this->virtual_device_name, $this->virtual_device_name);
			DeviceManager::approved($this->virtual_device_name);
		}
	}
}
