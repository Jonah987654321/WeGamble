<?php

require "modules/dbController.php";

require 'vendor/autoload.php';

use OmniRoute\Router;
use OmniRoute\Extensions\OmniLogin;
use OmniRoute\Extensions\Tasks;

Router::loadExtension(EXT_TASKS);
Router::loadExtension(EXT_LOGIN, ["loginRoute" => "/login"]);

Tasks::create("updateBalance", function() {
    $u = OmniLogin::getUser();

    $u["balance"] = getBalance($u["userID"]);

    OmniLogin::updateUser($u);
});

Router::add("/", function() {
    require_once "templates/home.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::add("/login", function() {
    if (OmniLogin::isUserLoggedIn()) {
        return redirect("/");
    }

    require_once "templates/login.php";
});

Router::add("/login", function() {
    if (OmniLogin::isUserLoggedIn()) {
        return redirect("/");
    }

    if (!isset($_POST["username"]) || !isset($_POST["password"])) {
        return redirect("/login");
    }

    $user = loginUser($_POST["username"], $_POST["password"]);

    if (!$user) {
        $_SESSION["invalidLogin"] = true;
        return redirect("/login");
    }

    OmniLogin::loginUserAndRedirect($user);
}, method: ["POST"]);

Router::add("/logout", function() {
    revokeToken(OmniLogin::getUser()["apiToken"]);
    OmniLogin::logoutUser();
    return redirect("/login");
}, ext: [LOGIN_REQUIRED]);

Router::add("/leaderboard", function() {
    $data = getLeaderboard();

    require_once "templates/leaderboard.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::registerSubRouter("games.php");
Router::registerSubRouter("api.php");

Router::run();

?>