<?php
function formatCurrency($balance) {
    $balance = strval($balance);
    $negative = str_starts_with($balance, "-")?"-":"";
  	$balance = str_replace("-", "", $balance);
    if (count_chars($balance) > 4) {
        $balance = str_split(strrev($balance), 3);
        return $negative.strrev(join(".", $balance));
    } else {
        return $negative.$balance;
    }
}

function formatDate($date) {
    return date_format(date_create($date),"d.m.Y - H:i");
}
?>