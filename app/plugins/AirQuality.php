<?php
class AirQuality extends VirtualDeviceManager
{
	private $city_sluig = "prague";
	private $app_id = "53ccbc353bb0bd0b05515169a593b96c38d57c48";
	private $api_uri = 'http://api.waqi.info/feed/%s/?token=%s'; // Your redirect uri
	private $virtual_device_name = "Air Quality";
	private $subdevice_type = "air-quality";

	function make()
	{
		//Register the settings
		$settingMng = new SettingsManager();
		if (!($settingField = $settingMng->getByName("airquality"))) {
			$settingMng->create("token", "", "airquality");
		} else {
			$app_id = $settingField['value'];
		}

		try {
			if (DeviceManager::registeret($this->virtual_device_name)) {
				$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
				if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, $this->subdevice_type)) {
					SubDeviceManager::create($deviceId, $this->subdevice_type, '');
					sleep(1);
					$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($this->subdevice_type));
				}

				//if (!$this->fetchEnabled($deviceId,$subDevice['subdevice_id'])) die();

				$finalUrl = sprintf($this->api_uri, $this->city_sluig, $this->app_id);
				$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true);
				RecordManager::create($deviceId, $this->subdevice_type, $json['data']['aqi'], 'plugin');
			} else {
				DeviceManager::create($this->virtual_device_name, $this->virtual_device_name, 'senzore-virtual');
				DeviceManager::approved($this->virtual_device_name);
			}
			return 'sucessful';
		} catch(Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}

	function translate($value){
		if ($value < 50) {
			return 'Good';
		}  else if  ($value > 51 && $value < 100) {
			return 'Moderate';
		} else if ($value > 101 && $value < 150) {
			return 'Normal';
		} else if ($value > 151 && $value < 200) {
			return 'Unhealthy';
		} else if ($value > 201 && $value < 300) {
			return 'Very Unhealthy';
		} else if ($value > 301 ) {
			return 'Hazardous';
		}
		return '';
	}
}
