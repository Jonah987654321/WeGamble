<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $user["userName"];?> | WeGamble</title>
        <link rel="stylesheet" href="../assets/css/main.css">
        <link rel="stylesheet" href="../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../assets/css/navbar.css">
        <link rel="stylesheet" href="../assets/css/profile.css">
        <link rel="icon" type="image/png" href="../assets/img/logo.png">
    </head>
    <body>
        <?php
        require_once __DIR__."/../modules/utils.php";
        require_once "navbar.php";

        $gameIcons = [1 => "../assets/img/icon/casino-roulette.png", 2 => "../assets/img/icon/blackjack.png", 3 => "../assets/img/icon/punch.png", 4 => "../assets/img/icon/slot-machine.png"];
        $gameNames = [1 => "Roulette", 2 => "Blackjack", 3 => "Hit the Nick", 4 => "Slots"];
        ?>
        
        <div class="statsWrapper">
            <div class="userTop">
                <div class="userImgWrapper">
                    <img src="../assets/img/icon/user.png" class="userIcon">
                </div>
                <div class="userDetails">
                    <div class="statsUsername"><?php echo $user["userName"];?></div>
                    <div class="statsBalance"><?php echo formatCurrency($user["balance"]);?>€</div>
                </div>
            </div>
            <div class="statDisplayWrapper">
                <div class="statDisplay">
                    <h2>Stats</h2>
                    <div class="statsLine">
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-arrow-right-to-bracket"></i> Letzter Login:</div>
                            <div class="se-DetailsContent"><?php echo formatDate($data["lastLogin"])?></div>
                        </div>
                        <div class="statEntry">
                            <?php 
                            $hours = floor($data["playTime"] / 3600);
                            $minutes = floor(($data["playTime"] % 3600) / 60);

                            $playTime = "{$hours}h {$minutes}min";
                            ?>
                            <div class="se-DetailsTitle"><i class="fa-regular fa-clock"></i> Spielzeit:</div>
                            <div class="se-DetailsContent"><?php echo $playTime?></div>
                        </div>
                    </div>
                    <div class="statsLine">
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-coins"></i> Meistes Geld:</div>
                            <div class="se-DetailsContent"><?php echo formatCurrency($data["allTimeHigh"])?>€</div>
                        </div>
                        <?php
                            $bestGame = null;

                            foreach($data["gameStats"] as $g) {
                                $wl = $g["winSum"]+$g["looseSum"];
                                if ($bestGame == null || $bestGame["wl"] < $wl) {
                                    $bestGame = ["gameID"=>$g["gameID"], "wl"=>$wl];
                                }
                            }
                        ?>
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-ranking-star"></i> Erfolgreichstes Spiel:</div>
                            <div class="se-DetailsContent"><?php echo $gameNames[$bestGame["gameID"]].' ('.formatCurrency($bestGame["wl"]).'€)'?></div>
                        </div>
                    </div>
                    <div class="statsLine">
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-arrow-trend-up"></i> Gewinne gesamt:</div>
                            <div class="se-DetailsContent"><?php echo $data["totalWins"].' ('.formatCurrency($data["totalWinSum"]).'€)'?></div>
                        </div>
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-arrow-trend-down"></i> Verluste gesamt:</div>
                            <div class="se-DetailsContent"><?php echo $data["totalLosses"].' ('.formatCurrency($data["totalLossSum"]).'€)'?></div>
                        </div>
                    </div>
                    <div class="statsLine">
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-arrow-up"></i> Höchster Gewinn:</div>
                            <div class="se-DetailsContent"><?php echo formatCurrency($data["highestWin"]).'€'?></div>
                        </div>
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-arrow-down"></i> Höchster Verlust:</div>
                            <div class="se-DetailsContent"><?php echo formatCurrency($data["highestLoss"]).'€'?></div>
                        </div>
                    </div>
                    <div class="statsLine">
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-fire"></i> Längste Winstreak:</div>
                            <div class="se-DetailsContent"><?php echo $data["longestWinStreak"]?></div>
                        </div>
                        <div class="statEntry">
                            <div class="se-DetailsTitle"><i class="fa-solid fa-fire"></i> Längste Loosestreak:</div>
                            <div class="se-DetailsContent"><?php echo $data["longestLooseStreak"]?></div>
                        </div>
                    </div>
                </div>
                <div class="statDisplay">
                    <h2>Letzte Spiele</h2>
                    <?php
                    $i = 1;
                    foreach ($data["history"] as $h) {
                        if ($i == 7) {
                            break;
                        }
                        echo '<div class="historyEntry">
                            <div class="he-iconSection">
                                <img src="'.$gameIcons[$h["gameID"]].'" class="he-icon">
                            </div>
                            <div class="he-infoSection">
                                <div class="he-winLoss">'.(($h["winLoss"]>0)?"+":"").formatCurrency($h["winLoss"]).'€</div>
                                <div class="he-details">'.$gameNames[$h["gameID"]].', '.formatDate($h["timestamp"]).'</div>
                            </div>
                        </div>';
                        $i++;
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>