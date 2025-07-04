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
$sql = "SELECT username, email, speciality, adress FROM users WHERE users_id = :user_id";
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
    $new_adress = $_POST['adress'];

    $params = [
        'username' => $new_username,
        'email' => $new_email,
        'speciality' => $new_speciality,
        'adress' => $new_adress,
        'user_id' => $user_id,
    ];

    $sql_update = "UPDATE users SET username = :username, email = :email, speciality = :speciality, adress = :adress";

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update .= ", password = :password";
        $params['password'] = $hashed_password;
    }

    $sql_update .= " WHERE users_id = :user_id";

    $stmt_update = $pdo->prepare($sql_update);
    if ($stmt_update->execute($params)) {
        header("Location: edit_account_doc.php?success");
        // Met à jour la session si le pseudo change
        $_SESSION['username'] = $new_username;
    } else {
        header("Location: edit_account_doc.php?fail");
    }

    // Recharger les données modifiées
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}


$message = "";

// Message de succès
if (isset($_GET['success'])) {
    $message = "La modification a été effectuée avec succès ✅";
}
// Message d'erreur
if (isset($_GET['fail'])) {
    $message = "La modification a échoué ❌";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier mes informations</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesIcons.css" />
    <link rel="stylesheet" href="./css/stylesAdress.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>

    <body data-message="<?= htmlspecialchars($message) ?>"></body>
    <aside>
        <nav>
            <ul>
                <li><a href="dashboard.php"> <i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
                <li><a href="demande_rdv_doc.php"><i class="fas fa-plus"></i> Ajouter un rendez-vous</a></li>
                <li><a href="#" class="inactive"> <i class="fas fa-edit"></i>Modifier mes informations</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
        <br>
        <p class="doctor_name">
            Connecté en tant que : <br> <?= htmlspecialchars($user['username']) ?>
            (<?= htmlspecialchars($user['speciality']) ?>)
        </p>
    </aside>

    <main>
        <h2>Modifier mes informations</h2>

        <form method="post">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>"
                required>

            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="password">Nouveau mot de passe : <small>(Laisser vide pour ne pas changer)</small></label>
            <input type="password" name="password" id="password">

            <label for="speciality">Spécialité :</label>
            <input type="text" name="speciality" id="speciality" value="<?= htmlspecialchars($user['speciality']) ?>"
                required>

            <label for="adress">Adresse :</label>
            <div class="address-container">
                <input type="text" name="adress" id="adress" value="<?= htmlspecialchars($user['adress']) ?>" required
                    autocomplete="off" />
                <ul id="suggestions"></ul>
            </div>

            <button type="submit">Enregistrer</button>
        </form>
    </main>
    <script src="./js/scriptAdress.js"></script>
    <script src="./js/scriptMsg.js"></script>
</body>

</html>