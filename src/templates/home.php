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
                <p class="gameName">Roulette</p>
                <img src="https://homburg1.de/wp-content/uploads/2022/03/Roulette-Casino-Gluecksspiel.jpg">
                <p class="gameDesc">Erlebe den Nervenkitzel von Roulette, einem der beliebtesten Casinospiele weltweit! Setze auf dein Glück, während das Rad sich dreht und die Kugel über die Zahlen tanzt - jede Runde bietet die Chance auf große Gewinne. Wirst du der nächste Glückspilz sein, der den Jackpot knackt?</p>
                <div class="gameButtonWrapper">
                    <button onclick="window.location.href='/game/roulette'">Jetzt spielen</button>
                </div>
            </div>
        </div>
    </body>
</html>