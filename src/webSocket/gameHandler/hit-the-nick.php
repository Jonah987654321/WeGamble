<?php

namespace GameHandler;

use GameState;
require_once "gameState.php";

define("ERR_G3_BID_MISSING", 11);
define("ERR_G3_BIDS_OVER_BALANCE", 12);
define("ERR_G3_NO_SELECTION", 13);

class HitTheNick extends GameState {
    public function __construct() {
        parent::__construct(GID_HITTHENICK);
    }

    public function handleData(array $data) {
        parent::updateUserData();

        if ($this->userData == false) {
            return [
                'type' => 'error',
                "code" => ERR_API_TOKEN_INVALID,
                'message' => 'Invalid or expired API token',
            ];
        }

        if (!isset($data["betAmount"]) || intval($data["betAmount"] <= 0)) {
            return [
                'type' => 'error',
                "code" => ERR_G3_BID_MISSING,
                'message' => 'Invalid or missing bid amount',
            ];
        }

        if (intval($data["betAmount"]) > $this->userData["balance"]) {
            return [
                'type' => 'error',
                "code" => ERR_G3_BID_MISSING,
                'message' => 'Bids over balance',
            ];
        }

        $betAmount = intval($data["betAmount"]);

        if (!isset($data["selection"]) || intval($data["selection"]) > 9 || intval($data["selection"]) < 1) {
            return [
                'type' => 'error',
                "code" => ERR_G3_NO_SELECTION,
                'message' => 'Missing or invalid selection',
            ];
        }

        $sel = intval($data["selection"]);

        $out = random_int(1, 9);

        if ($sel == $out) {
            $newBalance = $this->userData["balance"]+$betAmount*8;
            updateBalance($this->userData["userID"], $newBalance);
            updateStats($this->userData["userID"], 3, $betAmount*8);

            return [
                "type" => "success",
                "event" => "hitTheNick-runGame",
                "out" => $out,
                "result" => "win",
                "newBalance" => $newBalance
            ];
        } else {
            $newBalance = $this->userData["balance"]-$betAmount;
            updateBalance($this->userData["userID"], $newBalance);
            updateStats($this->userData["userID"], 3, -$betAmount);

            return [
                "type" => "success",
                "event" => "hitTheNick-runGame",
                "out" => $out,
                "result" => "loss",
                "newBalance" => $newBalance
            ];
        }
    }
}

?>