<?php

use OmniRoute\Extensions\Tasks;
use OmniRoute\Router;

Router::registerPrefix("/game");

Router::add("/roulette", function() {
    require_once "templates/games/roulette.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

?>