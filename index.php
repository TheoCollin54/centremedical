<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Message de succès après redirection vers l'index si le rendez-vous a bien été ajouté-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <script>
            alert("Le rendez-vous a bien été ajouté ✅");
        </script>
    <?php endif; ?>

    <div class="container">
        <h1>Patient</h1> <!--Formulaire de connexion-->

        <div class="icon">
            <a href="demande_rdv.php">
                <img src="./img/calendar.png" alt="calendar" class="btn">
                <h3>Prise de rendez-vous</h3>
            </a>

        </div>

        <div class="icon">
            <a href="info.php">
                <img src="./img/information.png" alt="info" class="btn">
                <h3>Informations</h3>
            </a>

        </div>
    </div>
</body>

</html>