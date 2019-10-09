<?php

//Config
include_once './config.php';

//setup
ini_set ('session.cookie_httponly', '1');
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
ini_set('session.cookie_path', str_replace("login", "", str_replace('https://' . $_SERVER['HTTP_HOST'], "", $_SERVER['REQUEST_URI'])));
ini_set('session.cookie_secure', '1');
session_start ();
mb_internal_encoding ("UTF-8");


//Autoloader
foreach (["class", "views"] as $dir) {
	$files = scandir('./app/'.$dir.'/');
	$files = array_diff($files, array('.', '..', 'app'));

	foreach($files as $file) {
		//echo './app/'.$dir.'/'.  $file;
		include './app/'.$dir.'/'.  $file;
	}
}

/** Language **/
$langTag = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if (DEBUGMOD == 1) {
	echo '<div class="col-md-9 main-body">';
	echo '<pre>';
	echo 'Language SLUG: ' . $langTag;
	echo '</pre>';
	echo '<pre>';
	print_r(get_defined_constants());
	echo '</pre>';
	echo '<pre>';
	print_r(get_defined_vars());
	echo '</pre>';
	echo '</dev>';
}
require_once './app/lang/' . $langTag . '.php';

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//TODO: Přesunout do Login Pohledu
$userManager = new UserManager();
if (isset($_POST['username']) && isset($_POST['password']) ) {
	$userManager->login($_POST['username'], $_POST['password'], (isset ($_POST['remember']) ? $_POST['remember'] : 'false'));
}

$logManager = new LogManager();
/*
$form = new Form('name','1','POST','');
$form->addInput(InputTypes::TEXT,'nadpis','','Label','');
$form->addInput(InputTypes::BUTTON,'nadpis','','Label','test');
$form->addInput(InputTypes::TEXT,'nadpis','','Label','');
$form->addInput(InputTypes::TEXT,'nadpis','','Label','', false);
$form->addInput(InputTypes::TEXT,'nadpis','','Label','');
$form->addInput(InputTypes::CHECK,'nadpis','','Label','');
$form->addInput(InputTypes::TEXT,'nadpis','','Label','');
$arg = array(
'test_v' => 'test',
'test_v2' => 'test',
);
$form->addSelect('1', '1', '1', $arg, false);
$form->render();
die();
*/

$route = new Route();
$route->add('/', 'Home');
$route->add('/login', 'Login');
$route->add('/logout', 'Logout');
$route->add('/automation', 'Automation');
$route->add('/dashboard', 'Dashboard');
$route->add('/setting', 'Setting');
$route->add('/scene', 'Scene');
$route->add('/ajax', 'Ajax');
$route->add('/log', 'Log');
$route->add('/rooms', 'Rooms');

$route->submit();
