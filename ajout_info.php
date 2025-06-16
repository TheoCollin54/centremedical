<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une information</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="ajout_doc.php">Ajouter un médecin</a></li>
                <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li>
                <li><a class="inactive">Ajouter une information</a></li>
                <li><a href="dashboard_admin.php">Retour à l'accueil</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <h2>Ajouter une information</h2>
        <div class="container">
            <form action="add_info.php" method="POST" class = "index">
                <label for="title"><strong>Titre :</strong></label>
                <input type="text" id="title" name="title" required>

                <label for="description"><strong>Description :</strong></label>
                <input type="text" id="description" name="description" required>

                <button type="submit" class="btn">Ajouter l'information</button>
            </form>
        </div>
    </main>
</body>

</html>