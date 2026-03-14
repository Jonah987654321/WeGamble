<?php

require_once __DIR__."/LobbyHandler.php";

class Lobby {
    protected string $id;
    protected string $lobbyName;
    protected int $hasStarted;
    protected array $players;
    protected bool $isPrivate;
    protected ?string $privateLobbyPassword;
    protected int $ownerID;
    protected int $gameID;
    protected int $maxPlayers;
    protected int $minPlayers;

    public function __construct(GameStateMP $startingGS, $lobbyName, $maxPlayers, $minPlayers) {
        $this->id = uniqid("mp-lobby_");
        $this->lobbyName = $lobbyName;
        $this->hasStarted = false;
        $this->players = [$startingGS];
        $this->isPrivate = false;
        $this->ownerID = $startingGS->getUserData()["userID"];
        $this->gameID = $startingGS->getGameID();
        $this->maxPlayers = $maxPlayers;
        $this->minPlayers = $minPlayers;
        LobbyHandler::registerLobby($this);
    }

    public function setToPrivate(string $pwd): void {
        $this->isPrivate = true;
        $this->privateLobbyPassword = $pwd;
    }

    public function hasEnoughPlayers() {
        return count($this->players) >= $this->minPlayers;
    }

    public function startGame(): void {
        if (!$this->hasEnoughPlayers()) return;

        $this->lobbyInit();
        $this->hasStarted = true;
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "gameHasStarted",
            "data" => [
                "fullLobbyData" => $this->toArray(true),
            ]
        ]);

        $this->gameStartLogic();
    }

    public function lobbyInit() {}
    public function gameStartLogic() {}

    public function join(GameStateMP $gs, ?string $pwd = null): bool {
        if (($this->isPrivate && $pwd != $this->privateLobbyPassword) || (count($this->players) >= $this->maxPlayers)) {
            return false;
        }
        $this->players[] = $gs;
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "playerJoin",
            "data" => [
                "newPlayer" => [
                    "playerID" => $gs->getUserData()["userID"],
                    "playerName" => $gs->getUserData()["userName"],
                    "playerBalance" => $gs->getUserData()["balance"]
                ],
                "fullLobbyData" => $this->toArray(true),
            ]
        ]);
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
            "lobbyMinPlayers" => $this->minPlayers,
            "lobbyCurrentPlayerCount" => count($this->players),
            "lobbyHasStarted" => $this->hasStarted
        ];
        if ($isJoined) {
            $response["ownerID"] = $this->ownerID;
            $response["lobbyPassword"] = $this->isPrivate?$this->privateLobbyPassword:"";
            $playerData = [];
            foreach ($this->players as $p) {
                $reducedPlayer = $p->getUserData();
                unset($reducedPlayer["userPassword"]);
                $reducedPlayer["connected"] = $p->isConnected();
                $playerData[] = $reducedPlayer;
            }
            $response["players"] = $playerData;
            $response["gameSpecificData"] = $this->getGameSpecificData();
        }
        return $response;
    }

    public function getID(): string { return $this->id; }
    public function getGameID(): int { return $this->gameID; }
    public function getOwnerID(): int { return $this->ownerID; }
    public function hasStarted(): bool { return $this->hasStarted; }
    public function getGameSpecificData(): array { return []; }
}
