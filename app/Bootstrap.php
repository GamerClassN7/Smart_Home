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


// import routes
require_once './Routes.php';
