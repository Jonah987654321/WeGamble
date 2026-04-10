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

    private PokerTable $table;

    private bool $isInit = false;

    // Round specific attributes:
    private int $status;
    private int $currentlyWaitingForPlayerPos;
    private int $currentSmallBlindPos;
    private CardDeck $cards;
    private array $tableCards = [];
    private array $roundPots = [];
    private array $currentRaises = [];

    private bool $blockedTillRetry = false;

    public function lobbyInit(): void {
        $this->currentSmallBlindPos = 0;
        $this->cards = new CardDeck();
        $this->table = new PokerTable();

        // Setup playerdata & do buy-ins
        foreach ($this->players as $player) {
            $userBalance = $player->getUserData()["balance"];
            $userID = $player->getUserData()["userID"];
            if ($userBalance >= $this->settingBuyIn) {
                updateBalance($userID, $userBalance - $this->settingBuyIn);
                $initBalance = $this->settingBuyIn;
            } else {
                $initBalance = 0;
            }

            $playerObj = new PokerPlayer($userID, $initBalance);
            $this->table->addPlayer($playerObj);
        }

        $this->isInit = true;
    }

    public function gameStartLogic(): void {
        $this->newRound();
    }

    private function newRound(): void {
        $this->status = STATUS_PREFLOP;

        // DEALING CARDS:
        $playingUsers = [];
        foreach ($this->players as $player)  {
            $userID = $player->getUserData()["userID"];
            $userObj = $this->table->getPlayerByID($userID);

            if ($player->isConnected() && $userObj->balance > 0) {
                $userObj->cards = [$this->cards->drawCard(), $this->cards->drawCard()];
                $userObj->isPlaying = true;
                $playingUsers[$userID] = true;
            } else {
                $userObj->isPlaying = false;
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
                        "playerCards" => $this->table->getPlayerByID($player->getUserData()["userID"])->cards
                    ]
                ]);
            }
        }

        // Find blinds
        $smallBlind = $this->findNextPlayingUser($this->currentSmallBlindPos);
        if ($smallBlind == -1) {
            $this->setBlockerForNotEnoughPlayers();
            return;
        }
        $this->currentSmallBlindPos = $smallBlind;

        $bigBlind = $this->findNextPlayingUser($this->currentSmallBlindPos+1);
        if ($bigBlind== -1) {
            $this->setBlockerForNotEnoughPlayers();
            return;
        }

        $this->createBet($this->table->getPlayerByIndex($this->currentSmallBlindPos), $this->settingSmallBlind, "Small Blind");
        $this->createBet($this->table->getPlayerByIndex($bigBlind), $this->settingSmallBlind*2, "Big Blind");

        // Check for next player to act
        $this->currentlyWaitingForPlayerPos = $this->findNextPlayingUser($bigBlind +1);
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "poker_requestAction",
            "data" => [
                "targetPlayer" => $this->table->getPlayerByIndex($this->currentlyWaitingForPlayerPos)->userID,
                "timeToAct" => $this->settingsPlayerTimeToAct
            ]
        ]);
    }

    private function createBet(PokerPlayer $user,  int $betAmount, string $betName): void {
        if ($user->balance <= $betAmount) {
            // Bet amount forces an all-in
            $user->isAllIn = true;
            $betAmount = $user->balance;
        }

        $this->currentRaises[$user->userID] = $betAmount;
        $user->balance -= $betAmount;

        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "poker_betCreated",
            "data" => [
                "userID" => $user->userID,
                "isAllIn" => $user->isAllIn,
                "betAmount" => $betAmount,
                "betName" => $betName
            ]
        ]);
    }

    private function setBlockerForNotEnoughPlayers(): void {
        $this->broadcastEvent([
            "type" => "eventBroadcast",
            "event" => "poker_newRoundCancel",
            "message" => "Not enough players connected and with balance"
        ]);
        $this->blockedTillRetry = true;
    }

    private function findNextPlayingUser($startIndex): int {
        $startIndex = $startIndex%$this->table->getPlayerCount();
        $currentIndex = $startIndex;
        while (!$this->table->getPlayerByIndex($currentIndex)->isPlaying) {
            $currentIndex = ($currentIndex+1)%$this->table->getPlayerCount();
            if ($currentIndex == $startIndex) {
                return -1;
            }
        }
        return $currentIndex;
    }

    public function getGameSpecificData(): array {
        $data = [
            "settings" => [
                "buyIn" => $this->settingBuyIn,
                "smallBlind" => $this->settingSmallBlind,
                "playerActionTime" => $this->settingsPlayerTimeToAct
            ]
        ];

        if ($this->isInit) {
            $data["gameData"] = [
                "players" => $this->table->toArray()
            ];
        }

        return $data;
    }
}

class PokerTable {
    private array $players;

    public function __construct() {
        $this->players = [];
    }

    public function addPlayer(PokerPlayer $player): void {
        $this->players[] = $player;
    }

    public function getPlayerByID(int $userID): PokerPlayer {
        foreach ($this->players as $player) {
            if ($player->userID == $userID) {
                return $player;
            }
        }

        throw new InvalidArgumentException("No player with ID $userID on table");
    }

    public function getPlayerByIndex(int $index): PokerPlayer {
        if ($index >= count($this->players)) {
            throw new InvalidArgumentException("Index $index is out of bounds for table");
        }

        return $this->players[$index];
    }

    public function getPlayerCount(): int {
        return count($this->players);
    }

    public function toArray(): array {
        $result = [];
        foreach ($this->players as $player) {
            $result[$player->userID] = $player->toArray();
        }
        return $result;
    }
}

class PokerPlayer {
    public int $userID;
    public int $balance;
    public array $cards;

    public bool $isPlaying;
    public bool $isAllIn;

    public function __construct(int $userID, int $initBalance) {
        $this->userID = $userID;
        $this->balance = $initBalance;

        $this->isAllIn = false;
        $this->isPlaying = true;

        $this->cards = [];
    }

    public function toArray(): array {
        return [
            "userID" => $this->userID,
            "balance" => $this->balance,
            "isPlaying" => $this->isPlaying,
            "isAllIn" => $this->isAllIn
        ];
    }
}

