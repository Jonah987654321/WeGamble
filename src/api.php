<?php

use OmniRoute\Router;

Router::registerPrefix("/api");

Router::add("/roulette", function() {
    header("Content-Type: application/json; charset=UTF-8");
    $body = json_decode(file_get_contents('php://input'), true);

    $user = validateToken($body["auth"]);

    if (!isset($body["auth"]) || !$user) {
        http_response_code(401);
        return print(json_encode(["Error" => "Invalid or no token provided"]));
    }

    if (!isset($body["bids"])) {
        http_response_code(400);
        return print(json_encode(["Error" => "Bad Request"]));
    }

    if (count(array_keys($body["bids"])) == 0) {
        http_response_code(204);
        return print(json_encode(["Status" => "No bids placed"]));
    }

    $totalAmount = 0;
    foreach (array_keys($body["bids"]) as $bid) {
        $bidAmount = intval($body["bids"][$bid]);
        if ($bidAmount < 0) {
            http_response_code(406);
            return print(json_encode(["Error" => "Bids over balance"]));
        }
        $totalAmount += $bidAmount;
    }

    if ($totalAmount > $user["balance"]) {
        http_response_code(406);
        return print(json_encode(["Error" => "Bids over balance"]));
    }

    $result = rand(0, 36);

    $winningConditions = [
        "0" => [0],
        "1" => [1],
        "2" => [2],
        "3" => [3],
        "4" => [4],
        "5" => [5],
        "6" => [6],
        "7" => [7],
        "8" => [8],
        "9" => [9],
        "10" => [10],
        "11" => [11],
        "12" => [12],
        "13" => [13],
        "14" => [14],
        "15" => [15],
        "16" => [16],
        "17" => [17],
        "18" => [18],
        "19" => [19],
        "20" => [20],
        "21" => [21],
        "22" => [22],
        "23" => [23],
        "24" => [24],
        "25" => [25],
        "26" => [26],
        "27" => [27],
        "28" => [28],
        "29" => [29],
        "30" => [30],
        "31" => [31],
        "32" => [32],
        "33" => [33],
        "34" => [34],
        "35" => [35],
        "36" => [36],
        "r1" => [3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36],
        "r2" => [2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35],
        "r3" => [1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34],
        "t1" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        "t2" => [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24],
        "t3" => [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
        "h1" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
        "h2" => [19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
        "ev" => [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36],
        "ue" => [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35],
        "black" => [2, 4, 6, 8, 10, 11, 13, 15, 17, 20, 22, 24, 26, 28, 29, 31, 33, 35],
        "red" => [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36]
    ];

    $winningMultipliers = [
        "0" => 35,
        "1" => 35,
        "2" => 35,
        "3" => 35,
        "4" => 35,
        "5" => 35,
        "6" => 35,
        "7" => 35,
        "8" => 35,
        "9" => 35,
        "10" => 35,
        "11" => 35,
        "12" => 35,
        "13" => 35,
        "14" => 35,
        "15" => 35,
        "16" => 35,
        "17" => 35,
        "18" => 35,
        "19" => 35,
        "20" => 35,
        "21" => 35,
        "22" => 35,
        "23" => 35,
        "24" => 35,
        "25" => 35,
        "26" => 35,
        "27" => 35,
        "28" => 35,
        "29" => 35,
        "30" => 35,
        "31" => 35,
        "32" => 35,
        "33" => 35,
        "34" => 35,
        "35" => 35,
        "36" => 35,
    
        "r1" => 2,
        "r2" => 2,
        "r3" => 2,
    
        "t1" => 2,
        "t2" => 2,
        "t3" => 2,
    
        "h1" => 1,
        "h2" => 1,
    
        "ev" => 1,
        "ue" => 1,
    
        "black" => 1,
        "red" => 1
    ];
    

    $winningBids = [];
    $losingBids = [];
    $totalWinLoss = 0;
    foreach (array_keys($body["bids"]) as $bid) {
        if (in_array($result, $winningConditions[$bid])) {
            $winningBids[] = $bid;
            $totalWinLoss += intval($body["bids"][$bid])*$winningMultipliers[$bid];
        } else {
            $losingBids[] = $bid;
            $totalWinLoss -= intval($body["bids"][$bid]);
        }
    }

    $newBalance = $user["balance"]+$totalWinLoss;
    updateBalance($user["userID"], $newBalance);
    
    http_response_code(200);
    return print(json_encode(["number" => $result, "winningBids" => $winningBids, "losingBids" => $losingBids, "totalWinLoss" => $totalWinLoss, "newBalance" => $newBalance]));
}, ["POST"]);

?>
