<?php

$router = new Router();

$router->setDefault(function(){
	echo $_GET['url'].': 404';
});

//Pages
$router->any('/', 'Log');
$router->any('/login', 'Login');
$router->any('/logout', 'Logout');
$router->any('/automation', 'Automation');
$router->any('/setting', 'Setting');
$router->any('/ajax', 'Ajax');

$router->post('/api/devices', 'DevicesApi@getAllDevices');
$router->post('/api/login', 'AuthApi@login');

$router->get('/api/HA/auth', 'GoogleHomeApi@autorize');
$router->any('/api/HA', 'GoogleHomeApi@response');

// examples
$router->any('/api/example', 'ExampleApi@example');
$router->any('/example', 'ExampleController@index');
$router->any('/example/subpage', 'ExampleController@subpage');

$router->run($_SERVER['REQUEST_METHOD'], '/'.(isset($_GET['url']) ? $_GET['url'] : ''));
