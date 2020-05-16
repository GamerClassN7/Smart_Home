<?php
class GoogleHomeApi{
	static function response(){
		//$this->requireAuth();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		header('Content-Type: application/json');

		switch ($obj['inputs'][0]['intent']) {
			case 'action.devices.SYNC':
			GoogleHome::sync($obj['requestId']);
			$apiLogManager->write("[Google Home] action.devices.SYNC", LogRecordType::INFO);
			break;

			case 'action.devices.QUERY':
			GoogleHome::query($obj['requestId'], $obj['inputs'][0]['payload']);
			//$apiLogManager->write("[Google Home] action.devices.QUERY", LogRecordType::INFO);
			break;

			case 'action.devices.EXECUTE':
			GoogleHome::execute($obj['requestId'], $obj['inputs'][0]['payload']);
			$apiLogManager->write("[Google Home] action.devices.EXECUTE", LogRecordType::INFO);
			break;
		}
	}

	static function autorize(){
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/api/HA/'. date("Y-m-d").'.log');
		$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
		$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordType::INFO);

		$get = [
			"access_token"=>"2222255888",
			"token_type"=>"Bearer",
			"state"=>$_GET["state"],
		];

		echo $_GET["redirect_uri"] . '#' . http_build_query($get) ;
		echo '<a href="'.$_GET["redirect_uri"] . '#' . http_build_query($get) . '">FINISH</a>';
	}
}
