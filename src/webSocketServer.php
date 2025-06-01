<?php

error_reporting(E_ALL);

require "vendor/autoload.php";
use OmniRoute\utils\Dotenv as config;

config::loadFile(__DIR__."/../.env");

require "modules/dbController.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once "gameHandler/blackjack.php";
require_once "gameHandler/roulette.php";
require_once "gameHandler/hit-the-nick.php";
require_once "gameHandler/slots.php";
use GameHandler\Blackjack;
use GameHandler\Roulette;
use GameHandler\HitTheNick;
use GameHandler\Slots;

define("ERR_INVALID_JSON", 1);
define("ERR_CHECKIN_REQUIRED", 2);
define("ERR_CHECKIN_DATA_INVALID", 3);
define("ERR_API_TOKEN_INVALID", 6);

class APIServer implements MessageComponentInterface {
    protected $clients;
    private array $gameStates;
    private array $cachedGameStates;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->gameStates = [];
        $this->cachedGameStates = [];

        echo "Server constructed";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $keepOpen = false;
        $data = json_decode($msg, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (!key_exists($from->resourceId, $this->gameStates)) {
                if (!key_exists("type", $data) || $data["type"] != "check-in") {
                    $response = [
                        'type' => 'error',
                        "code" => ERR_CHECKIN_REQUIRED,
                        'message' => 'Check-in required',
                    ];
                } else if (!key_exists("apiKey", $data) || !key_exists("gameID", $data)) {
                    $response = [
                        'type' => 'error',
                        "code" => ERR_CHECKIN_DATA_INVALID,
                        'message' => 'Check-in data invalid or misssing',
                    ];
                } else {
                    $userData = validateToken($data["apiKey"]);
                    if (!$userData) {
                        $response = [
                            'type' => 'error',
                            "code" => ERR_API_TOKEN_INVALID,
                            'message' => 'Invalid or expired API token',
                        ];
                    } else {
                        if (array_key_exists($userData["userID"]."|".$data["gameID"], $this->cachedGameStates)) {
                            $gs = $this->cachedGameStates[$userData["userID"]."|".$data["gameID"]];
                            unset($this->cachedGameStates[$userData["userID"]."|".$data["gameID"]]);
                            $restoredData = $gs->onCacheRestore();
                            $this->gameStates[$from->resourceId] = $gs;
                            startTime($gs->getID(), $gs->getUserData()["userID"], $data["gameID"], $gs->getOpenedOn());
                            $response = [
                                "type" => "success",
                                "event" => "check-in",
                                "restored" => true,
                                "restoredData" => $restoredData
                            ];
                            $keepOpen = true;
                        } else {
                            $validID = true;
                            switch ($data["gameID"]) {
                                case 1: //Roulette
                                    $gs = new Roulette();
                                    break;
                                case 2: //Blackjack
                                    $gs = new Blackjack();
                                    break;
                                case 3: //Hit the Nick
                                    $gs = new HitTheNick();
                                    break;
                                case 4: //Slots
                                    $gs = new Slots();
                                    break;
                                default: //Invalid gameID
                                    $validID = false;
                                    break;
                            }
                            if (!$validID) {
                                $response = [
                                    'type' => 'error',
                                    "code" => ERR_CHECKIN_DATA_INVALID,
                                    'message' => 'Check-in data invalid or missing',
                                ];
                            } else {
                                $this->gameStates[$from->resourceId] = $gs;
                                $gs->checkIn($data["apiKey"]);
                                startTime($gs->getID(), $gs->getUserData()["userID"], $data["gameID"], $gs->getOpenedOn());
                                $response = [
                                    "type" => "success",
                                    "event" => "check-in",
                                    "restored" => false
                                ];
                                $keepOpen = true;
                            }
                        }
                    }
                }
            } else {
                $response = $this->gameStates[$from->resourceId]->handleData($data);
                $keepOpen = true;
            }
        } else {
            echo "Invalid JSON received\n";
            $response = [
                'type' => 'error',
                "code" => ERR_INVALID_JSON,
                'message' => 'Invalid JSON format',
            ];
        }
        $from->send(json_encode($response));
        if (!$keepOpen) {
            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (key_exists($conn->resourceId, $this->gameStates)) {
            endTime($this->gameStates[$conn->resourceId]->getID());
            $gs = $this->gameStates[$conn->resourceId];
            if ($gs->cacheOnDc()) {
                $gs->onCache();
                $this->cachedGameStates[$gs->getUserData()["userID"]."|".$gs->getGameID()] = $gs;
            } else {
                $gs->endSession();
            }
            unset($this->gameStates[$conn->resourceId]);
        }


        
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

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