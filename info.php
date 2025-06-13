<?php
require_once('./db/connection.php');

// Connexion à la base de données
$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer toutes les informations
    $stmt = $pdo->query("SELECT * FROM infos ORDER BY info_id DESC");
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <div class="container">
            <?php if (!empty($infos)): ?>
                <?php foreach ($infos as $info): ?>
                    <div class="info-card">
                        <h2><?= htmlspecialchars($info['title']) ?></h2>
                        <p><?= nl2br(htmlspecialchars($info['description'])) ?></p>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune information disponible.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>