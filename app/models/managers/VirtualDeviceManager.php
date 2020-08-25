<?php
class VirtualDeviceManager
{
    function fetch($url)
    {
        $json = json_decode(Utilities::CallAPI('GET', 'api.openweathermap.org/data/2.5/weather?q=prague&appid=1ee609f2fcf8048e84f1d2fb1d1d72b5', ''), true);

        if (DeviceManager::registeret('1ee609f2fcf8048e84f1d2fb1d1d72b5')) {

            $deviceId = DeviceManager::getDeviceByToken('1ee609f2fcf8048e84f1d2fb1d1d72b5')['device_id'];

            if (!SubDeviceManager::getSubDeviceByMaster($deviceId, 'weather-nice')) {
                SubDeviceManager::create($deviceId, 'weather-nice', '');
            }

            var_dump($json['weather'][0]);
            RecordManager::create($deviceId, 'weather-nice', $json['weather'][0]['main']);
        } else {
            $deviceId = DeviceManager::create('1ee609f2fcf8048e84f1d2fb1d1d72b5', '1ee609f2fcf8048e84f1d2fb1d1d72b5')['device_id'];
        }
    }
}
