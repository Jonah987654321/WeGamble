<?php

require "modules/dbController.php";
require 'vendor/autoload.php';
require_once "modules/config/config.php";

use OmniRoute\Router;
use OmniRoute\Extensions\OmniLogin;
use OmniRoute\Extensions\Tasks;

function isWebSocketRunning() {
    $output = [];
    if (stripos(PHP_OS, 'WIN') === 0) {
        // Windows: use tasklist
        exec("tasklist /FI \"IMAGENAME eq php.exe\" /FI \"WINDOWTITLE eq webSocketServer.php\"", $output);
        foreach ($output as $line) {
            if (strpos($line, 'php.exe') !== false && strpos($line, 'webSocketServer.php') !== false) {
                return true;
            }
        }
    } else {
        // Linux/Unix: use ps aux & grep
        exec("ps aux | grep 'webSocketServer.php' | grep -v grep", $output);
        return count($output) > 0;
    }
    return false;
}

function restartWebSocket() {
    exec("ps aux | grep 'webSocketServer.php' | grep -v grep", $output);
    if (count($output) > 0) {
        preg_match("/\d+/", $output[0], $matches);
        $pid = $matches[0];
        exec("kill -9 $pid");
    }
    
    sleep(1);
    exec("php " . __DIR__ . "/webSocketServer.php > /dev/null 2>&1 &");
}

if(!isWebSocketRunning() && MODE != "DEV") {
    exec("php " . __DIR__ . "/webSocketServer.php > /dev/null 2>&1 &");
}

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

Router::add("/profile/<:id:>", function($id) {
    $user = getUser($id);

    if (!$user) {
        return redirect("/leaderboard");
    }

    $data = getUserStats($id);

    require_once "templates/profile.php";
}, ext: [LOGIN_REQUIRED, Tasks::runTask("updateBalance")]);

Router::registerSubRouter("games.php");

Router::run();

?>