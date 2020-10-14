<?php
class OpenWeatherMap extends VirtualDeviceManager
{
	private $city_sluig = "prague";
	private $app_id = "1ee609f2fcf8048e84f1d2fb1d1d72b5";
	private $api_uri = 'api.openweathermap.org/data/2.5/weather?q=%s&appid=%s'; // Your redirect uri
	private $virtual_device_name = "Weather";
	private $subdevice_type = "weather";

	function fetch($url)
	{
		if (DeviceManager::registeret($this->virtual_device_name)) {
			$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
			if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, $this->subdevice_type)) {
				SubDeviceManager::create($deviceId, $this->subdevice_type, '');
				sleep(1);
			}

			if (!$this->fetchEnabled($deviceId,$subDevice['subdevice_id'])) die();

			$finalUrl = sprintf($this->api_uri, $this->city_sluig, $this->app_id);
			$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true);

			RecordManager::create($deviceId, $this->subdevice_type, $json['weather'][0]['id']);
		} else {
			DeviceManager::create($this->virtual_device_name, $this->virtual_device_name);
			DeviceManager::approved($this->virtual_device_name);
		}
	}
}
