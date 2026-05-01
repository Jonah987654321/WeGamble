<?php

namespace GameHandler;

require_once __DIR__."/../abstract/gameStateMP.php";
require_once __DIR__."/pokerLobby.php";

use GameStateMP;
use Lobby;

class Poker extends GameStateMP {
    public function __construct() {
        parent::__construct(GID_POKER);
    }

    public function getGameSpecificLobby($name): Lobby {
        return new \PokerLobby($this, $name, 10, 2);
    }

    public function handleGameLogic(array $data): array {
        
    }
}

?>