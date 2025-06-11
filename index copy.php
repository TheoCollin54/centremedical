<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Connexion</h1> <!--Formulaire de connexion-->
        <form  action="login.php" method="POST">
            <label for="username"><strong>Nom d'utilisateur :</strong></label>
            <input type="username" id="username" name="username" required>

            <label for="password"><strong>Mot de passe:</strong></label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit" class="login-btn">Se connecter</button>
        </form>
        <p>Vous n'avez pas de compte ? Inscrivez-vous !</p>
        <button class="btn" onclick="window.location.href='page_d_inscription.php'">S'inscrire</button>
        <br>
        <a href="mdp_oublie.php"> Mot de passe oublié </a>
    </div>
</body>
</html>