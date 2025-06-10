<?php

class GameState {
    protected ?string $apiKey;
    protected ?string $openedOn;
    protected array $userData;
    protected string $id;
    protected int $gameType;

    public function __construct(int $gameID) {
        $this->id = uniqid("gs_");
        $this->openedOn = date('Y-m-d H:i:s');
        $this->gameType = $gameID;
    }

    public function checkIn(string $apiKey) {
        $userData = validateToken($apiKey);
        if (!$userData) {
            return false;
        }
        $this->userData = $userData;
        $this->apiKey = $apiKey;
        return true;
    }

    protected function updateUserData() {
        $this->userData = validateToken($this->apiKey);
    }

    public function getGameID() {
        return $this->gameType;
    }

    public function handleData(array $data) {}

    public function endSession() {}

    public function getOpenedOn() {
        return $this->openedOn;
    }

    public function getUserData() {
        return $this->userData;
    }

    public function getID() {
        return $this->id;
    }

    public function cacheOnDc() {
        return false;
    }

    public function onCache() {}

    public function onCacheRestore() {}
}

?>