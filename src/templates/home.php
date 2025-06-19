<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home | WeGamble</title>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="assets/css/fontawesome.css">
        <link rel="stylesheet" href="assets/css/navbar.css">
        <link rel="stylesheet" href="assets/css/home.css">
        <link rel="icon" type="image/png" href="assets/img/logo.png">
    </head>
    <body>
        <?php
        require_once "navbar.php";
        ?>

        <div class="dailyLoginWrapper">
            <div class="loginStreakDisplay">
                <div class="loginStreakDisplay-left">
                    <i class="fa-solid fa-fire-flame-curved"></i> 3 Tage Login-Streak
                </div>
                <div class="loginStreakDisplay-right">
                    <button>Tägliche Belohnung abholen</button>
                </div>
            </div>
        </div>

        <div class="gameSelector">
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/roulette.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Roulette</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/roulette'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/blackjack.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Blackjack</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/blackjack'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/slots.jpeg">
                    <div class="gc-Overlay">
                        <p class="gameName">Slots</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/slots'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/hitTheNick.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Hit the Nick</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/hit-the-nick'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/poker.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Poker</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/blackjack'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/crasher.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Crasher</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/blackjack'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gameCard">
                <div class="bgImage">
                    <img src="/assets/img/gameThumbs/meiern.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Meiern</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/blackjack'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>