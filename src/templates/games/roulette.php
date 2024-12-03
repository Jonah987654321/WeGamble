<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Roulette | WeGamble</title>
        <link rel="stylesheet" href="../../assets/css/main.css">
        <link rel="stylesheet" href="../../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../assets/css/games/roulette.css">
        <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    </head>
    <body>
        <?php

        use OmniRoute\Extensions\OmniLogin;

        require_once __DIR__."/../navbar.php";
        require_once __DIR__."/../../modules/config/config.php";
        ?>

        <div class="mainWrapper">
            <table class="rouletteBoard">
                <tr>
                    <td rowspan="3" class="roNF">
                        <div id="roF-0" class="roF greenField">0</div>
                        <div id="roBD-0" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-3" class="roF redField">3</div>
                        <div id="roBD-3" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-6" class="roF blackField">6</div>
                        <div id="roBD-6" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-9" class="roF redField">9</div>
                        <div id="roBD-9" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-12" class="roF redField">12</div>
                        <div id="roBD-12" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-15" class="roF blackField">15</div>
                        <div id="roBD-15" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-18" class="roF redField">18</div>
                        <div id="roBD-18" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-21" class="roF redField">21</div>
                        <div id="roBD-21" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-24" class="roF blackField">24</div>
                        <div id="roBD-24" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-27" class="roF redField">27</div>
                        <div id="roBD-27" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-30" class="roF redField">30</div>
                        <div id="roBD-30" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-33" class="roF blackField">33</div>
                        <div id="roBD-33" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-36" class="roF redField">36</div>
                        <div id="roBD-36" class="betDisplay"></div>
                    </td>
                    <td class="roRF">
                        <div id="roF-r1" class="roF">1. Reihe</div>
                        <div id="roBD-r1" class="betDisplay"></div>
                    </td>
                </tr>
                <tr>
                    <td class="roNF">
                        <div id="roF-2" class="roF blackField">2</div>
                        <div id="roBD-2" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-5" class="roF redField">5</div>
                        <div id="roBD-5" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-8" class="roF blackField">8</div>
                        <div id="roBD-8" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-11" class="roF blackField">11</div>
                        <div id="roBD-11" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-14" class="roF redField">14</div>
                        <div id="roBD-14" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-17" class="roF blackField">17</div>
                        <div id="roBD-17" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-20" class="roF blackField">20</div>
                        <div id="roBD-20" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-23" class="roF redField">23</div>
                        <div id="roBD-23" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-26" class="roF blackField">26</div>
                        <div id="roBD-26" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-29" class="roF blackField">29</div>
                        <div id="roBD-29" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-32" class="roF redField">32</div>
                        <div id="roBD-32" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-35" class="roF blackField">35</div>
                        <div id="roBD-35" class="betDisplay"></div>
                    </td>
                    <td class="roRF">
                        <div id="roF-r2" class="roF">2. Reihe</div>
                        <div id="roBD-r2" class="betDisplay"></div>
                    </td>
                </tr>
                <tr>
                    <td class="roNF">
                        <div id="roF-1" class="roF redField">1</div>
                        <div id="roBD-1" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-4" class="roF blackField">4</div>
                        <div id="roBD-4" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-7" class="roF redField">7</div>
                        <div id="roBD-7" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-10" class="roF blackField">10</div>
                        <div id="roBD-10" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-13" class="roF blackField">13</div>
                        <div id="roBD-13" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-16" class="roF redField">16</div>
                        <div id="roBD-16" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-19" class="roF redField">19</div>
                        <div id="roBD-19" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-22" class="roF blackField">22</div>
                        <div id="roBD-22" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-25" class="roF redField">25</div>
                        <div id="roBD-25" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-28" class="roF blackField">28</div>
                        <div id="roBD-28" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-31" class="roF blackField">31</div>
                        <div id="roBD-31" class="betDisplay"></div>
                    </td>
                    <td class="roNF">
                        <div id="roF-34" class="roF redField">34</div>
                        <div id="roBD-34" class="betDisplay"></div>
                    </td>
                    <td class="roRF">
                        <div id="roF-r3" class="roF">3. Reihe</div>
                        <div id="roBD-r3" class="betDisplay"></div>
                    </td>
                </tr>
                <tr>
                    <td class="roE"></td>
                    <td colspan="4" class="roNF">
                        <div id="roF-t1" class="roF">1. Drittel</div>
                        <div id="roBD-t1" class="betDisplay"></div>
                    </td>
                    <td colspan="4" class="roNF">
                        <div id="roF-t2" class="roF">2. Drittel</div>
                        <div id="roBD-t2" class="betDisplay"></div>
                    </td>
                    <td colspan="4" class="roNF">
                        <div id="roF-t3" class="roF">3. Drittel</div>
                        <div id="roBD-t3" class="betDisplay"></div>
                    </td>
                    <td class="roE"></td>
                </tr>
                <tr>
                    <td class="roE"></td>
                    <td colspan="2" class="roNF">
                        <div id="roF-h1" class="roF">1 bis 18</div>
                        <div id="roBD-h1" class="betDisplay"></div>
                    </td>
                    <td colspan="2" class="roNF">
                        <div id="roF-ue" class="roF">Ungerade</div>
                        <div id="roBD-ue" class="betDisplay"></div>
                    </td>
                    <td colspan="2" class="roNF">
                        <div id="roF-red" class="roF redField">Rot</div>
                        <div id="roBD-red" class="betDisplay"></div>
                    </td>
                    <td colspan="2" class="roNF">
                        <div id="roF-black" class="roF blackField">Schwarz</div>
                        <div id="roBD-black" class="betDisplay"></div>
                    </td>
                    <td colspan="2" class="roNF">
                        <div id="roF-ev" class="roF">Gerade</div>
                        <div id="roBD-ev" class="betDisplay"></div>
                    </td>
                    <td colspan="2" class="roNF">
                        <div id="roF-h2" class="roF">19 bis 36</div>
                        <div id="roBD-h2" class="betDisplay"></div>
                    </td>
                    <td class="roE"></td>
                </tr>
            </table>
        </div>
        <div id="lastBidsRedo">
            <p>Letzte Einsätze:</p>
            <button onclick="redoLastBids(1);">x1</button>
            <button onclick="redoLastBids(2);">x2</button>
        </div>
        <div class="allBidsDone">
            <button onclick="spin();" id="spinBtn">Spin</button>
        </div>
        <input type="hidden" value="<?php echo OmniLogin::getUser()["balance"] ?>" id="userBalanceStash">
        <input type="hidden" value="<?php echo OmniLogin::getUser()["apiToken"] ?>" id="apiTokenStash">
        <input type="hidden" value="<?php echo SERVER_PATH ?>" id="serverURLStash">
        <input type="hidden" value="<?php echo WS_PATH ?>" id="wsURLStash">
    </body>
    <script src="../../assets/js/functions.js"></script>
    <script src="../../assets/js/overlay.js"></script>
    <script src="../../assets/js/games/roulette.js"></script>
</html>
