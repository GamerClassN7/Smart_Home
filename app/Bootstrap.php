<?php
session_start();

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

class ErrorHandler {
	static function exception($exception){
		error_log($exception);
		http_response_code($exception->getCode());
		$message = [
			'code' => $exception->getCode(),
			'message' => $exception->getMessage(),
		];
		echo json_encode($message);

		$apiLogManager = new LogManager('../logs/apache/'. date("Y-m-d").'.log');
		$apiLogManager->write("[APACHE] ERROR\n" . json_encode($message, JSON_PRETTY_PRINT), LogRecordType::INFO);
	}
}
set_exception_handler("ErrorHandler::exception");

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

$apiLogManager = new LogManager('../logs/api/'. date("Y-m-d").'.log');

$apiLogManager->write("[API] request body\n" . json_encode($obj, JSON_PRETTY_PRINT), LogRecordType::INFO);
$apiLogManager->write("[API] POST  body\n" . json_encode($_POST, JSON_PRETTY_PRINT), LogRecordType::INFO);
$apiLogManager->write("[API] GET body\n" . json_encode($_GET, JSON_PRETTY_PRINT), LogRecordType::INFO);

//Debug
error_reporting(E_ALL);
ini_set( 'display_errors','1');

//setup
ini_set ('session.cookie_httponly', '1');
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
ini_set('session.cookie_path', str_replace("login", "", str_replace('https://' . $_SERVER['HTTP_HOST'], "", $_SERVER['REQUEST_URI'])));
ini_set('session.cookie_secure', '1');
mb_internal_encoding ("UTF-8");

// import configs
require_once '../config/config.php';

// Logs
$logManager = new LogManager();

// Language
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$langTag = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	$langMng = new LanguageManager($langTag);
	$langMng->load();
}

//D B Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

// TODO: Přesunout do Login Pohledu
$userManager = new UserManager();

// import routes
require_once '../app/Routes.php';
