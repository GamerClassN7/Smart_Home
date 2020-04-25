<?php 
class ErrorHandler {
    static function exception($exception){
        error_log($exception);
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo '<html>
                <head>
                    <title>Error!</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width">
                </head>
                <body>
                    <h1>Oops!</h1>
                    <p>Something went wrong!</p>
                </body>
            </html>';
        exit;
    }
}
set_exception_handler('ErrorHandler::exception');
 