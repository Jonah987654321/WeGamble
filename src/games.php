<?php

use OmniRoute\Extensions\Tasks;
use OmniRoute\Router;

Router::registerPrefix("/game");

Router::add("/roulette", function() {
    require_once "templates/games/roulette.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::add("/blackjack", function() {
    require_once "templates/games/blackjack.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::add("/hit-the-nick", function() {
    require_once "templates/games/hit-the-nick.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::add("/slots", function() {
    require_once "templates/games/slots.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::add("/poker", function() {
    require_once "templates/games/poker.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

?>