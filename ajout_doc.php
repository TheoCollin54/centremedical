<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        ul#suggestions {
            width: 100%;
            max-width: 400px;
            border: 1px solid #ccc;
            list-style: none;
            padding: 0;
            margin-top: 5px;
            position: absolute;
            background-color: white;
            z-index: 10;
        }

        ul#suggestions li {
            padding: 8px;
            cursor: pointer;
        }

        ul#suggestions li:hover {
            background-color: #eee;
        }

        .address-container {
            position: relative;
        }
    </style>
</head>

<body>

    <aside>
        <nav>
            <ul>
                <li><a href="dashboard_admin.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="#" class="inactive"><i class="fa-solid fa-user-doctor"></i> Ajouter un médecin</a></li>
                <li><a href="ajout_info.php"><i class="fa-solid fa-circle-info"></i> Ajouter une information</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <div class="container" style="margin-top:100px;">
            <form action="add_doc.php" method="POST" class="log">
                <label for="username"><strong>Nom d'utilisateur :</strong></label>
                <input type="text" id="username" name="username" required>

                <label for="speciality"><strong>Spécialité :</strong></label>
                <input type="text" id="speciality" name="speciality" required>

                <label for="adress"><strong>Adresse :</strong></label>
                <div class="address-container">
                    <input type="text" id="adress" name="adress" autocomplete="off" required>
                    <ul id="suggestions"></ul>
                </div>

                <label for="email"><strong>Email :</strong></label>
                <input type="email" id="email" name="email" required>

                <label for="password"><strong>Mot de passe :</strong></label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="btn">Ajouter l'utilisateur</button>
            </form>
        </div>
    </main>

    <script src="./js/scriptAdress.js"></script>
</body>

</html>