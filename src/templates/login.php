<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | WeGamble</title>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="assets/css/fontawesome.css">
        <link rel="stylesheet" href="assets/css/login.css">
        <link rel="icon" type="image/png" href="assets/img/logo.png">
    </head>
    <body>
        <div class="loginWrap">
            <form action="/login" method="post">
                <div class="logoContainer">
                    <h1>Anmeldung</h1>
                </div>
                <div class="loginFieldWrap">
                    <label for="user">Nutzername</label><br>
                    <input type="text" name="username" id="username" placeholder="Gib deinen Nutzernamen ein" required>
                    <i class="fa-solid fa-user inputIcon"></i><br>
                
                    <label for="password">Passwort</label><br>
                    <input type="password" name="password" id="password" placeholder="Gib dein Passwort ein" required>
                    <i class="fa-solid fa-lock inputIcon"></i><br>
                    <?php
                    if (isset($_SESSION["invalidLogin"])) {
                        echo '<div class="alignRight forgotPwd errorIndicator">
                            <p>Ungültige Anmeldedaten!</p>
                        </div>';
                        unset($_SESSION["invalidLogin"]);
                    }
                    ?>
                </div>
                <button type="submit" id="loginButton">Login</button>
            </form>
        </div>
    </body>
</html>
