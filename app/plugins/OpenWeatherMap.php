<?php
class OpenWeatherMap extends VirtualDeviceManager
{
	private $city_sluig = "prague";
	private $app_id = "1ee609f2fcf8048e84f1d2fb1d1d72b5";
	private $api_uri = 'api.openweathermap.org/data/2.5/weather?q=%s&appid=%s'; // Your redirect uri
	private $virtual_device_name = "Weather";
	private $subdevice_type = "weather";

	function make()
	{
		try {
			if (DeviceManager::registeret($this->virtual_device_name)) {
				$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
				if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, $this->subdevice_type)) {
					SubDeviceManager::create($deviceId, $this->subdevice_type, '');
					sleep(1);
					$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($this->subdevice_type));
				}

				if (!$this->fetchEnabled($deviceId, $subDevice['subdevice_id'])) die();

				$finalUrl = sprintf($this->api_uri, $this->city_sluig, $this->app_id);
				$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true);


				//Notification data setup
				$notificationMng = new NotificationManager;
				if ($json['weather'][0]['id'] >= 500 && $json['weather'][0]['id'] < 600) {
					// $notificationData = [
					// 	'title' => 'Weather',
					// 	'body' => 'It Will be rainy outhere, Take Umbrela :)',
					// 	'icon' => 'http://dev.steelants.cz/projekty/simplehome-client/img/icons/favicon-16x16.png',
					// ];
					// //Notification for newly added Device
					// if ($notificationData != []) {
					// 	$subscribers = $notificationMng::getSubscription();
					// 	foreach ($subscribers as $key => $subscriber) {
					// 		$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
					// 	}
					// }
				} else if ($json['weather'][0]['id'] >= 600 && $json['weather'][0]['id'] < 700) {
					// $notificationData = [
					// 	'title' => 'Weather',
					// 	'body' => 'It is white out there :)',
					// 	'icon' => 'http://dev.steelants.cz/projekty/simplehome-client/img/icons/favicon-16x16.png',
					// ];
					// //Notification for newly added Device
					// if ($notificationData != []) {
					// 	$subscribers = $notificationMng::getSubscription();
					// 	foreach ($subscribers as $key => $subscriber) {
					// 		$notificationMng::sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
					// 	}
					// }
				}


				RecordManager::create($deviceId, $this->subdevice_type, $json['weather'][0]['id']);
			} else {
				DeviceManager::create($this->virtual_device_name, $this->virtual_device_name, 'senzore-virtual');
				DeviceManager::approved($this->virtual_device_name);
			}
			return 'sucessful';
		} catch (Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}

	function enable(){
		(new SettingsManager)->create('open_weather_api_token', '', 'open_weather');
	}
}
