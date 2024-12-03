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
        <div class="gameSelector">
            <div class="gameCard">
                <div class="bgImage">
                    <img src="https://homburg1.de/wp-content/uploads/2022/03/Roulette-Casino-Gluecksspiel.jpg">
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
                    <img src="https://de2.sportal365images.com/process/smp-betway-images/betway.com/27072023/f8f1c651-8fa2-424d-bee4-77979469f189.jpg">
                    <div class="gc-Overlay">
                        <p class="gameName">Blackjack</p>
                        <div class="gameButtonWrapper">
                            <button onclick="window.location.href='/game/blackjack'">Jetzt spielen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>