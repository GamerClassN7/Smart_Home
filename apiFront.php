<?php
/** Includes **/
include_once('./config.php');

//Autoloader
$files = scandir('./app/class/');
$files = array_diff($files, array(
	'.',
	'..',
	'app',
	'ChartJS.php',
	'ChartJS_Line.php',
	'ChartManager.php',
	'DashboardManager.php',
	'Partial.php',
	'Form.php',
	'Route.php',
	'Template.php',
	'Ajax.php',
));

foreach($files as $file) {
	include './app/class/'.  $file;
}

//Log
$apiLogManager = new LogManager('./app/logs/apiFront/'. date("Y-m-d").'.log');

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//Read API data
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

//Log RAW api request
if (API_DEBUGMOD == 1) {
	$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
}

unset($logManager);
Db::disconect();
die();
