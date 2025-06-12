<?php
session_start();

require_once('./db/connection.php');

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $speciality = htmlspecialchars(trim($_POST['speciality']));

    // Vérifie si l'email existe déjà
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt_check->execute([$email]);
    $emailExists = $stmt_check->fetchColumn();

    if ($emailExists) {
        $message = "❌ Un utilisateur avec cet email existe déjà.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, speciality) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $speciality]);
            $message = "✅ Utilisateur ajouté avec succès.";
        } catch (PDOException $e) {
            $message = "❌ Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>

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