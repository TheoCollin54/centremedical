<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="./css/styles.css" />
</head>

<body>
    <!-- Message d'erreur -->
    <?php if (isset($_GET['fail']) && $_GET['fail'] == "login"): ?>
        <script>alert("Nom d'utilisateur ou mot de passe incorrects ❌");</script>
    <?php endif; ?>
    <h1 class="subtitle">Connexion</h1>
    <div class="container">
        <!--Formulaire de connexion-->
        <form action="login.php" method="POST" class="log">
            <label for="username"><strong>Nom d'utilisateur :</strong></label>
            <input type="username" id="username" name="username" required>

            <label for="password"><strong>Mot de passe:</strong></label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit" class="login-btn">Se connecter</button>
        </form>

        
        <a href="#" style="margin-top : 200px"> Mot de passe oublié </a>
        <!-- href devrait être mdp_oublie.php mais besoin d'un nom de domaine pour pouvoir envoyer des mails -->
    </div>
</body>

</html>