<?php
class UsaElection extends VirtualDeviceManager
{
	private $api_uri = 'https://ft-ig-content-prod.s3.eu-west-1.amazonaws.com/v2/Financial-Times/ig-rcp-polls-backend/2020-presidential/latest/presidential-races.json'; // Your redirect uri
	private $virtual_device_name = "Election";
	private $subdevice_type = "election";

	function make()
	{
		try {
			if (DeviceManager::registeret($this->virtual_device_name)) {
				$deviceId = DeviceManager::getDeviceByToken($this->virtual_device_name)['device_id'];
				$dataItems = ['Trump', 'Biden', 'Unknown'];
				foreach ($dataItems as $dataItem) {
					if (!$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($dataItem))) {
						SubDeviceManager::create($deviceId, strtolower($dataItem), '% ' . $dataItem);
						sleep(1);
						$subDevice = SubDeviceManager::getSubDeviceByMaster($deviceId, strtolower($this->subdevice_type));
					}
				}

				if (!$this->fetchEnabled($deviceId, $subDevice['subdevice_id'])) die();

				$finalUrl = $this->api_uri;
				$json = json_decode(Utilities::CallAPI('GET', $finalUrl), true);

				$voteSpectrum = [
					'republican' => [
						'solid' => 0,
						'leaning' => 0,
					],
					'democrat' => [
						'solid' => 0,
						'leaning' => 0,
					],
					'tossup' => 0
				];

				foreach ($json as $state) {
					if ($state['raceCategory'] != 'tossup') {
						$raceCategory = explode('-', $state['raceCategory']);
						$voteSpectrum[$raceCategory[0]][$raceCategory[1]] = $voteSpectrum[$raceCategory[0]][$raceCategory[1]] + $state['raceDelegates'];
					} else {
						$voteSpectrum['tossup'] = $voteSpectrum['tossup'] + $state['raceDelegates'];
					}
				}

				$Trump = $voteSpectrum['republican']['solid'] + $voteSpectrum['republican']['leaning'];
				$Biden = $voteSpectrum['democrat']['solid'] + $voteSpectrum['democrat']['leaning'];
				$Unknown = $voteSpectrum['tossup'];

				$OnePercent = ($Trump + $Biden + $Unknown) / 100;

				foreach ($dataItems as $Category) {
					RecordManager::create($deviceId, strtolower($Category), round(($$Category / $OnePercent)), 'plugin');
				}
			} else {
				DeviceManager::create($this->virtual_device_name, $this->virtual_device_name, 'senzore-virtual');
				DeviceManager::approved($this->virtual_device_name);
			}
			return 'sucessful';
		} catch (Exception $e) {
			return 'exception: ' . $e->getMessage();
		}
	}
}
