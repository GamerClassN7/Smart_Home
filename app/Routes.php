<?php

$router = new Router();

$router->setDefault(function(){
	echo $_GET['url'].': 404';
});

//Pages
$router->any('/', 'Home');
$router->any('/login', 'Login');
$router->any('/logout', 'Logout');
$router->any('/automation', 'Automation');
$router->any('/dashboard', 'Dashboard');
$router->any('/setting', 'Setting');
$router->any('/scene', 'Scene');
$router->any('/ajax', 'Ajax');
$router->any('/log', 'Log');
$router->any('/rooms', 'Rooms');

$router->get('/api/devices', 'DevicesApi@getAllDevices');
$router->get('/api/login', 'AuthApi@login');

$router->run($_SERVER['REQUEST_METHOD'], '/'.(isset($_GET['url']) ? $_GET['url'] : ''));
