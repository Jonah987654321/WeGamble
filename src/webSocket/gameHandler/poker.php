<?php

namespace GameHandler;

require_once "abstract/gameStateMP.php";
use GameStateMP;

class Poker extends GameStateMP {
    public function __construct() {
        parent::__construct(GID_POKER);
    }
}

?>