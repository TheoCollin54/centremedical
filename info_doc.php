<?php
require_once('./db/connection.php');
$pdo = new PDO("mysql:host={$dbConn['host']};dbname={$dbConn['name']};charset=utf8", $dbConn['user'], $dbConn['pass']);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE users_id = ?");
    $stmt->execute([$id]);
    $medecin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Informations du médecin</title>
</head>

<body>
    <?php if ($medecin): ?>
        <h1><?= htmlspecialchars($medecin['username']) ?></h1>
        <p>Spécialité : <?= htmlspecialchars($medecin['speciality']) ?></p>
        <!-- ajoute d'autres infos si dispo -->
    <?php else: ?>
        <p>Médecin introuvable.</p>
    <?php endif; ?>
</body>

</html>