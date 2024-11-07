<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Leadboard | WeGamble</title>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="assets/css/fontawesome.css">
        <link rel="stylesheet" href="assets/css/navbar.css">
        <link rel="stylesheet" href="assets/css/leaderboard.css">
        <link rel="icon" type="image/png" href="assets/img/logo.png">
    </head>
    <body>
        <?php
        require_once "navbar.php";
        ?>
        <div class="leaderboardContainer">
            <?php
            $i = 1;
            foreach ($data as $entry) {
                echo "$i. ".$entry[1]." (".$entry[2]."€)<br>";
                $i++;
            } 
            ?>
        </div>
    </body>
</html>