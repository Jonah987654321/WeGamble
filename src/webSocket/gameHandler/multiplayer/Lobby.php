<?php

require_once __DIR__."/LobbyHandler.php";

class Lobby {
    private string $id;
    private string $lobbyName;
    private int $hasStarted;
    private array $players;
    private bool $isPrivate;
    private ?string $privateLobbyPassword;
    private int $ownerID;
    private int $gameID;
    private int $maxPlayers;

    public function __construct(GameStateMP $startingGS, $lobbyName, $maxPlayers) {
        $this->id = uniqid("mp-lobby_");
        $this->lobbyName = $lobbyName;
        $this->hasStarted = false;
        $this->players = [$startingGS];
        $this->isPrivate = false;
        $this->ownerID = $startingGS->getUserData()["userID"];
        $this->gameID = $startingGS->getGameID();
        $this->maxPlayers = $maxPlayers;
        LobbyHandler::registerLobby($this);
    }

    public function setToPrivate(string $pwd) {
        $this->isPrivate = true;
        $this->privateLobbyPassword = $pwd;
    }

    public function join(GameStateMP $gs, ?string $pwd = null): bool {
        if (($this->isPrivate && $pwd != $this->privateLobbyPassword) || (count($this->players) >= $this->maxPlayers)) {
            return false;
        }
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "playerJoin",
            "data" => [
                "playerID" => $gs->getUserData()["userID"],
                "playerName" => $gs->getUserData()["userName"],
                "playerBalance" => $gs->getUserData()["balance"]
            ]
        ]);
        $this->players[] = $gs;
        return true;
    }

    public function gsHasDisconnected(GameStateMP $gs): void {
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "playerDisconnect",
            "data" => [
                "playerID" => $gs->getUserData()["userID"],
                "playerName" => $gs->getUserData()["userName"],
                "playerBalance" => $gs->getUserData()["balance"]
            ]
        ]);
    }

    public function checkJoinable(int $gameID): bool {
        return !$this->hasStarted
            && $this->gameID == $gameID
            && count($this->players) < $this->maxPlayers;
    }

    public function broadcastEvent(array $data): void {
        foreach ($this->players as $p) {
            if ($p->isConnected()) {
                $p->sendData($data);
            }
        }
    }

    public function toArray(bool $isJoined = false): array {
        $response = [
            "lobbyID" => $this->id,
            "lobbyName" => $this->lobbyName,
            "lobbyIsPrivate" => $this->isPrivate,
            "lobbyMaxPlayers" => $this->maxPlayers,
            "lobbyCurrentPlayerCount" => count($this->players),
            "lobbyHasStarted" => $this->hasStarted
        ];
        if ($isJoined) {
            $response["ownerID"] = $this->ownerID;
            $playerData = [];
            foreach ($this->players as $p) {
                $reducedPlayer = $p->getUserData();
                unset($reducedPlayer["userPassword"]);
                $reducedPlayer["connected"] = $p->isConnected();
                $playerData[] = $reducedPlayer;
            }
            $response["players"] = $playerData;
        }
        return $response;
    }

    public function getID(): string { return $this->id; }
    public function getGameID(): int { return $this->gameID; }
}
