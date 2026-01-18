<?php

class LobbyHandler {
    private static array $lobbies = [];

    protected function __construct() {}

    public static function registerLobby(Lobby $newLobby): void {
        self::$lobbies[$newLobby->getID()] = $newLobby;
    }

    public static function getLobbyByID(string $lobbyID): ?Lobby {
        return self::$lobbies[$lobbyID] ?? null;
    }

    public static function getLobbyListByGameID(int $gameID): array {
        return array_values(array_filter(
            self::$lobbies,
            fn(Lobby $lobby) => $lobby->checkJoinable($gameID)
        ));
    }
}
