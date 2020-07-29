<?php
//Database Conections
define('DBHOST','');
define('DBNAME','');
define('DBUSER','');
define('DBPASS','');
define('BASEDIR','');
define('BASEURL','');

//Leade record newer than XX days
define('RECORDTIMOUT', 1);

//Log Setting
define('DEBUGMOD', 1); //show debug codes
define('API_DEBUGMOD', 1); //show debug codes in api
define('LOGTIMOUT', 5); //Deleate logfiles older than XX days
/*
	LogRecordTypes::ERROR
	LogRecordTypes::WARNING
	LogRecordTypes::INFO
*/
define('LOGLEVEL', LogRecordTypes::INFO);

//Restrict acess to API
$allowerdIp = [
	'', //IP Domácí sítě
];
define('HOMEIP', $allowerdIp);

//Randomizet Security Keys
define('FIRSTKEY','');
define('SECONDKEY','');

//DEFAULT CONECT INTERVALS in seconds
define('DEVICECONNECTINTERVAL', 60);

//time format
define('DATEFORMAT','d. m. Y H:i');
define('TIMEFORMAT','H:i');

/** Unit Definition **/
const UNITS = [
	''=> '',
	'temp' => "°C",
	'temp_cont' => "°C",
	'humi' => "%",
	'light' => "",
	'on/off' => "",
	'doot' => "",
	'battery' => "V",
	'water' => "",
	'wifi' => "Bpm",
	'media_state' => "",
	'media_cont' => "",
	'vol_cont' => "",
	'media_apps' => "",
	'media_input' => "",

];

//Notifications
define('SERVERKEY','');
define('SERVERVAIPKEY','');

//TODO: Po registraci vzít výchozí hodnoty
//Default network Setting
const NETWORK = [
	'subnet' => '',
	'gateway' => '',
	'dns' => '',
];

//Graph Setting
const RANGES = [
	'' => [
		'min' => 0,
		'max' => 100,
		'scale' => 20,
		'graph' => 'line',
	],
	'temp' => [
		'min' => 10,
		'max' => 45,
		'scale' =>20,
		'graph' => 'line',
	],
	'humi' => [
		'min' => 0,
		'max' => 100,
		'scale' =>20,
		'graph' => 'line',
	],
	'light' => [
		'min' => 0,
		'max' => 1,
		'scale' =>1,
		'graph' => 'bar',
	],
	'on/off' => [
		'min' => 0,
		'max' => 1,
		'scale' =>1,
		'graph' => 'bar',
	],
	'door' => [
		'min' => 0,
		'max' => 1,
		'scale' =>1,
		'graph' => 'bar',
		'fallBack' => 1,
		'fallBackTime' => 2, //in Minutes
	],
	'battery' => [
		'min' => 0,
		'max' => 4.7,
		'scale' =>0,
		'graph' => 'line',
	],
	'temp_cont' => [
		'min' => 0,
		'max' => 30,
		'scale' => 0,5,
		'graph' => 'line',
	],
	'wifi' => [
		'min' => 0,
		'max' => 30,
		'scale' => 0,5,
		'graph' => 'line',
	],
];



?>
