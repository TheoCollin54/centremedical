<?php
session_start();

if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php"); // redirection vers la page de connexion si non connecté
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

$stmt = $pdo->query("SELECT users_id, username, speciality FROM users WHERE username != 'admin'");
$medecins = $stmt->fetchAll();

$sql_user = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($user && $user['username'] !== 'admin') {
    header("Location: index_doc.php");
    exit();
}
$message = "";
if (isset($_GET['fail'])) {
    if ($_GET['fail'] == 1) {
        $message = "Le numéro de téléphone n'est pas valide ❌";
    } elseif ($_GET['fail'] == 2) {
        $message = "Le numéro de sécurité sociale n'est pas valide ❌";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body data-message="<?= htmlspecialchars($message) ?>">
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="ajout_doc.php">Ajouter un médecin</a></li>
                <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li>
                <li><a href="ajout_info.php">Ajouter une information</a></li>
                <li><a href="dashboard_admin.php">Retour à l'accueil</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <div class="container">
            <form action="add_rdv_admin.php" method="POST">

                <label for="users_id">Choisissez votre médecin :</label>
                <select name="users_id" id="users" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($medecins as $user): ?>
                        <option value="<?= htmlspecialchars($user['users_id']) ?>">
                            <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['speciality']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>


                <label for="name"><strong>NOM :</strong></label>
                <input type="text" id="name" name="name" required>

                <label for="firstname"><strong>PRÉNOM :</strong></label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="tel" maxlength="10" minlength="10"><strong>TÉLÉPHONE :</strong></label>
                <input type="number" id="tel" name="tel" required>

                <label for="numsecu"><strong>NUMÉRO DE SÉCURITÉ SOCIALE :</strong></label>
                <input type="number" id="numsecu" name="numsecu" maxlength="15" minlength="15" required>

                <label for="date"><strong>DATE ET HEURE DU RENDEZ-VOUS :</strong></label>
                <input type="text" id="date" name="date" required>


                <button class="btn" type="submit" class="login-btn">Ajouter</button>
            </form>
        </div>
    </main>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="./js/scriptCalendar.js"></script>
    <script src="./js/scriptMsg.js"></script>
</body>

</html>