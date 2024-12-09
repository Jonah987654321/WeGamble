<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hit the Nick | WeGamble</title>
        <link rel="stylesheet" href="../../assets/css/main.css">
        <link rel="stylesheet" href="../../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../assets/css/speechBubbles.css">
        <link rel="stylesheet" href="../../assets/css/games/hit-the-nick.css">
        <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    </head>
    <body>
        <?php

        use OmniRoute\Extensions\OmniLogin;

        require_once __DIR__."/../navbar.php";
        require_once __DIR__."/../../modules/config/config.php";
        require_once __DIR__."/../../modules/dbController.php";
        ?>

        <div class="mainWrapper">
            <div class="mainInnerWrapper">
                <div id="classroom">
                    <div id="startGameContainer" class="leftPart">
                        <div class="startGameContainerInnerWrapper">
                            <div class="betAmount">
                                <label for="betAmountInp">Wetteinsatz eingeben:</label><br>
                                <input type="number" min="0" id="betAmountInp" name="betAmountInp">€
                            </div>
                            <button onclick="startGame();" id="startGameBtn">Spiel starten</button>
                        </div>
                    </div>
                    <div class="allDesks hidden leftPart" id="playtable">
                        <div class="deskWrapper" id="d1" onclick="guess(1);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d2" onclick="guess(2);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d3" onclick="guess(3);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d4" onclick="guess(4);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d5" onclick="guess(5);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d6" onclick="guess(6);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d7" onclick="guess(7);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d8" onclick="guess(8);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div class="deskWrapper" id="d9" onclick="guess(9);"><img src="../../assets/img/desk.png" class="desk"></div>
                        <div id="blocker" class="hidden"></div>
                    </div>
                    <div class="dealer">
                        <div id="msgTop">
                            <div speech-bubble pbottom aright>
                                <p>Gib links einen Wetteinsatz ein, um das Spiel zu starten</p>
                            </div>
                        </div>
                        <div id="imgDown">
                            <div id="imgDLeft" class="notActive">
                                <div>
                                    <button onclick="resetGame();">Neues Spiel</button>
                                </div>
                                <div>
                                    <button onclick="redoGame();">Gleicher Einsatz nochmal</button>
                                </div>
                            </div>
                            <div id="imgDRight">
                                <img src="../../assets/img/bald-man.png">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <input type="hidden" value="<?php echo OmniLogin::getUser()["balance"] ?>" id="userBalanceStash">
        <input type="hidden" value="<?php echo OmniLogin::getUser()["apiToken"] ?>" id="apiTokenStash">
        <input type="hidden" value="<?php echo SERVER_PATH ?>" id="serverURLStash">
        <input type="hidden" value="<?php echo WS_PATH ?>" id="wsURLStash">
    </body>
    <script src="../../assets/js/functions.js"></script>
    <script src="../../assets/js/overlay.js"></script>
    <script src="../../assets/js/games/hit-the-nick.js"></script>
</html>
