<?php
class AirQuality extends VirtualDeviceManager
{
	private $city_sluig = "prague";
	private $app_id = "53ccbc353bb0bd0b05515169a593b96c38d57c48";
	private $api_uri = 'http://api.waqi.info/feed/%s/?token=%s'; // Your redirect uri
	private $virtual_device_name = "Air Quality";
	private $subdevice_type = "air-quality";

	function fetch($url)
	{
		

		if (DeviceManager::registeret($this->virtual_device_name)) {
			$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
			if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, $this->subdevice_type)) {
				SubDeviceManager::create($deviceId, $this->subdevice_type, '');
				sleep(1);
			}

			//if (!$this->fetchEnabled($deviceId,$subDevice['subdevice_id'])) die();

			$finalUrl = sprintf($this->api_uri, $this->city_sluig, $this->app_id);
			$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true);

			RecordManager::create($deviceId, $this->subdevice_type, $json['data']['aqi']);
		} else {
			DeviceManager::create($this->virtual_device_name, $this->virtual_device_name);
			DeviceManager::approved($this->virtual_device_name);
		}
	}
}
