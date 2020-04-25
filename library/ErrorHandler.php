<?php 
class ErrorHandler {
    static function exception($exception){
        error_log($exception);
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo '<h1>Oops!</h1><p>Something went wrong!</p>';
        exit;
    }
}
 