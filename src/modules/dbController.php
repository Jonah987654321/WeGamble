<?php 

use OmniRoute\utils\Dotenv as c;
require_once __DIR__."/../vendor/autoload.php";

// Establish a new MySQL connection using the credentials from the .env config
function newSQLConnection() {
    return new mysqli(c::get("SQL_HOST"), c::get("SQL_USER"), c::get("SQL_PASSWORD"), c::get("SQL_DB"), c::get("SQL_PORT"));
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

    getUserStats($user["userID"]); //Creating stats if not existing
    $stmt = $conn->prepare("UPDATE stats SET lastLogin=? WHERE userID=?");
    $stmt->execute([date('Y-m-d H:i:s'), $user["userID"]]);

    $stmt = $conn->prepare("DELETE FROM apiKeys WHERE userID=?");
    $stmt->execute([$user["userID"]]);

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

function getUser($userID) {
    $conn = newSQLConnection();

    $stmt = $conn->prepare("SELECT * FROM users  WHERE userID=?");
    $stmt->execute([$userID]);

    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        return false;
    }

    $conn->close();
    return $res->fetch_assoc();
}

function getUserStats($userID) {
    $conn = newSQLConnection();

    $stmt = $conn->prepare("SELECT * FROM stats WHERE userID=?");
    $stmt->execute([$userID]);
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO `stats` 
            (`userID`, 
            `allTimeHigh`, 
            `longestWinStreak`, 
            `currentWinStreak`, 
            `longestLooseStreak`, 
            `currentLooseStreak`, 
            `lastLogin`,
            `highestWin`,
            `highestLoss`,
            `playTime`,
            `totalWins`,
            `totalWinSum`,
            `totalLosses`,
            `totalLossSum`)
            VALUES (?,100000,0,0,0,0,'2024-11-08 19:11:30',0,0,0,0,0,0,0)");
        $stmt->execute([$userID]);
        $stmt = $conn->prepare("SELECT * FROM stats WHERE userID=?");
        $stmt->execute([$userID]);
        $res = $stmt->get_result();
    }
    $stats = $res->fetch_assoc();

    $stmt = $conn->prepare("SELECT gameID, timestamp, winLoss FROM history WHERE userID=? ORDER BY timestamp DESC");
    $stmt->execute([$userID]);
    $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stats["history"] = $res;

    $stmt = $conn->prepare("SELECT gameID, playTime, wins, winSum, looses, looseSum FROM gameSpecificStats WHERE userID=?");
    $stmt->execute([$userID]);
    $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $gameStats = [];
    $missingStats = [1, 2, 3, 4];
    foreach ($res as $game) {
        $gameStats[$game["gameID"]] = $game;
        if (($key = array_search($game["gameID"], $missingStats)) !== false) {
            unset($missingStats[$key]);
        }
    }

    foreach ($missingStats as $ID) {
        $stmt = $conn->prepare("INSERT INTO `gameSpecificStats`
            (`userID`,
            `gameID`,
            `playTime`,
            `wins`,
            `winSum`,
            `looses`,
            `looseSum`)
            VALUES (?,?,0,0,0,0,0)");
        $stmt->execute([$userID, $ID]);
    }
    $stats["gameStats"] = $gameStats;

    $conn->close();
    return $stats;
}

function updateStats($userID, $gameID, $winLoss) {
    $stats = getUserStats($userID);
    $balance = getBalance($userID);
    $stats["allTimeHigh"] = ($balance > $stats["allTimeHigh"])?$balance:$stats["allTimeHigh"];
    if ($winLoss > 0) {
        $stats["currentWinStreak"]++;
        $stats["longestWinStreak"] = ($stats["currentWinStreak"] > $stats["longestWinStreak"])?$stats["currentWinStreak"]:$stats["longestWinStreak"];
        $stats["currentLooseStreak"] = 0;
        $stats["highestWin"] = ($winLoss > $stats["highestWin"])?$winLoss:$stats["highestWin"];
        $stats["totalWins"]++;
        $stats["totalWinSum"] += $winLoss;

        $stats["gameStats"][$gameID]["wins"]++;
        $stats["gameStats"][$gameID]["winSum"] += $winLoss;
    } elseif ($winLoss < 0) {
        $stats["currentLooseStreak"]++;
        $stats["longestLooseStreak"] = ($stats["currentLooseStreak"] > $stats["longestLooseStreak"])?$stats["currentLooseStreak"]:$stats["longestLooseStreak"];
        $stats["currentWinStreak"] = 0;
        $stats["highestLoss"] = ($winLoss < $stats["highestLoss"])?$winLoss:$stats["highestLoss"];
        $stats["totalLosses"]++;
        $stats["totalLossSum"] += $winLoss;

        $stats["gameStats"][$gameID]["looses"]++;
        $stats["gameStats"][$gameID]["looseSum"] += $winLoss;
    }

    $conn = newSQLConnection();

    $stmt = $conn->prepare("UPDATE stats SET allTimeHigh=?, longestWinStreak=?, currentWinStreak=?, longestLooseStreak=?, currentLooseStreak=?, highestWin=?, highestLoss=?, totalWins=?, totalWinSum=?, totalLosses=?, totalLossSum=? WHERE userID=?");
    $stmt->execute([$stats["allTimeHigh"], $stats["longestWinStreak"], $stats["currentWinStreak"], $stats["longestLooseStreak"], $stats["currentLooseStreak"], $stats["highestWin"], $stats["highestLoss"], $stats["totalWins"], $stats["totalWinSum"], $stats["totalLosses"], $stats["totalLossSum"], $userID]);

    $stmt = $conn->prepare("INSERT INTO history VALUES (?, ?, ?, ?)");
    $stmt->execute([$userID, date('Y-m-d H:i:s'), $gameID, $winLoss]);

    $stmt = $conn->prepare("UPDATE gameSpecificStats SET wins=?, winSum=?, looses=?, looseSum=? WHERE userID=? AND gameID=?");
    $stmt->execute([$stats["gameStats"][$gameID]["wins"], $stats["gameStats"][$gameID]["winSum"], $stats["gameStats"][$gameID]["looses"], $stats["gameStats"][$gameID]["looseSum"], $userID, $gameID]);

    $conn->close();
}

function startTime($gtID, $userID, $gameID, $startTime) {
    $conn = newSQLConnection();

    $stmt = $conn->prepare("INSERT INTO gameTimeSessions VALUES (?, ?, ?, ?)");
    $stmt->execute([$gtID, $userID, $gameID, $startTime]);

    $conn->close();
}

function endTime($gtID, $lastInput) {
    $conn = newSQLConnection();

    $stmt = $conn->prepare("SELECT * FROM gameTimeSessions WHERE gtID=?");
    $stmt->execute([$gtID]);

    $res = $stmt->get_result()->fetch_assoc();

    $start = new DateTime($res["startTime"]);
    $end = new DateTime($lastInput);
    $diffSec = abs($end->getTimestamp() - $start->getTimestamp());

    $stmt = $conn->prepare("UPDATE stats SET playTime=playTime+? WHERE userID=?");
    $stmt->execute([$diffSec, $res["userID"]]);

    $stmt = $conn->prepare("UPDATE gameSpecificStats SET playTime=playTime+? WHERE userID=? AND gameID=?");
    $stmt->execute([$diffSec, $res["userID"], $res["gameID"]]);

    $stmt = $conn->prepare("DELETE FROM gameTimeSessions WHERE gtID=?");
    $stmt->execute([$gtID]);

    $conn->close();
}
?>