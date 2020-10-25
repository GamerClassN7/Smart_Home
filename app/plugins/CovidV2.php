<?php
class CovidV2 extends VirtualDeviceManager
{
	private $api_uri = 'https://onemocneni-aktualne.mzcr.cz/api/v2/covid-19/nakazeni-vyleceni-umrti-testy.json'; // Your redirect uri
	private $virtual_device_name = "Covid-V2";
	private $name_index = [
		"Active" => "kumulativni_pocet_nakazenych",
		"Recovered" => "kumulativni_pocet_vylecenych",
		"Tested" => "kumulativni_pocet_testu",
		"Deaths" => "kumulativni_pocet_umrti",

	];

	public function make()
	{
		try {
			if (DeviceManager::registeret($this->virtual_device_name)) {
				$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
				$dataItems = ['Tested', 'Deaths', 'Recovered', 'Active'];
				foreach ($dataItems as $dataItem) {
					if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($dataItem))) {
						SubDeviceManager::create($deviceId, strtolower($dataItem), $dataItem);
						sleep(1);
						$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($dataItem));
					}
				}

				if (!$this->fetchEnabled($deviceId, $subDevice['subdevice_id'])) die();

				$finalUrl = $this->api_uri;
				$json = json_decode(Utilities::CallAPI('GET', $finalUrl, ''), true)['data'];

				foreach ($dataItems as $dataItem) {
					RecordManager::create($deviceId, strtolower($dataItem), end($json)[$this->name_index[$dataItem]]);
				}
			} else {
				DeviceManager::create($this->virtual_device_name, $this->virtual_device_name, strtolower($this->virtual_device_name));
				DeviceManager::approved($this->virtual_device_name);
			}
			return 'sucessful';
		} catch (Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}

	public function translate($value){
		$outcome = $value / 1000;
		if ($outcome < 1){
			return $value;
		}
		return round($outcome) . 'K';
	}
}