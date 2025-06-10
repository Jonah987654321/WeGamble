<?php

namespace GameHandler;

use GameState;
require_once "gameState.php";

define("ERR_G2_BID_MISSING", 7);
define("ERR_G2_BIDS_OVER_BALANCE", 8);
define("ERR_G2_MISSING_ACTION", 9);
define("ERR_G2_INVALID_ACTION", 10);

class Blackjack extends GameState {
    private array $cardValues = [
        // Numbers 2-10
        "2C" => 2, "2D" => 2, "2H" => 2, "2S" => 2,
        "3C" => 3, "3D" => 3, "3H" => 3, "3S" => 3,
        "4C" => 4, "4D" => 4, "4H" => 4, "4S" => 4,
        "5C" => 5, "5D" => 5, "5H" => 5, "5S" => 5,
        "6C" => 6, "6D" => 6, "6H" => 6, "6S" => 6,
        "7C" => 7, "7D" => 7, "7H" => 7, "7S" => 7,
        "8C" => 8, "8D" => 8, "8H" => 8, "8S" => 8,
        "9C" => 9, "9D" => 9, "9H" => 9, "9S" => 9,
        "10C" => 10, "10D" => 10, "10H" => 10, "10S" => 10,
        
        // Face cards (all with value 10)
        "JC" => 10, "JD" => 10, "JH" => 10, "JS" => 10,
        "QC" => 10, "QD" => 10, "QH" => 10, "QS" => 10,
        "KC" => 10, "KD" => 10, "KH" => 10, "KS" => 10,
        
        // Aces (all with value 11)
        "AC" => 11, "AD" => 11, "AH" => 11, "AS" => 11
    ];
    private int $betAmount;
    private array $userCards;
    private array $dealerCards;
    private array $availableCards;
    private array $acceptableNext;
    private array $queue;
    private bool $secondDealerCardShown;

    public function __construct() {
        parent::__construct(GID_BLACKJACK);
        $this->cleanSetup();
    }

    private function cleanSetup() {
        $this->userCards = [];
        $this->dealerCards = [];
        $this->availableCards = array_keys($this->cardValues);
        $this->acceptableNext = ["initGame"];
        $this->queue = [];
        $this->secondDealerCardShown = true;
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

        if(!isset($data["type"])) {
            return [
                'type' => 'error',
                "code" => ERR_G2_MISSING_ACTION,
                'message' => 'Missing type of action',
            ];
        }


        if(!in_array($data["type"], $this->acceptableNext)) {
            return [
                'type' => 'error',
                "code" => ERR_G2_INVALID_ACTION,
                'message' => 'The action type provided is not valid for the current game state',
            ];
        }

        if ($data["type"] == "initGame") {
            if (!isset($data["betAmount"]) || intval($data["betAmount"] <= 0)) {
                return [
                    'type' => 'error',
                    "code" => ERR_G2_BID_MISSING,
                    'message' => 'Invalid or missing bid amount',
                ];
            }

            if (intval($data["betAmount"]) > $this->userData["balance"]) {
                return [
                    'type' => 'error',
                    "code" => ERR_G2_BIDS_OVER_BALANCE,
                    'message' => 'Bids over balance',
                ];
            }

            $this->betAmount = intval($data["betAmount"]);

            $newCards = [];

            $c = $this->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            $c = $this->drawCard();
            $newCards[] = ["type"=>2, "card"=>$c];
            $this->dealerCards[] = $c;

            $c = $this->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            $c = $this->drawCard();
            $newCards[] = ["type"=>2, "card"=>$c];
            $this->dealerCards[] = $c;

            //Check for blackjack
            if ($this->calcCardValues($this->userCards) == 21 && $this->calcCardValues($this->dealerCards) == 21) {
                return $this->draw("initGame", $newCards);
            } else if ($this->calcCardValues($this->userCards) == 21 && $this->calcCardValues($this->dealerCards) != 21) {
                return $this->win("initGame", "Blackjack!", $this->getUserData()["balance"]+round($this->betAmount*(3/2)), $newCards);
            } else if ($this->calcCardValues($this->userCards) != 21 && $this->calcCardValues($this->dealerCards) == 21) {
                return $this->loose("initGame", "Dealer: Blackjack!", $newCards);
            } else {
                $this->acceptableNext = ["stand", "hit", "surrender"];
                if ($this->cardValues[$this->userCards[0]] == $this->cardValues[$this->userCards[1]]) {
                    $this->acceptableNext[] = "split";
                }
                if ($this->betAmount*2 <= $this->userData["balance"]) {
                    $this->acceptableNext[] = "doubleDown";
                }
                $this->secondDealerCardShown = false;
                $this->queue[] = array_pop($newCards);
                return [
                    "type" => "success",
                    "event" => "initGame",
                    "gameData" => [
                        "gameRunning" => true,
                        "endStatus" => ""
                    ],
                    "gameUpdates" => [
                        "newCards" => $newCards,
                        "possibleNext" => $this->acceptableNext
                    ]
                ];
            }
        }

        if ($data["type"] == "surrender") {
            return $this->loose("surrender", "Du hast aufgegeben", []);
        }

        if ($data["type"] == "hit") {
            $newCards = [];

            $c = $this->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            if ($this->calcCardValues($this->userCards) > 21) {
                return $this->loose("hit", "Bust!", $newCards);
            } else if ($this->calcCardValues($this->userCards) == 21) {
                $proceedToStand = true;
            } else {
                $this->acceptableNext = ["hit", "stand"];
                return [
                    "type" => "success",
                    "event" => "hit",
                    "gameData" => [
                        "gameRunning" => true,
                        "endStatus" => ""
                    ],
                    "gameUpdates" => [
                        "newCards" => $newCards,
                        "possibleNext" => ["hit", "stand"]
                    ]
                ];
            }
        }

        if ($data["type"] == "doubleDown") {
            $this->betAmount *= 2;
            $newCards = [];

            $c = $this->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            if ($this->calcCardValues($this->userCards) > 21) {
                return $this->loose("hit", "Bust!", $newCards);
            }

            $proceedToStand = true;
        }

        if ($data["type"] == "stand" || (isset($proceedToStand) && $proceedToStand)) {
            $newCards = (isset($proceedToStand) && $proceedToStand)?$newCards:[];
            $newCards = array_merge($newCards, $this->queue);
            $this->secondDealerCardShown = true;

            while ($this->calcCardValues($this->dealerCards) <= 16) {
                $c = $this->drawCard();
                $newCards[] = ["type"=>2, "card"=>$c];
                $this->dealerCards[] = $c;
            }

            if ($this->calcCardValues($this->dealerCards) > 21) {
                return $this->win("stand", "Dealer: Bust!", $this->getUserData()["balance"]+$this->betAmount, $newCards);
            }

            if ($this->calcCardValues($this->dealerCards) > $this->calcCardValues($this->userCards)) {
                return $this->loose("stand", "Dealer gewinnt", $newCards);
            } else if ($this->calcCardValues($this->dealerCards) < $this->calcCardValues($this->userCards)) {
                return $this->win("stand", "Du hast gewonnen!", $this->getUserData()["balance"]+$this->betAmount, $newCards);
            } else {
                return $this->draw("stand", $newCards);
            }
        }
    }

    public function cacheOnDc() {
        return !in_array("initGame", $this->acceptableNext);
    }

    public function onCache() {
        parent::updateUserData();
        updateBalance($this->userData["userID"], $this->userData["balance"]-$this->betAmount);
    }

    public function onCacheRestore() {
        parent::updateUserData();
        $nB = $this->userData["balance"]+$this->betAmount;
        updateBalance($this->userData["userID"], $nB);
        $dealerDisplayCards = $this->dealerCards;
        if (!$this->secondDealerCardShown) {
            array_pop($dealerDisplayCards);
        }
        return [
            "betAmount" => $this->betAmount,
            "userCards" => $this->userCards,
            "dealerCards" => $dealerDisplayCards,
            "dealerCardShown" => $this->secondDealerCardShown,
            "possibleNext" => $this->acceptableNext,
            "balance" => $nB
        ];
    }

    private function win(string $event, string $displayText, int $newBalance, array $newCards) {
        updateBalance($this->userData["userID"], $newBalance);
        updateStats($this->userData["userID"], 2, $newBalance-$this->userData["balance"]);
        $this->cleanSetup();
        return [
            "type" => "success",
            "event" => $event,
            "gameData" => [
                "gameRunning" => false,
                "endStatus" => "win"
            ],
            "gameUpdates" => [
                "displayText" => $displayText,
                "newCards" => $newCards,
                "userBalance" => $newBalance
            ]
        ];
    }

    private function loose(string $event, string $displayText, array $newCards) {
        updateBalance($this->userData["userID"], $this->getUserData()["balance"]-$this->betAmount);
        updateStats($this->userData["userID"], 2, -$this->betAmount);
        $this->cleanSetup();
        return [
            "type" => "success",
            "event" => $event,
            "gameData" => [
                "gameRunning" => false,
                "endStatus" => "loss"
            ],
            "gameUpdates" => [
                "displayText" => $displayText,
                "newCards" => $newCards,
                "userBalance" => $this->getUserData()["balance"]-$this->betAmount
            ]
        ];
    }

    private function draw(string $event, array $newCards) {
        updateStats($this->userData["userID"], 2, 0);
        $this->cleanSetup();
        return [
            "type" => "success",
            "event" => $event,
            "gameData" => [
                "gameRunning" => false,
                "endStatus" => "draw"
            ],
            "gameUpdates" => [
                "displayText" => "Stand off - Unentschieden",
                "newCards" => $newCards,
                "userBalance" => $this->getUserData()["balance"]
            ]
        ];
    }

    private function calcCardValues(array $cards) {
        $val = 0;
        foreach ($cards as $c) {
            $val += $this->cardValues[$c];
        }
        return $val;
    }

    private function drawCard() {
        $newCardIndex = rand(0, count($this->availableCards)-1);
        $card = $this->availableCards[$newCardIndex];
        unset($this->availableCards[$newCardIndex]);
        $this->availableCards = array_values($this->availableCards); //Reindex to close gaps
        return $card;
    }
}

?>