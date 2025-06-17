<?php
session_start();

if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

$user_id = $_SESSION['users_id'];

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

// Récupération des infos actuelles
$sql = "SELECT username, email, speciality FROM users WHERE users_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_speciality = $_POST['speciality'];
    $new_password = $_POST['password'];

    $params = [
        'username' => $new_username,
        'email' => $new_email,
        'speciality' => $new_speciality,
        'user_id' => $user_id,
    ];

    $sql_update = "UPDATE users SET username = :username, email = :email, speciality = :speciality";

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update .= ", password = :password";
        $params['password'] = $hashed_password;
    }

    $sql_update .= " WHERE users_id = :user_id";

    $stmt_update = $pdo->prepare($sql_update);
    if ($stmt_update->execute($params)) {
        $message = "Mise à jour réussie.";
        // Met à jour la session si le pseudo change
        $_SESSION['username'] = $new_username;
    } else {
        $message = "Erreur lors de la mise à jour.";
    }

    // Recharger les données modifiées
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mes informations</title>
     <link rel="stylesheet" href="./css/styles.css" />
</head>
<body>
<aside>
    <nav>
        <ul>
            <li><a href="#" class="inactive">Mes rendez-vous</a></li>
            <li><a href="demande_rdv_doc.php">Ajouter un rendez-vous</a></li>
            <li><a href="logout.php">Se déconnecter</a></li>
        </ul>
    </nav>
    <br>
    <p class="doctor_name">
        Connecté en tant que : <?= htmlspecialchars($user['username']) ?>
        (<?= htmlspecialchars($user['speciality']) ?>)
    </p>
</aside>

<main>
    <h2>Modifier mes informations</h2>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label for="email">Email :</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="speciality">Spécialité :</label>
        <input type="text" name="speciality" id="speciality" value="<?= htmlspecialchars($user['speciality']) ?>" required>

        <label for="password">Nouveau mot de passe :</label>
        <input type="password" name="password" id="password">
        <small>Laisser vide pour ne pas changer</small>

        <button type="submit">Enregistrer</button>
    </form>
</main>
</body>
</html>
