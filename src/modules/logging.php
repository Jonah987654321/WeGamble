<?php

require_once __DIR__."/../vendor/autoload.php";

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

define("LOG_WEBSOCKET", "websocket");
define("LOG_", "");

function getLogger(string $type, bool $toConsole = false): Logger {
    $logger = new Logger($type);

    if ($toConsole) {
        $logger->pushHandler(new StreamHandler("php://stdout"));
    }
    $logger->pushHandler(new RotatingFileHandler(__DIR__."/../../logs/".$type.".log", 7));
    $logger->pushHandler(new RotatingFileHandler(__DIR__."/../../logs/application.log", 7, Level::Error));

    return $logger;
}

?>