<?php
require_once __DIR__."/../multiplayer/Lobby.php";
require_once __DIR__."/../../random/cardDeck.php";

const STATUS_PREFLOP = 0;
const STATUS_FLOP = 1;
const STATUS_TURN = 2;
const STATUS_RIVER = 3;
const STATUS_SHOWDOWN = 4;

class PokerLobby extends Lobby {
    // Settings:
    private int $settingBuyIn = 1000;
    private int $settingSmallBlind = 25;
    private int $settingsPlayerTimeToAct = 25;

    // Round specific attributes:
    private int $status;
    private int $currentlyWaitingForPlayerPos;
    private int $currentSmallBlindPos;
    private int $currentBigBlindPos;
    private CardDeck $cards;
    private array $userCards = [];
    private array $tableCards = [];
    private array $roundPots = [];
    private array $currentRaises = [];
    private array $allIns = [];

    private array $userBalances = [];
    private array $playerIDs;

    private bool $blockedTillRetry = false;

    public function lobbyInit(): void {
        $this->currentSmallBlindPos = 0;
        $this->cards = new CardDeck();

        // Initial buy-ins
        foreach ($this->players as $player) {
            $userBalance = $player->getUserData()["balance"];
            $userID = $player->getUserData()["userID"];
            if ($userBalance >= $this->settingBuyIn) {
                updateBalance($userID, $userBalance - $this->settingBuyIn);
                $this->userBalances[$userID] = $this->settingBuyIn;
            } else {
                $this->userBalances[$userID] = 0;
            }
        }
    }

    public function gameStartLogic(): void {
        $this->newRound();
    }

    private function newRound(): void {
        $this->status = STATUS_PREFLOP;

        // DEALING CARDS:
        $this->userCards = [];
        $playingUsers = [];
        foreach ($this->players as $player)  {
            $userID = $player->getUserData()["userID"];
            if ($player->isConnected() && $this->userBalances[$userID] > 0) {
                $this->userCards[$userID] = [$this->cards->drawCard(), $this->cards->drawCard()];
                $playingUsers[$userID] = true;
            } else {
                // "empty" cards = user is not playing this round
                $this->userCards[$userID] = [];
                $playingUsers[$userID] = false;
            }
        }
        $this->tableCards = [];
        for($i = 0; $i<5; $i++) {
            $this->tableCards[] = $this->cards->drawCard();
        }

        // SEND CARDS TO CLIENTS:
        foreach ($this->players as $player)  {
            if ($player->isConnected()) {
                $player->sendData([
                    "type" => "eventBroadcast",
                    "event" => "poker_newRound",
                    "data" => [
                        "activePlayers" => $playingUsers,
                        "playerCards" => $this->userCards[$player->getUserData()["userID"]]
                    ]
                ]);
            }
        }

        // Find blinds
        $this->playerIDs = array_keys($this->userCards);
        $playerCount = count($this->playerIDs);
        $start = $this->currentSmallBlindPos;
        while (!$this->isUserPlaying($this->playerIDs[$this->currentSmallBlindPos])) {
            $this->currentSmallBlindPos++;
            $this->currentSmallBlindPos = $this->currentSmallBlindPos%$playerCount;
            if ($this->currentSmallBlindPos == $start) {
                $this->broadcastEvent([
                    "type" => "eventBroadcast",
                    "event" => "poker_newRoundCancel",
                    "message" => "Not enough players connected and with balance"
                ]);
                $this->blockedTillRetry = true;
                return;
            }
        }
        $this->currentBigBlindPos = ($this->currentSmallBlindPos+1)%$playerCount;
        while (!$this->isUserPlaying($this->playerIDs[$this->currentBigBlindPos])) {
            $this->currentBigBlindPos++;
            $this->currentBigBlindPos = $this->currentBigBlindPos%$playerCount;
            if ($this->currentBigBlindPos == $this->currentSmallBlindPos) {
                $this->broadcastEvent([
                    "type" => "eventBroadcast",
                    "event" => "poker_newRoundCancel",
                    "message" => "Not enough players connected and with balance"
                ]);
                $this->blockedTillRetry = true;
                return;
            }
        }

        // New "Raise" from small blind
        if ($this->userBalances[$this->playerIDs[$this->currentSmallBlindPos]] < $this->settingSmallBlind) {
            // Blind forces an all-in
            $this->allIns[] = $this->playerIDs[$this->currentSmallBlindPos];
            $this->currentRaises[$this->playerIDs[$this->currentSmallBlindPos]] = $this->userBalances[$this->playerIDs[$this->currentSmallBlindPos]];
            $this->userBalances[$this->playerIDs[$this->currentSmallBlindPos]] = 0;
        } else {
            $this->currentRaises[$this->playerIDs[$this->currentSmallBlindPos]] = $this->settingSmallBlind;
            $this->userBalances[$this->playerIDs[$this->currentSmallBlindPos]] -= $this->settingSmallBlind;
        }

        // New "Raise" from big blind
        if ($this->userBalances[$this->playerIDs[$this->currentBigBlindPos]] < $this->settingSmallBlind*2) {
            $this->allIns[] = $this->playerIDs[$this->currentBigBlindPos];
            $this->currentRaises[$this->playerIDs[$this->currentBigBlindPos]] = $this->userBalances[$this->playerIDs[$this->currentSmallBlindPos]];
            $this->userBalances[$this->playerIDs[$this->currentBigBlindPos]] = 0;
        } else {
            $this->currentRaises[$this->playerIDs[$this->currentBigBlindPos]] = $this->settingSmallBlind*2;
            $this->userBalances[$this->playerIDs[$this->currentBigBlindPos]] -= $this->settingSmallBlind*2;
        }
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "poker_bidsPlaced",
            "pos" =>  [
                "sb" => $this->playerIDs[$this->currentSmallBlindPos],
                "bb" => $this->playerIDs[$this->currentBigBlindPos]
            ],
            "data" => [
                "raises" => $this->currentRaises,
                "allIns" => $this->allIns
            ]
        ]);

        // Check for next player to act
        $this->currentlyWaitingForPlayerPos = ($this->currentBigBlindPos+1)%$playerCount;
        while (!$this->isUserPlaying($this->playerIDs[$this->currentlyWaitingForPlayerPos])) {
            $this->currentlyWaitingForPlayerPos++;
            $this->currentlyWaitingForPlayerPos = $this->currentlyWaitingForPlayerPos%$playerCount;
        }
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "poker_requestAction",
            "data" => [
                "targetPlayer" => $this->playerIDs[$this->currentlyWaitingForPlayerPos],
                "timeToAct" => $this->settingsPlayerTimeToAct
            ]
        ]);
    }

    private function isUserPlaying($userID): bool {
        return count($this->userCards[$userID])==2;
    }

    public function getGameSpecificData(): array {
        return [
            "settings" => [
                "buyIn" => $this->settingBuyIn,
                "smallBlind" => $this->settingSmallBlind,
                "playerActionTime" => $this->settingsPlayerTimeToAct
            ],
            "userBalances" => $this->userBalances
        ];
    }
}

