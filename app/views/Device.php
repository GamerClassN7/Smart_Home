<?php


class Device extends Template
{
	function __construct () {
		$userManager = new UserManager ();
		$deviceManager = new DeviceManager ();
		$subDeviceManager = new SubDeviceManager ();
		$recordManager = new RecordManager ();
		$roomManager = new RoomManager ();
		$langMng = new LanguageManager ('en');

		if (!$userManager->isLogin()){
			header('Location: ' . BASEURL . 'login');
		}

		$template = new Template ('device');
		$template->prepare ('title', $langMng->get ("m_devices"));

		if (!empty ($_GET['sort']) && !empty ($_SESSION['sort']) && $_SESSION['sort'] != $_GET['sort']) {
			unset($_SESSION['sort']);
			header('Location: device?sort=' . $_GET["sort"] . "&sortType=ASC");
			die();
		}

		if (isset ($_GET['sortType'])) {
			switch ($_GET['sortType']) {
				case "DESC":
				$sortType = "";
				$sortIcon = "&#xf0dd";
				break;
				case "ASC":
				$sortType = "DESC";
				$sortIcon = "&#xf0de";
				break;
				case "":
				unset($_GET["sort"]);
				unset($_GET["sortType"]);
				header('Location: device');
				die();
				break;
			}
		} else {
			$sortType = "ASC";
		}

		if (!empty ($_GET['sort']) && !empty ($_GET['sortType'])) {
			$template->prepare ('sortIcon', array ($_GET['sort'] => $sortIcon));
			$actualSort = "devices.device_id";
			switch ($_GET['sort']) {
				case "name":
				$actualSort = "devices.name";
				break;
				case "room":
				$actualSort = "rooms.name";
				break;
				case "ip":
				$actualSort = "devices.ip_address";
				break;
				case "mac":
				$actualSort = "devices.mac";
				break;
				case "token":
				$actualSort = "devices.token";
				break;
			}
			$devices = $deviceManager->getAllDevicesSorted ($actualSort, $_GET['sortType']);
		} else {
			$devices = $deviceManager->getAllDevices ();
		}

		if (!empty ($_GET['sort'])) {
			$_SESSION['sort'] = $_GET['sort'];
		}

		foreach ($devices as $key => $device) {
			$subdevice = $subDeviceManager->getSubDeviceByMasterAndType ($device['device_id'], "wifi");
			if (!empty ($subdevice['subdevice_id'])) {
				$record = $recordManager->getLastRecord($subdevice['subdevice_id']);
				if (!empty ($record)) {
					$devices[$key]['signal'] = $record['value'] . " " . $subdevice['unit'];
				}
			}
			if (empty ($devices[$key]['signal'])) {
				$devices[$key]['signal'] = "";
			}
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
			if (empty ($device['mac'])) {
				$devices[$key]['firmware_hash'] = "";
			}
		}

		if (!empty ($_GET['sort']) && !empty ($_GET['sortType']) && $_GET['sort'] == "firmware") {
			if ($_GET['sortType'] == "DESC") {
				usort($devices, function($a, $b) {
					return $a['firmware_hash'] <=> $b['firmware_hash'];
				});
			} else if ($_GET['sortType'] == "ASC") {
				usort($devices, function($a, $b) {
					return $b['firmware_hash'] <=> $a['firmware_hash'];
				});
			}
		} else if (!empty ($_GET['sort']) && !empty ($_GET['sortType']) && $_GET['sort'] == "signal") {
			if ($_GET['sortType'] == "DESC") {
				usort($devices, function($a, $b) {
					return $a['signal'] <=> $b['signal'];
				});
			} else if ($_GET['sortType'] == "ASC") {
				usort($devices, function($a, $b) {
					return $b['signal'] <=> $a['signal'];
				});
			}
		}

		$rooms = $roomManager->getAllRooms();

		$template->prepare ('baseDir', BASEDIR);
		$template->prepare ('debugMod', DEBUGMOD);
		$template->prepare ('logToLiveTime', LOGTIMOUT);
		$template->prepare ('rooms', $rooms);
		$template->prepare ('sortType', $sortType);
		$template->prepare ('devices', $devices);
		$template->prepare ('langMng', $langMng);

		$template->render ();
	}
}
