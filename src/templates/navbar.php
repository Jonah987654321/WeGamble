<div class="navbar">
    <div class="navElem navLeft"></div>
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