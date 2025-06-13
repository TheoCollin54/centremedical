<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <aside>
        <nav>
            <ul>
                <li><a class="inactive">Ajouter un médecin</a></li>
                <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li>
                <li><a href="ajout_info.php">Ajouter une information</a></li>
                <li><a href="dashboard_admin.php">Retour à l'accueil</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>

    <main>

        <div class="container">
            <h2>Ajouter un nouvel utilisateur</h2>

            <form action="add_doc.php" method="POST">
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