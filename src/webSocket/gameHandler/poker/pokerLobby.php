<?php
require_once __DIR__."/../multiplayer/Lobby.php";

const ALL_CARDS = [
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
    private array $availableCards;
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
        $this->availableCards = array_keys(ALL_CARDS);

        // DEALING CARDS:
        $this->userCards = [];
        $playingUsers = [];
        foreach ($this->players as $player)  {
            $userID = $player->getUserData()["userID"];
            if ($player->isConnected() && $this->userBalances[$userID] > 0) {
                $this->userCards[$userID] = [$this->drawCard(), $this->drawCard()];
                $playingUsers[$userID] = true;
            } else {
                // "empty" cards = user is not playing this round
                $this->userCards[$userID] = [];
                $playingUsers[$userID] = false;
            }
        }
        $this->tableCards = [];
        for($i = 0; $i<5; $i++) {
            $this->tableCards[] = $this->drawCard();
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

    private function drawCard() {
        $newCardIndex = rand(0, count($this->availableCards)-1);
        $card = $this->availableCards[$newCardIndex];
        unset($this->availableCards[$newCardIndex]);
        $this->availableCards = array_values($this->availableCards); //Reindex to close gaps
        return $card;
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

