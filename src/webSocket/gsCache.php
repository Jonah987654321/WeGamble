<?php

class GsCache {
    private array $cache;

    public function __construct() {
        $this->cache = array();
    }

    public function store(GameState $gameState) {
        $userID = $gameState->getUserData()["userID"];
        if (array_key_exists($userID, $this->cache)) {
            // User is already registered in cache
            $this->cache[$userID][$gameState->getGameID()] = $gameState;
        } else {
            // Register user in cache & store gameState
            $this->cache[$userID] = [$gameState->getGameID() => $gameState];
        }
    }

    public function load(int $userID, int $gameID) {
        if (!$this->isCached($userID, $gameID)) {
            return;
        }

        $gs = $this->cache[$userID][$gameID];
        unset($this->cache[$userID][$gameID]);
        return $gs;
    }

    public function isCached(int $userID, int $gameID) {
        return array_key_exists($userID, $this->cache) && array_key_exists($gameID, $this->cache[$userID]);
    }
}

?>