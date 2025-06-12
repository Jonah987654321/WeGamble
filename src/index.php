<?php

require "modules/dbController.php";
require 'vendor/autoload.php';

use OmniRoute\Router;
use OmniRoute\Extensions\OmniLogin;
use OmniRoute\Extensions\Tasks;
use OmniRoute\utils\Dotenv as config;

config::loadFile(__DIR__."/../.env");

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

Router::add("/admin", function() {
    if (OmniLogin::getUser()["userID"] != 1) {
        return redirect("/");
    }

    echo '<form action="/admin" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name"><br>
        <label for="pwd">Passwort:</label>
        <input type="text" id="pwd" name="pwd"><br>
        <button type="submit">Erstellen</button>
    </form>';
}, ext: [LOGIN_REQUIRED]);

Router::add("/admin", function() {
    if (OmniLogin::getUser()["userID"] != 1) {
        return redirect("/");
    }

    $conn = newSQLConnection();
    $stmt = $conn->prepare("INSERT INTO users (userName, userPassword, balance) VALUES (?, ?, 100000)");
    $stmt->execute([$_POST["name"], md5($_POST["pwd"])]);
    $conn->close();

    return redirect("/admin");
}, method: ["POST"], ext: [LOGIN_REQUIRED]);

Router::registerSubRouter("games.php");

Router::run();

?>