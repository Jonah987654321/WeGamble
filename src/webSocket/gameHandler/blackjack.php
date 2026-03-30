<?php

namespace GameHandler;

use CardDeck;
use GameState;
require_once __DIR__."/abstract/gameState.php";
require_once __DIR__."/../random/cardDeck.php";

class Blackjack extends GameState {
    private int $betAmount;
    private array $userCards;
    private array $dealerCards;
    private array $acceptableNext;
    private array $queue;
    private bool $secondDealerCardShown;
    private CardDeck $cards;

    public function __construct() {
        parent::__construct(GID_BLACKJACK);
        $this->cards = new CardDeck();
        $this->cleanSetup();
    }

    private function cleanSetup() {
        $this->userCards = [];
        $this->dealerCards = [];
        $this->cards->resetCards();
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
                "code" => ERR_MISSING_ACTION,
                'message' => 'Missing type of action',
            ];
        }


        if(!in_array($data["type"], $this->acceptableNext)) {
            return [
                'type' => 'error',
                "code" => ERR_INVALID_ACTION,
                'message' => 'The action type provided is not valid for the current game state',
            ];
        }

        if ($data["type"] == "initGame") {
            if (!isset($data["betAmount"]) || intval($data["betAmount"] <= 0)) {
                return [
                    'type' => 'error',
                    "code" => ERR_MISSING_BIDS,
                    'message' => 'Invalid or missing bid amount',
                ];
            }

            if (intval($data["betAmount"]) > $this->userData["balance"]) {
                return [
                    'type' => 'error',
                    "code" => ERR_BIDS_OVER_BALANCE,
                    'message' => 'Bids over balance',
                ];
            }

            $this->betAmount = intval($data["betAmount"]);

            $newCards = [];

            $c = $this->cards->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            $c = $this->cards->drawCard();
            $newCards[] = ["type"=>2, "card"=>$c];
            $this->dealerCards[] = $c;

            $c = $this->cards->drawCard();
            $newCards[] = ["type"=>1, "card"=>$c];
            $this->userCards[] = $c;

            $c = $this->cards->drawCard();
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
                if (CardDeck::getCardValue($this->userCards[0]) == CardDeck::getCardValue($this->userCards[1])) {
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

            $c = $this->cards->drawCard();
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

            $c = $this->cards->drawCard();
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
                $c = $this->cards->drawCard();
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
        $aces = 0;
        foreach ($cards as $c) {
            $val += CardDeck::getCardValue($c);
            if (CardDeck::getCardValue($c) == 11) {
                $aces++;
            }
        }

        while ($val > 21 && $aces > 0) {
            $val -= 10;
            --$aces;
        }
        return $val;
    }
}

?>