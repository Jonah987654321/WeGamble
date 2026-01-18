<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blackjack | WeGamble</title>
        <link rel="stylesheet" href="../../assets/css/main.css">
        <link rel="stylesheet" href="../../assets/css/fontawesome.css">
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../assets/css/lobbySelector.css">
        <link rel="stylesheet" href="../../assets/css/games/poker.css">
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
            <div id="lobbySelectorWrapper"></div>
            <div id="lobbyWaitingDisplay">

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
    <script src="../../assets/js/mpComponents/lobbySelector.js"></script>
    <script src="../../assets/js/games/poker.js"></script>
</html>
