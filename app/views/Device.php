<?php


class Device extends Template
{
	function __construct () {
		$userManager = new UserManager ();
		$deviceManager = new DeviceManager ();
		$langMng = new LanguageManager ('en');

		if (!$userManager->isLogin ()) {
			header ('Location: ' . BASEURL . 'device');
		}

		$template = new Template ('device');
		$template->prepare ('title', $langMng->get ("m_devices"));

		$devices = $deviceManager->getAllDevices ();

		foreach ($devices as $key => $device) {
			$localBinary = "../updater/" . str_replace (':', '', $device['mac']) . ".bin";
			if (file_exists ($localBinary)) {
				$hash = md5_file ($localBinary);
				if ($hash == $device['firmware_hash']) {
					$devices[$key]['firmware_hash'] = "true";
				} else {
					$devices[$key]['firmware_hash'] = "need";
				}
			} else {
				$devices[$key]['firmware_hash'] = "false";
			}
		}

		$template->prepare ('baseDir', BASEDIR);
		$template->prepare ('debugMod', DEBUGMOD);
		$template->prepare ('logToLiveTime', LOGTIMOUT);
		$template->prepare ('devices', $devices);
		$template->prepare ('langMng', $langMng);

		$template->render ();
	}
}
