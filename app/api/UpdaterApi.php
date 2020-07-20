<?php
class UpdatesApi {
    private function sendFile($path)	{
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK', true, 200);
        header('Content-Type: application/octet-stream', true);
        header('Content-Disposition: attachment; filename=' . basename($path));
        header('Content-Length: ' . filesize($path), true);
        header('x-MD5: ' . md5_file($path), true);
        readfile($path);
    }

    public function default(){
		 header('Content-type: text/plain; charset=utf8', true);
        $logManager = new LogManager('../logs/ota/'. date("Y-m-d").'.log');

        //Filtrování IP adress
       	/* if (DEBUGMOD != 1) {
            if (!in_array($_SERVER['REMOTE_ADDR'], HOMEIP)) {
                echo json_encode(array(
                    'state' => 'unsuccess',
                    'errorMSG' => "Using API from your IP insnt alowed!",
                ));
                header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
                $logManager->write("[Updater] acces denied from " . $_SERVER['REMOTE_ADDR'], LogRecordType::WARNING);
                exit();
            }
        }*/

        $macAddress = $_SERVER['HTTP_X_ESP8266_STA_MAC'];
		  $localBinary = "../updater/" . str_replace(':', '', $macAddress) . ".bin";

        $logManager->write("[Updater] url: " . $localBinary, LogRecordType::INFO);
        $logManager->write("[Updater] version: " . $_SERVER['HTTP_X_ESP8266_SKETCH_MD5'], LogRecordType::INFO);
        if (file_exists($localBinary)) {
            $logManager->write("[Updater] version PHP: \n" . md5_file($localBinary), LogRecordType::INFO);
            if ($_SERVER['HTTP_X_ESP8266_SKETCH_MD5'] != md5_file($localBinary)) {
                $this->sendFile($localBinary);
                //get device data
                $device = DeviceManager::getDeviceByMac($macAddress);
                $deviceName = $device['name'];
                $deviceId = $device['device_id'];
                //logfile write
                $logManager->write("[Device] device_ID " . $deviceId . " was just updated to new version", LogRecordType::WARNING);
                $logManager->write("[Device] version hash: \n" . md5_file($localBinary), LogRecordType::INFO);
                //notification
                $notificationMng = new NotificationManager;
                $notificationData = [
                    'title' => 'Info',
                    'body' => $deviceName.' was just updated to new version',
                    'icon' => BASEDIR . '/app/templates/images/icon-192x192.png',
                ];
                if ($notificationData != []) {
                    $subscribers = $notificationMng->getSubscription();
                    foreach ($subscribers as $key => $subscriber) {
                        $logManager->write("[NOTIFICATION] SENDING TO " . $subscriber['id'] . " ", LogRecordType::INFO);
                        $answer = $notificationMng->sendSimpleNotification(SERVERKEY, $subscriber['token'], $notificationData);
                    }
                }
            } else {
                header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
            }
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        }
        die();
    }
}