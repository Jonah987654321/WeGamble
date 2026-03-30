<?php

class CardDeck {

    private static array $cardValues = [
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
    private array $cards;

    public function __construct() {
        $this->resetCards();
    }

    public function resetCards(): void {
        $this->cards = array_keys(CardDeck::$cardValues);
    }

    public function drawCard(): string {
        // Get a random card index
        $newCardIndex = rand(0, count($this->cards)-1);
        $card = $this->cards[$newCardIndex];

        //Remove card & reindex to close gaps
        unset($this->cards[$newCardIndex]);
        $this->cards = array_values($this->cards);

        return $card;
    } 

    public static function getCardValue(string $card): int {
        if (!array_key_exists($card, CardDeck::$cardValues)) {
            throw new InvalidArgumentException("String is not a card");
        }

        return CardDeck::$cardValues[$card];
    }
}

?>