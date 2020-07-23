<?php

$router = new Router();

$router->setDefault(function(){
	echo $_GET['url'].': 404';
});

//Pages
$router->any('/', 'Log');
$router->any('/log', 'Log');
$router->any('/server', 'Server');
$router->any('/login', 'Login');
$router->any('/logout', 'Logout');
$router->any('/automation', 'Automation');
$router->any('/setting', 'Setting');
$router->any('/ajax', 'Ajax');
$router->any('/oauth', 'Oauth');

//Vue APP
$router->post('/api/login', 'AuthApi@login');
$router->post('/api/logout', 'AuthApi@logout');
$router->get('/api/rooms', 'RoomsApi@default');
$router->get('/api/rooms/{roomId}/update', 'RoomsApi@update');
$router->get('/api/devices', 'DevicesApi@default');
$router->get('/api/users', 'UsersApi@default');
$router->post('/api/widgets/{widgetId}/run', 'WidgetApi@run');
$router->post('/api/widgets/{widgetId}/detail', 'WidgetApi@detail');

//cron
$router->post('/cron/clean', 'CronApi@clean');

//Google Home - API
$router->any('/api/HA/auth', 'Oauth');
$router->any('/api/HA', 'GoogleHomeApi@response');

//Endpoints API
$router->post('/api/endpoint/', 'EndpointsApi@default');
$router->any('/api/update/', 'UpdatesApi@default');
$router->any('/api/users/status', 'UsersApi@status');

// examples
$router->any('/api/example', 'ExampleApi@example');
$router->any('/example', 'ExampleController@index');
$router->any('/example/subpage', 'ExampleController@subpage');

$router->run($_SERVER['REQUEST_METHOD'], '/'.(isset($_GET['url']) ? $_GET['url'] : ''));
