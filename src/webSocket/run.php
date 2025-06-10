<?php

define("ERR_INVALID_JSON", 1);
define("ERR_CHECKIN_REQUIRED", 2);
define("ERR_CHECKIN_DATA_INVALID", 3);
define("ERR_API_TOKEN_INVALID", 6);

define("GID_ROULETTE", 1);
define("GID_BLACKJACK", 2);
define("GID_HITTHENICK", 3);
define("GID_SLOTS", 4);

require_once __DIR__."/../vendor/autoload.php";

use OmniRoute\utils\Dotenv as config;
config::loadFile(__DIR__."/../../.env");

require __DIR__."/../modules/dbController.php";

require_once "server.php";

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new APIServer()
        )
    ),
    8443
);

$server->run();

?>