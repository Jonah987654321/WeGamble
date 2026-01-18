<?php

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../codeNumberRegister.php";

use OmniRoute\utils\Dotenv as config;
config::loadFile(__DIR__."/../../.env");

require __DIR__."/../modules/dbController.php";
require_once __DIR__."/../modules/logging.php";

require_once "server.php";

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\SecureServer;
use React\Socket\SocketServer;
use React\EventLoop\Loop;

$log = getLogger(LOG_WEBSOCKET);

if (config::get("WS_ENABLE_SSL")) {
    // We need to provide the websocket over WSS

    if (!config::has("SSL_CERT_PATH") || !config::has("SSL_KEY_PATH")) {
        // .env is missing required vars
        $log->emergency("Environment is set to SSL mode but missing SSL_CERT_PATH and/or SSL_KEY_PATH.");
        exit("Invalid .env config");
    }

    $sslCert = config::get("SSL_CERT_PATH");
    if (!is_readable($sslCert)) {
        $log->emergency("SSL_CERT_PATH not readable");
        exit("SSL_CERT_PATH not readable");
    }

    $sslKey = config::get("SSL_KEY_PATH");
    if (!is_readable($sslKey)) {
        $log->emergency("SSL_KEY_PATH not readable");
        exit("SSL_KEY_PATH not readable");
    }

    $sslConfig = [
        'local_cert' => $sslCert,
        'local_pk' => $sslKey,
        'allow_self_signed' => true,
        'verify_peer' => false
    ];

    /* === WARNING!!! ===
        'allow_self_signed' => true,
        'verify_peer' => false

        should normally be the other way, only currently like that due to server problems
    */

    $loop = Loop::get();
    $socket = new SocketServer('0.0.0.0:8443', [], $loop);
    $secureSocket = new SecureServer($socket, $loop, $sslConfig);
    
    $server = new IoServer(
        new HttpServer(
            new WsServer(
                new APIServer()
            )
        ),
        $secureSocket,
        $loop
    );
} else {
    // Start WebSocket under WS protocol
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new APIServer()
            )
        ),
        8443
    );
}

$log->info("Starting server", ["SSL"=>config::get("WS_ENABLE_SSL")]);
$server->run();

?>