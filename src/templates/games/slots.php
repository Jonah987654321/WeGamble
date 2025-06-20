<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Slots | WeGamble</title>
        <link rel="stylesheet" href="../../assets/css/main.css">
        <link rel="stylesheet" href="../../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../assets/css/games/slots.css">
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
            <div class="slotWrapper">
                <div class="slotGrid">
                    <div class="slotRow">
                        <div class="slotRoller" id="sR1">
                            <div id="sRI1" class="sRI">
                                <img src="../../assets/img/slots/1.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR2">
                            <div id="sRI2" class="sRI">
                                <img src="../../assets/img/slots/2.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR3">
                            <div id="sRI3" class="sRI">
                                <img src="../../assets/img/slots/3.png">
                            </div>
                        </div>
                    </div>
                    <div class="slotRow">
                        <div class="slotRoller" id="sR4">
                            <div id="sRI4" class="sRI">
                                <img src="../../assets/img/slots/4.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR5">
                            <div id="sRI5" class="sRI">
                                <img src="../../assets/img/slots/5.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR6">
                            <div id="sRI6" class="sRI">
                                <img src="../../assets/img/slots/6.png">
                            </div>
                        </div>
                    </div>
                    <div class="slotRow">
                        <div class="slotRoller" id="sR7">
                            <div id="sRI7" class="sRI">
                                <img src="../../assets/img/slots/7.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR8">
                            <div id="sRI8" class="sRI">
                                <img src="../../assets/img/slots/8.png">
                            </div>
                        </div>
                        <div class="slotRoller" id="sR9">
                            <div id="sRI9" class="sRI">
                                <img src="../../assets/img/slots/9.png">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="blocker"></div>
            </div>
            <div class="inputWrapper">
                <div class="iw iw-left" id="betInputWrapper">
                    <table>
                        <tr>
                            <td class="align-right">Gewinnreihen Einsatz:</td>
                            <td><input type="number" id="betInput" value="100">€</td>
                        </tr>
                        <tr>
                            <td class="align-right">Gesamteinsatz:</td>
                            <td class="align-right"><span id="totalBet">800</span>€</td>
                        </tr>
                    </table>
                    <div class="blocker hidden" id="betInputBlocker"></div>
                </div>
                <div class="iw iw-mid">
                    <button onclick="spin()">Spin</button>
                </div>
                <div class="iw iw-right">
                    <div id="spinIndicWrapper" class="iwRightField">
                        <div class="iwRight_LeftField">Freispiele: </div>
                        <div id="freeSpinIndic">0</div>
                    </div>
                    <div class="iwRightField">
                        <div class="iwRight_LeftField">Gewinn: </div>
                        <div id="winIndic">0€</div>
                    </div>
                </div>
            </div>
        </div>

        
        <input type="hidden" value="<?php echo OmniLogin::getUser()["balance"] ?>" id="userBalanceStash">
        <input type="hidden" value="<?php echo OmniLogin::getUser()["apiToken"] ?>" id="apiTokenStash">
        <input type="hidden" value="<?php echo config::get("APP_URL"); ?>" id="serverURLStash">
        <input type="hidden" value="<?php echo config::get("WS_URL"); ?>" id="wsURLStash">
    </body>
    <script src="../../assets/js/wsClient.js"></script>
    <script src="../../assets/js/functions.js"></script>
    <script src="../../assets/js/overlay.js"></script>
    <script src="../../assets/js/games/slots.js"></script>
</html>
