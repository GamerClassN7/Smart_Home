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
$router->any('/oauth', 'Oauth');

$router->post('/api/login', 'AuthApi@login');
$router->post('/api/logout', 'AuthApi@logout');

$router->get('/api/rooms', 'RoomsApi@default');
$router->get('/api/rooms/{roomId}/update', 'RoomsApi@update');

$router->get('/api/devices', 'DevicesApi@default');

$router->post('/api/widgets/{widgetId}/run', 'WidgetApi@run');
$router->post('/api/widgets/{widgetId}/check', 'WidgetApi@check');


$router->any('/api/HA/auth', 'Oauth');
$router->any('/api/HA', 'GoogleHomeApi@response');

// examples
$router->any('/api/example', 'ExampleApi@example');
$router->any('/example', 'ExampleController@index');
$router->any('/example/subpage', 'ExampleController@subpage');

$router->run($_SERVER['REQUEST_METHOD'], '/'.(isset($_GET['url']) ? $_GET['url'] : ''));
