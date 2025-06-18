<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une information</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="dashboard_admin.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="ajout_doc.php"><i class="fa-solid fa-user-doctor"></i> Ajouter un médecin</a></li>
                <!-- <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li> -->
                <li><a href="#" class="inactive"><i class="fa-solid fa-circle-info"></i> Ajouter une information</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <main>

        <div class="container">
            <h2>Ajouter une information</h2>
            <form action="add_info.php" method="POST" class="log">
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