<?php
//Debug
error_reporting(E_ALL);
ini_set( 'display_errors','1');

//setup
parse_str($_SERVER['QUERY_STRING'], $params);
if (defined ("BASEDIR")) {
	$urlSes = BASEDIR;
} else {
	$urlSes = str_replace((!empty ($params['url']) ? $params['url'] : ""), "", str_replace('https://' . $_SERVER['HTTP_HOST'], "", $_SERVER['REQUEST_URI']));
}
if (defined ("BASEDIR") && defined ("BASEURL")) {
	$domain = str_replace("http://", "", str_replace("https://", "", str_replace(BASEDIR, "", BASEURL)));
} else {
	$domain = str_replace("/var/www/", "", $_SERVER['DOCUMENT_ROOT']);
}
session_set_cookie_params(
    1209600,
    $urlSes,
    $domain,
    true,
    true
);
/*ini_set ('session.cookie_httponly', '1');
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
ini_set('session.cookie_path', str_replace('login', "", str_replace('https://' . $_SERVER['HTTP_HOST'], "", $_SERVER['REQUEST_URI'])));
ini_set('session.cookie_secure', '1');
ini_set('session.gc_maxlifetime', 1209600);*/
mb_internal_encoding ("UTF-8");

session_start();

// import configs
require_once '../library/Debugger.php';

Debugger::flag('loaders');

//Autoloader
class Autoloader {
	protected static $extension = ".php";
	protected static $root = __DIR__;
	protected static $files = [];

	static function ClassLoader ($className = ""){
		$directorys = new RecursiveDirectoryIterator(static::$root, RecursiveDirectoryIterator::SKIP_DOTS);

		//echo '<pre>';
		//var_dump($directorys);
		//echo '</pre>';

		$files = new RecursiveIteratorIterator($directorys, RecursiveIteratorIterator::LEAVES_ONLY);

		$filename = $className . static::$extension;

		foreach ($files as $key => $file) {
			if (strtolower($file->getFilename()) === strtolower($filename) && $file->isReadable()) {
				include_once $file->getPathname();
				return;
			}
		}
	}

	static function setRoot($rootPath){
		static::$root = $rootPath;
	}
}

spl_autoload_register("Autoloader::ClassLoader");
Autoloader::setRoot('/var/www/dev.steelants.cz/vasek/home-update/');

// import configs
require_once '../config/config.php';

class ErrorHandler {
	static function exception($exception){
		error_log($exception);
		http_response_code($exception->getCode());
		$message = [
			'code' => $exception->getCode(),
			'message' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'trace' => $exception->getTrace(),
		];
		echo json_encode($message);

		$apiLogManager = new LogManager('../logs/apache/'. date("Y-m-d").'.log');
		$apiLogManager->setLevel(LOGLEVEL);
		$apiLogManager->write("[APACHE]\n" . json_encode($message, JSON_PRETTY_PRINT), LogRecordTypes::ERROR);
	}
}
set_exception_handler("ErrorHandler::exception");

Debugger::flag('preload');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

$apiLogManager = new LogManager('../logs/api/'. date("Y-m-d").'.log');
$apiLogManager->setLevel(LOGLEVEL);

$apiLogManager->write("[API] headers\n" . json_encode($_SERVER, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
$apiLogManager->write("[API] POST  body\n" . json_encode($_POST, JSON_PRETTY_PRINT), LogRecordTypes::INFO);
$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordTypes::INFO);



Debugger::flag('dbconnect');
//D B Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

Debugger::flag('routes');
// import routes
require_once '../app/Routes.php';

Debugger::flag('done');
// echo Debugger::showFlags(false);
