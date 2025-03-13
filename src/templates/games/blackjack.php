<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blackjack | WeGamble</title>
        <link rel="stylesheet" href="../../assets/css/main.css">
        <link rel="stylesheet" href="../../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../assets/css/games/blackjack.css">
        <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    </head>
    <body>
        <?php

        use OmniRoute\Extensions\OmniLogin;
        use OmniRoute\utils\Dotenv as config;

        require_once __DIR__."/../navbar.php";
        require_once __DIR__."/../../modules/dbController.php";
        ?>

        <div class="mainWrapper">
            <div id="startGameContainer">
                <h1>Blackjack Spiel starten</h1>
                <div class="betAmount">
                    <label for="betAmountInp">Wetteinsatz eingeben:</label><br>
                    <input type="number" min="0" id="betAmountInp" name="betAmountInp">€
                </div>
                <button onclick="startGame();" id="startGameBtn">Spiel starten</button>
            </div>
            <div id="playtable" class="hidden">
                <div class="ptPart-top">
                    <div id="dealerCards" class="cardWrapper"></div>
                    <div class="dealerNotice"><p>Dealer</p></div>
                    <div id="endMessage" class="hidden"></div>
                </div>
                <div class="ptPart-bottom">
                    <div id="userCards" class="cardWrapper"></div>
                    <div id="betAmountDisplay"></div>
                    <div class="userBtnWrapper" id="inGameBtns">
                        <div class="buttonRow">
                            <button class="uaBtn" id="uaBtn-stand" onclick="stand();">Stand</button>
                            <button class="uaBtn" id="uaBtn-hit" onclick="hit();">Hit</button>
                            <button class="uaBtn" id="uaBtn-doubleDown" onclick="doubleDown();">Double down</button>
                        </div>
                        <div class="buttonRow">
                            <button class="uaBtn" id="uaBtn-split">Split</button>
                            <button class="uaBtn" id="uaBtn-surrender" onclick="surrender();">Surrender</button>
                        </div>
                    </div>
                    <div class="userBtnWrapper hidden" id="postGameBtns">
                        <button onclick="resetBoard();">Neues Spiel</button>
                    </div>
                </div>
            </div>
        </div>

        
        <input type="hidden" value="<?php echo OmniLogin::getUser()["balance"] ?>" id="userBalanceStash">
        <input type="hidden" value="<?php echo OmniLogin::getUser()["apiToken"] ?>" id="apiTokenStash">
        <input type="hidden" value="<?php echo config::get("SERVER_PATH"); ?>" id="serverURLStash">
        <input type="hidden" value="<?php echo config::get("WS_PATH"); ?>" id="wsURLStash">
    </body>
    <script src="../../assets/js/functions.js"></script>
    <script src="../../assets/js/overlay.js"></script>
    <script src="../../assets/js/games/blackjack.js"></script>
</html>
