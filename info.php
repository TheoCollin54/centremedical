<?php
require_once('./db/connection.php');

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des infos
    $stmt = $pdo->query("SELECT title, description FROM infos");
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Informations Médicales</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <aside>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>
    <main>

        <h1>Informations</h1>

        <?php if (!empty($infos)): ?>
            <ul>
                <?php foreach ($infos as $info): ?>
                    <li>
                        <strong><?= htmlspecialchars($info['title']) ?></strong><br>
                        <?= nl2br(htmlspecialchars($info['description'])) ?>
                    </li>
                    <hr>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune information disponible.</p>
        <?php endif; ?>
    </main>
</body>

</html>