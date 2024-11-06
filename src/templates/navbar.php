<div class="navbar">
    <div class="navElem navLeft">
        <button class="navBtn" onclick="window.location.href='/'"><i class="fa-solid fa-house"></i></button>
        <button class="navBtn" onclick="window.location.href='/leaderboard'"><i class="fa-solid fa-chart-simple"></i></button>
    </div>
    <div class="navElem navMid">
        <?php
        require_once __DIR__."/../modules/config/config.php";
        
        echo '<img src="'.SERVER_PATH.'/assets/img/logo.png" alt="Logo">';
        ?>
    </div>
    <div class="navElem navRight">
        <?php

            use OmniRoute\Extensions\OmniLogin;

            function formatCurrency($balance) {
                $balance = strval($balance);
                if (count_chars($balance) > 4) {
                    $balance = str_split(strrev($balance), 3);
                    return strrev(join(".", $balance));
                } else {
                    return $balance;
                }
            }

            echo '<div class="username">'.OmniLogin::getUser()["userName"].'</div>';
            echo '<div class="balance" id="balanceDisplay">'.formatCurrency(OmniLogin::getUser()["balance"]).'€</div>';
        ?>
    </div>
</div>