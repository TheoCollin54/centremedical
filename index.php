<?php
$message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "Le rendez-vous a bien été ajouté ✅";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesImg.css">
</head>

<body data-message="<?= htmlspecialchars($message) ?>">

    <div class="container">
        <h1>Patient</h1> <!--Formulaire de connexion-->

        <div class="icon">
            <a href="demande_rdv.php">
                <img src="./img/calendar.png" alt="calendar" class="btn">
                <h3>Prendre un rendez-vous</h3>
            </a>

        </div>

        <div class="icon">
            <a href="info.php">
                <img src="./img/information.png" alt="info" class="btn">
                <h3>Informations</h3>
            </a>

        </div>
    </div>
    <script src="./js/scriptMsg.js"></script>
    <script src="./js/scriptVeille.js"></script>
</body>

</html>