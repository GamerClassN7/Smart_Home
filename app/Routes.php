<?php

$router = new Router();

$router->setDefault(function(){
	echo '404';
});

$router->run($_SERVER['REQUEST_METHOD'], '/'.$_GET['url']);
