<?PHP
/** Includes **/
include_once('./config.php');

//Autoloader
$files = scandir('./app/class/');
$files = array_diff($files, array(
	'.',
	'..',
	'app',
	'ChartJS.php',
	'ChartJS_Line.php',
	'ChartManager.php',
	'DashboardManager.php',
	'Partial.php',
	'Form.php',
	'Route.php',
	'Template.php',
	'Ajax.php',
));

foreach($files as $file) {
	include './app/class/'.  $file;
}
$logManager = new LogManager();
header('Content-type: text/plain; charset=utf8', true);

/*
function check_header($name, $value = false)
{
    if (!isset($_SERVER[$name])) {
        return false;
    }
    if ($value && $_SERVER[$name] != $value) {
        return false;
    }
    return true;
}*/

function sendFile($path)
{
    header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK', true, 200);
    header('Content-Type: application/octet-stream', true);
    header('Content-Disposition: attachment; filename=' . basename($path));
    header('Content-Length: ' . filesize($path), true);
    header('x-MD5: ' . md5_file($path), true);
    readfile($path);
}


/*if (!check_header('HTTP_USER_AGENT', 'ESP8266-http-Update')) {
    header($_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden', true, 403);
    echo "only for ESP8266 updater!\n";
    exit();
}

if (
    !check_header('HTTP_X_ESP8266_STA_MAC') ||
    !check_header('HTTP_X_ESP8266_AP_MAC') ||
    !check_header('HTTP_X_ESP8266_FREE_SPACE') ||
    !check_header('HTTP_X_ESP8266_SKETCH_SIZE') ||
    !check_header('HTTP_X_ESP8266_SKETCH_MD5') ||
    !check_header('HTTP_X_ESP8266_CHIP_SIZE') ||
    !check_header('HTTP_X_ESP8266_SDK_VERSION')
) {
    header($_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden', true, 403);
    echo "only for ESP8266 updater! (header)\n";
    exit();
}*/




$localBinary = "./app/updater/" . str_replace(':', '', $_SERVER['HTTP_X_ESP8266_STA_MAC']) . ".bin";
$logManager->write("[Update] url: " . $localBinary, LogRecordType::INFO);
$logManager->write("[Update] version: " . $_SERVER['HTTP_X_ESP8266_SKETCH_MD5'], LogRecordType::INFO);
if (file_exists($localBinary)) {
	$logManager->write("[Update] version PHP: " . md5_file($localBinary), LogRecordType::INFO);
	if ($_SERVER['HTTP_X_ESP8266_SKETCH_MD5'] != md5_file($localBinary)) {
    sendFile($localBinary);
	} else {
		header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
	}
} else {
    header("HTTP/1.1 404 Not Found");
}
die();
