<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <aside>
        <nav>
            <ul>
                <li><a href="dashboard_admin.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a class="inactive">Ajouter un médecin</a></li>
                <!-- <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li> -->
                <li><a href="ajout_info.php">Ajouter une information</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <main>

        <h2>Ajouter un nouvel utilisateur</h2>
        <div class="container">

            <form action="add_doc.php" method="POST" class = "index">
                <label for="username"><strong>Nom d'utilisateur :</strong></label>
                <input type="text" id="username" name="username" required>

                <label for="speciality"><strong>Spécialité :</strong></label>
                <input type="text" id="speciality" name="speciality" required>

                <label for="email"><strong>Email :</strong></label>
                <input type="email" id="email" name="email" required>

                <label for="password"><strong>Mot de passe :</strong></label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="btn">Ajouter l'utilisateur</button>
            </form>
        </div>
    </main>
</body>

</html>