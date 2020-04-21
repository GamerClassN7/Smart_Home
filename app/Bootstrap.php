<?php
// import autoload
//Autoloader

Class Autoloader {
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
                break;
            }
        }
    }

    static function setRoot($rootPath){
        static::$root = $rootPath;
    }
}

Autoloader::setRoot('/var/www/dev.steelants.cz/vasek/home-update/');
spl_autoload_register("Autoloader::ClassLoader");

//setup
ini_set ('session.cookie_httponly', '1');
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
ini_set('session.cookie_path', str_replace("login", "", str_replace('https://' . $_SERVER['HTTP_HOST'], "", $_SERVER['REQUEST_URI'])));
ini_set('session.cookie_secure', '1');
session_start ();
mb_internal_encoding ("UTF-8");

//Language
$langTag = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$langMng = new LanguageManager($langTag);
$langMng->load();

//DB Conector
Db::connect (DBHOST, DBUSER, DBPASS, DBNAME);

//TODO: Přesunout do Login Pohledu
$userManager = new UserManager();

//Logs
$logManager = new LogManager();

// import routes
require_once './Routes.php';
