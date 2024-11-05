<?php 

require_once "config/config.php";

// Establish a new MySQL connection using the credentials from the .env config
function newSQLConnection() {
    return new mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB, SQL_PORT);
}

// Function to generate a random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
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
?>