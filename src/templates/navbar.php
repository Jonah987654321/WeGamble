<div class="navbar">
    <div class="navElem navLeft">
        <button class="navBtn" onclick="window.location.href='/'"><i class="fa-solid fa-house"></i></button>
        <button class="navBtn" onclick="window.location.href='/leaderboard'"><i class="fa-solid fa-chart-simple"></i></button>
    </div>
    <div class="navElem navMid">
        <?php
        use OmniRoute\utils\Dotenv as config;
        require_once __DIR__."/../modules/utils.php";
        
        echo '<img src="'.config::get("APP_URL").'/assets/img/logo.png" alt="Logo">';
        ?>
    </div>
    <div class="navElem navRight">
        <?php

            use OmniRoute\Extensions\OmniLogin;

            echo '<div class="username">'.OmniLogin::getUser()["userName"].'</div>';
            echo '<div class="balance" id="balanceDisplay">'.formatCurrency(OmniLogin::getUser()["balance"]).'€</div>';
        ?>
    </div>
</div>
<div class="navbarMobileLandscape">
    <button><i class="fa-solid fa-bars"></i></button>
</div>