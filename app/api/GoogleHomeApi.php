<?php
class GoogleHomeApi{
	static function response(){
		//$this->requireAuth();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/google-home/'. date("Y-m-d").'.log');
		$apiLogManager->setLevel(LOGLEVEL);

		header('Content-Type: application/json');

		switch ($obj['inputs'][0]['intent']) {
			case 'action.devices.SYNC':
			GoogleHome::sync($obj['requestId']);
			$apiLogManager->write("[Google Home] action.devices.SYNC", LogRecordTypes::INFO);
			break;

			case 'action.devices.QUERY':
			GoogleHome::query($obj['requestId'], $obj['inputs'][0]['payload']);
			$apiLogManager->write("[Google Home] action.devices.QUERY", LogRecordTypes::INFO);
			$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
			break;

			case 'action.devices.EXECUTE':

			GoogleHome::execute($obj['requestId'], $obj['inputs'][0]['payload']);
			$apiLogManager->write("[Google Home] action.devices.EXECUTE", LogRecordTypes::INFO);
			$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordTypes::INFO);

			break;
		}

		unset($apiLogManager);
	}

	static function autorize(){
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);

		$apiLogManager = new LogManager('../logs/google-home/'. date("Y-m-d").'.log');
		$apiLogManager->setLevel(LOGLEVEL);
		$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
		$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
		unset($apiLogManager);

		$get = [
			"access_token"=>"2222255888", //TODO: FIX
			"token_type"=>"Bearer",
			"state"=>$_GET["state"],
		];

		echo $_GET["redirect_uri"] . '#' . http_build_query($get) ;
		echo '<a href="'.$_GET["redirect_uri"] . '#' . http_build_query($get) . '">FINISH</a>';
	}
}
