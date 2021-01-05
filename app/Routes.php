<?php

$router = new Router();

$router->setDefault(function(){
	echo $_GET['url'].': 404';
	$logManager = new LogManager();
	$logManager->setLevel(LOGLEVEL);
	$logManager->write("[ROUTER]" . $_GET['url'] . "not found", LogRecordTypes::WARNING);
	unset($logManager);
});

//Pages
$router->any('/', 'Log');
$router->any('/log', 'Log');
$router->any('/server', 'Server');
$router->any('/login', 'Login');
$router->any('/logout', 'Logout');
$router->any('/automation', 'Automation');
$router->any('/setting', 'Setting');
$router->any('/device', 'Device');
$router->any('/device/{sortBy}/{sortType}', 'Device');
$router->any('/plugins', 'Plugins');
$router->any('/ajax', 'Ajax');
$router->any('/oauth', 'Oauth');

//Vue APP
$router->post('/api/login', 'AuthApi@login');
$router->post('/api/logout', 'AuthApi@logout');
$router->get('/api/rooms', 'RoomsApi@default');
$router->get('/api/rooms/{roomId}/update', 'RoomsApi@update');

$router->get('/api/devices', 'DevicesApi@default');
$router->get('/api/plugins', 'PluginsApi@default');
$router->get('/api/users', 'UsersApi@default');
$router->get('/api/server', 'ServerApi@default');
$router->get('/api/server/log', 'ServerApi@logStatus');

$router->post('/api/widgets/{widgetId}/run', 'WidgetApi@run');
$router->get('/api/widgets/{widgetId}/detail', 'WidgetApi@detail');
$router->get('/api/widgets/{widgetId}/detail/{period}', 'WidgetApi@detail');


//cron
$router->post('/cron/clean', 'CronApi@clean');
$router->post('/cron/fetch', 'CronApi@fetch');

//Google Home - API
$router->any('/api/HA/auth', 'Oauth');
$router->any('/api/HA', 'GoogleHomeApi@response');

//Endpoints API
$router->post('/api/endpoint/', 'EndpointsApi@default');
$router->any('/api/update/', 'UpdatesApi@default');
$router->any('/api/users/status', 'UsersApi@status');
$router->any('/api/users/subscribe', 'UsersApi@subscribe');

// examples
$router->any('/api/example', 'ExampleApi@example');
$router->any('/example', 'ExampleController@index');
$router->any('/example/subpage', 'ExampleController@subpage');

//module routes
//$router->any('/plugins/spotify/callback', 'Spotify@callback');

$router->run($_SERVER['REQUEST_METHOD'], '/'.(isset($_GET['url']) ? $_GET['url'] : ''));
