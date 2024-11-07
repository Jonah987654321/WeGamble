<?php 

require_once "config/config.php";

// Establish a new MySQL connection using the credentials from the .env config
function newSQLConnection() {
    return new mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB, SQL_PORT);
}

function revokeToken($token) {
    $conn = newSQLConnection();
    $stmt = $conn->prepare("DELETE FROM apiKeys WHERE apiKey=?");
    $stmt->execute([$token]);
    $conn->close();
}

function validateToken($token) {
    $conn = newSQLConnection();
    $stmt = $conn->prepare("SELECT userID, expirationDate FROM apiKeys WHERE apiKey=?");
    $stmt->execute([$token]);
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        return false;
    } 

    $res = $res->fetch_assoc();
    if (date('Y-m-d H:i:s') > $res["expirationDate"]) {
        revokeToken($token);
        return false;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE userID=?");
    $stmt->execute([$res["userID"]]);
    $res = $stmt->get_result()->fetch_assoc();

    return $res;
}

// Function to generate a random token
function generateToken($length = 32) {
    $token = bin2hex(random_bytes($length));
    while (validateToken($token) != false) {
        $token = bin2hex(random_bytes($length));
    }
    return $token;
}

// Function to generate a token expiration time (e.g., 1 hour from now)
function generateExpirationTime() {
    return date('Y-m-d H:i:s', strtotime('+2 days'));
}

function generateAPIToken($userID) {
    $conn = newSQLConnection();

    $token = generateToken();

    $stmt = $conn->prepare("INSERT INTO apiKeys (userID, apiKey, expirationDate) VALUES (?, ?, ?)");
    $stmt->execute([$userID, $token, generateExpirationTime()]);

    $conn->close();

    return $token;
}

function loginUser($uname, $pwd) {
    $conn = newSQLConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE userName=? AND userPassword=?");
    $stmt->execute([$uname, md5($pwd)]);
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        return false;
    }

    $user = $res->fetch_assoc();

    $conn->close();

    $user["apiToken"] = generateAPIToken($user["userID"]);
    return $user;
}

function getBalance($userID) {
    $conn = newSQLConnection();

    $stmt = $conn->prepare("SELECT balance FROM users WHERE userID=?");
    $stmt->execute([$userID]);

    $balance = $stmt->get_result()->fetch_row()[0];

    $conn->close();

    return $balance;
}

function updateBalance($userID, $nB) {
    $conn = newSQLConnection();
    $stmt = $conn->prepare("UPDATE users SET balance=? WHERE userID=?");
    $stmt->execute([$nB, $userID]);
    $conn->close();
}

function getLeaderboard() {
    $conn = newSQLConnection();
    $stmt = $conn->prepare("SELECT userID, userName, balance FROM users ORDER BY balance DESC");
    $stmt->execute();

    $res = $stmt->get_result()->fetch_all();
    $conn->close();

    return $res;
}
?>