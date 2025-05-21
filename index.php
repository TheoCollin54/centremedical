<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Header</h1>
    </header>
    <div class="container">
        <h1>Connexion</h1>
        <form  action="login.php" method="POST">
            <label for="email"><strong>Nom d'utilisateur :</strong></label><br>
            <input type="username" id="username" name="username" required><br><br>

            <label for="password"><strong>Mot de passe:</strong></label><br>
            <input type="password" id="password" name="password" required><br><br>

            <button class="btn" type="submit" class="login-btn">Se connecter</button>
        </form>
        <button class="btn" onclick="window.location.href='Page_d_inscription'">S'inscrire</button>
    </div>
</body>
</html>