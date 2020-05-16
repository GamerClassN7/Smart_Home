<?php
define('DBHOST','');
define('DBNAME','');
define('DBUSER','');
define('DBPASS','');
define('BASEDIR','');

//Leade record newer than XX days
define('RECORDTIMOUT', 90);

//HOME IP ADRESS
//Restrict acess to API
define('HOMEIP', '');

//Randomizet Security Keys
define('FIRSTKEY','');
define('SECONDKEY','');

//show debug codes
define('DEBUGMOD',0);

/** Unit Definition **/
const UNITS = [
	''=> '',
	'temp' => "°C",
	'humi' => "%",
	'light' => "",
	'on/off' => ""
];

//Graph Setting
const RANGES = [
	'' => [
		'min' => 0,
		'max' => 100,
		'scale' =>20,
	],
	'temp' => [
		'min' => 10,
		'max' => 45,
		'scale' =>20,
	],
	'humi' => [
		'min' => 0,
		'max' => 100,
		'scale' =>20,
	],
	'light' => [
		'min' => 0,
		'max' => 1,
		'scale' =>1,
	],
	'on/off' => [
		'min' => 0,
		'max' => 1,
		'scale' =>1,
	],
];
?>
