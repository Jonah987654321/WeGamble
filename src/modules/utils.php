<?php
function formatCurrency($balance) {
    $balance = strval($balance);
    if (count_chars($balance) > 4) {
        $balance = str_split(strrev($balance), 3);
        return strrev(join(".", $balance));
    } else {
        return $balance;
    }
}
?>