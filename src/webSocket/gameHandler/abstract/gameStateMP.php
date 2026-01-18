<?php

require_once __DIR__."/../multiplayer/Lobby.php";

class GameStateMP extends GameState {
    protected ?Lobby $lobby;
    protected bool $isInLobby;

    public function __construct(int $gameID) {
        parent::__construct($gameID);
        $this->lobby = null;
        $this->isInLobby = false;
    }

    public function handleData(array $data): array {
        parent::updateUserData();
        if ($this->userData == false) {
            return [
                'type' => 'error',
                "code" => ERR_API_TOKEN_INVALID,
                'message' => 'Invalid or expired API token',
            ];
        }

        if(!isset($data["type"])) {
            return [
                'type' => 'error',
                "code" => ERR_MISSING_ACTION,
                'message' => 'Missing type of action',
            ];
        }

        if (!$this->isInLobby) {
            if ($data["type"] == "requestLobbies") {
                $response = [
                    "type" => "success",
                    "event" => "requestLobbies",
                    "lobbies" => []
                ];
                foreach (LobbyHandler::getLobbyListByGameID($this->getGameID()) as $lobby) {
                    $response["lobbies"][] = $lobby->toArray();
                }
                return $response;
            } else if ($data["type"] == "joinLobby") {
                if (!isset($data["lobbyID"])) {
                    return [
                        'type' => 'error',
                        "code" => ERR_MISSING_DATA,
                        'message' => 'Missing lobbyID to join',
                    ];
                }

                if(!$this->tryLobbyJoin($data["lobbyID"], isset($data["lobbyPassword"])?$data["lobbyPassword"]:null)) {
                    return [
                        'type' => 'error',
                        "code" => ERR_LOBBY_JOIN_FAILED,
                        'message' => 'Failed to join lobby',
                    ];
                }

                return [
                    "type" => "success",
                    "event" => "joinLobby",
                    "lobbyData" => $this->lobby->toArray(true)
                ];
            } else if ($data["type"] == "createLobby") {
                if (!isset($data["lobbyName"])) {
                    return [
                        'type' => 'error',
                        "code" => ERR_MISSING_DATA,
                        'message' => 'Missing lobbyName to create',
                    ];
                }

                $this->lobby = $this->createGameLobby($data);
                $this->isInLobby = true;
                return [
                    "type" => "success",
                    "event" => "createLobby",
                    "lobbyData" => $this->lobby->toArray(true)
                ];
            }

            return [
                'type' => 'error',
                "code" => ERR_INVALID_ACTION,
                'message' => 'The action type provided is not valid for the current game state',
            ];
        } else {
            return $this->handleGameLogic($data);
        }
    }

    public function createGameLobby(array $data): Lobby {
        $lobby = new Lobby($this, $data["lobbyName"], 100);
        if (isset($data["lobbyPassword"]) && trim($data["lobbyPassword"]) !== "") {
            $lobby->setToPrivate($data["lobbyPassword"]);
        }
        return $lobby;
    }

    public function handleGameLogic(array $data): array {
        return [];
    }

    public function getAvailableLobbies(): array {
        return LobbyHandler::getLobbyListByGameID($this->getGameID());
    }

    public function tryLobbyJoin(string $lobbyID, ?string $pwd = null): bool {
        $lb = LobbyHandler::getLobbyByID($lobbyID);
        if ($lb != null && $lb->join($this, $pwd)) {
            $this->lobby = $lb;
            $this->isInLobby = true;
            return true;
        }
        return false;
    }
}

?>