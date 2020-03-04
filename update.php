<?PHP

header('Content-type: text/plain; charset=utf8', true);

function check_header($name, $value = false)
{
    if (!isset($_SERVER[$name])) {
        return false;
    }
    if ($value && $_SERVER[$name] != $value) {
        return false;
    }
    return true;
}

function sendFile($path)
{
    header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK', true, 200);
    header('Content-Type: application/octet-stream', true);
    header('Content-Disposition: attachment; filename=' . basename($path));
    header('Content-Length: ' . filesize($path), true);
    header('x-MD5: ' . md5_file($path), true);
    readfile($path);
}

if (!check_header('HTTP_USER_AGENT', 'ESP8266-http-Update')) {
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
}

$localBinary = "./app/updater/" . $_SERVER['HTTP_X_ESP8266_STA_MAC'] . ".bin";
sendFile($localBinary);