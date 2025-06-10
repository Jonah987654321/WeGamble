<?php

namespace GameHandler;

use GameState;
require_once "gameState.php";

define("ERR_G4_BID_MISSING", 14);
define("ERR_G4_BIDS_OVER_BALANCE", 15);

class Slots extends GameState {
    private array $freeSpins;

    public function __construct() {
        parent::__construct(GID_SLOTS);
        $this->freeSpins = [];
    }

    public function handleData(array $data) {
        $amountWR = 8;

        parent::updateUserData();

        if ($this->userData == false) {
            return [
                'type' => 'error',
                "code" => ERR_API_TOKEN_INVALID,
                'message' => 'Invalid or expired API token',
            ];
        }

        if (count($this->freeSpins) > 0) {
            $usingFreeSpins = true;
            $betAmount = $this->freeSpins[0]["bidAmount"];
            --$this->freeSpins[0]["count"];
            if ($this->freeSpins[0]["count"] == 0) {
                array_shift($this->freeSpins);
            }
        } else {
            $usingFreeSpins = false;
            if (!isset($data["betAmount"]) || intval($data["betAmount"] <= 0)) {
                return [
                    'type' => 'error',
                    "code" => ERR_G4_BID_MISSING,
                    'message' => 'Invalid or missing bid amount',
                ];
            }

            if (intval($data["betAmount"])*$amountWR > $this->userData["balance"]) {
                return [
                    'type' => 'error',
                    "code" => ERR_G4_BIDS_OVER_BALANCE,
                    'message' => 'Bids over balance',
                ];
            }
    
            $betAmount = intval($data["betAmount"]);
        }

        $res = [];
        for ($i = 0; $i < 9; $i++) {
            $res[] = random_int(1, 10);
        }

        $counts = array_count_values($res);
        if (isset($counts[10]) && floor($counts[10]/2)>0) {
            $this->freeSpins[] = ["bidAmount" => $betAmount, "count" => floor($counts[10]/2)*3];
        }

        $totalWinLoss = $usingFreeSpins?$betAmount*$amountWR:0;

        $winCons = [
            [0,1,2],[3,4,5],[6,7,8], //Horizontals
            [0,3,6],[1,4,7],[2,5,8], //Verticals
            [0,4,8],[2,4,6] //Diagonal
        ];

        foreach ($winCons as $c) {
            $val = $res[$c[0]];
            $win = true;
            foreach ($c as $i) {
                if ($res[$i] != $val) {
                    $win = false;
                    break;
                }
            }

            $totalWinLoss += $win?$betAmount*15:$betAmount*-1;
        }

        $newBalance = $this->userData["balance"]+$totalWinLoss;
        updateBalance($this->userData["userID"], $newBalance);

        updateStats($this->userData["userID"], 4, $totalWinLoss);

        return [
            "type" => "success",
            "event" => "slotsRoll",
            "results" => $res,
            "winLoss" => $totalWinLoss,
            "newBalance" => $newBalance,
            "freeSpins" => array_sum(array_column($this->freeSpins, 'count'))
        ];
    }
}

?>