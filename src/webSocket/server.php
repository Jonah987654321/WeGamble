<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once __DIR__."/../modules/logging.php";

require_once "gameHandler/blackjack.php";
require_once "gameHandler/roulette.php";
require_once "gameHandler/hit-the-nick.php";
require_once "gameHandler/slots.php";
use GameHandler\Blackjack;
use GameHandler\Roulette;
use GameHandler\HitTheNick;
use GameHandler\Slots;

require_once "gsCache.php";

class APIServer implements MessageComponentInterface {
    protected $clients;
    private array $gameStates;
    private GsCache $cache;
    private $log;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->gameStates = [];
        $this->cache = new GsCache();
        $this->log = getLogger(LOG_WEBSOCKET, true);

        $this->log->info("Server constructed");
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->log->info("New connection opened", ["remoteAddress" => $conn->remoteAddress, "resourceID" => $conn->resourceId]);
    }

    public function onMessage(ConnectionInterface $client, $msg) {
        $data = json_decode($msg, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Received JSON is invalid, close connection
            $this->log->info("Invalid JSON received", ["resourceID" => $client->resourceId, "received" => $msg]);
            $response = [
                'type' => 'error',
                "code" => ERR_INVALID_JSON,
                'message' => 'Invalid JSON format',
            ];
            $client->send(json_encode($response));
            return $client->close();
        }

        if (!key_exists($client->resourceId, $this->gameStates)) {
            // The client is not yet checked in, this JSON needs to be check-in

            if (!key_exists("type", $data) || $data["type"] != "check-in") {
                // Client does not provide check-in, close connection
                $response = [
                    'type' => 'error',
                    "code" => ERR_CHECKIN_REQUIRED,
                    'message' => 'Check-in required',
                ];
                $client->send(json_encode($response));
                return $client->close();
            }

            if (!key_exists("apiKey", $data) || !key_exists("gameID", $data)) {
                // Client missing apiKey or gameID for check-in, close connection
                $response = [
                    'type' => 'error',
                    "code" => ERR_CHECKIN_DATA_INVALID,
                    'message' => 'API key or gameID misssing for check-in',
                ];
                $client->send(json_encode($response));
                return $client->close();
            }

            // Try to get user data based on the provided api key
            $userData = validateToken($data["apiKey"]);

            if (!$userData) {
                // User data could not be fetched -> api key invalid
                $response = [
                    'type' => 'error',
                    "code" => ERR_API_TOKEN_INVALID,
                    'message' => 'Invalid or expired API key',
                ];
                $client->send(json_encode($response));
                return $client->close();
            }

            if ($this->cache->isCached($userData["userID"], $data["gameID"])) {
                // There is a cached gameState for the given check-in game that we can load
                $gs = $this->cache->load($userData["userID"], $data["gameID"]);

                // Tell the gameState it was restored & get the data of the state
                $restoredData = $gs->onCacheRestore();

                // Save the gameState into the current connected clients states
                $this->gameStates[$client->resourceId] = $gs;

                // Start game session for time played stats
                startTime($gs->getID(), $gs->getUserData()["userID"], $data["gameID"], date('Y-m-d H:i:s'));

                $response = [
                    "type" => "success",
                    "event" => "check-in",
                    "restored" => true,
                    "restoredData" => $restoredData
                ];
                $client->send(json_encode($response));
            } else {
                // There is no gameState cached, init a new one
                $validID = true;
                switch ((int) $data["gameID"]) {
                    case GID_ROULETTE;
                        $gs = new Roulette();
                        break;
                    case GID_BLACKJACK:
                        $gs = new Blackjack();
                        break;
                    case GID_HITTHENICK:
                        $gs = new HitTheNick();
                        break;
                    case GID_SLOTS:
                        $gs = new Slots();
                        break;
                    default: //Invalid gameID
                        $validID = false;
                        break;
                }

                if (!$validID) {
                    // Provided gameID is invalid, close connection
                    $response = [
                        'type' => 'error',
                        "code" => ERR_CHECKIN_DATA_INVALID,
                        'message' => 'Check-in data invalid or missing',
                    ];
                    $client->send(json_encode($response));
                    return $client->close();
                }

                // Check-in for new gameState success
                $this->gameStates[$client->resourceId] = $gs;
                $gs->checkIn($data["apiKey"]);
                startTime($gs->getID(), $gs->getUserData()["userID"], $data["gameID"], $gs->getOpenedOn());
                $response = [
                    "type" => "success",
                    "event" => "check-in",
                    "restored" => false
                ];
                $client->send(json_encode($response));
            }
        } else {
            // Client is already checked in, let the game state handle the data
            $response = $this->gameStates[$client->resourceId]->handleData($data);
            $client->send(json_encode($response));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (key_exists($conn->resourceId, $this->gameStates)) {
            // Client was checked in, end session & stats
            endTime($this->gameStates[$conn->resourceId]->getID());
            $gs = $this->gameStates[$conn->resourceId];

            // Check if gameState wants to be cached
            if ($gs->cacheOnDc()) {
                $gs->onCache();
                $this->cache->store($gs);
            } else {
                $gs->endSession();
            }

            unset($this->gameStates[$conn->resourceId]);
        }


        
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $this->log->info("Connection disconnected", ["remoteAddress" => $conn->remoteAddress, "resourceID" => $conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log->error("An error has occurred", [
            "resourceID" => $conn->resourceId,
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        
        try {
            $conn->send(json_encode([
                            'type' => 'error',
                            "code" => 500,
                            'message' => 'Internal server error',
            ]));
        } catch (\Exception $e) {
            $this->log->warning("Failed to deliver closing error message to client", ["resourceID" => $conn->resourceId]);
        }

        $conn->close();
    }
}

?>