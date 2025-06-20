<?php

require_once __DIR__."/../vendor/autoload.php";

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor;
use Monolog\Formatter\LineFormatter;
use Monolog\ErrorHandler;

use OmniRoute\utils\Dotenv as config;

define("LOG_WEBSOCKET", "websocket");
define("LOG_", "");

function getLogger(string $type): Logger {
    $logger = new Logger($type);

    $handlers = [];
    if (config::get("APP_ENV") == "development") {
        $handlers[] = new StreamHandler("php://stdout", Level::Debug);
        $handlers[] = new RotatingFileHandler(__DIR__."/../../logs/dev.log", 1, Level::Debug);
    }
    $handlers[] = new RotatingFileHandler(__DIR__."/../../logs/".$type.".log", 7, Level::Info);
    $handlers[] = new RotatingFileHandler(__DIR__."/../../logs/application.log", 7, Level::Error);

    foreach ($handlers as $h) {
        $dateFormat = "Y-m-d H:i:s";
        $logFormat = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($logFormat, $dateFormat);
        $h->setFormatter($formatter);
        
        $logger->pushHandler($h);
    }

    $logger->pushProcessor(new Processor\WebProcessor());
    $logger->pushProcessor(new Processor\HostnameProcessor());

    ErrorHandler::register($logger);

    return $logger;
}

?>