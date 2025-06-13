<?php
session_start();

// Vérifie que l'utilisateur est connecté
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

$sql_user = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($user && $user['username'] === 'admin') {
    header("Location: dashboard_admin.php");
    exit();
}

$stmt = $pdo->query("SELECT users_id, username FROM users");
$utilisateurs = $stmt->fetchAll();

$sql_name = "SELECT username, speciality
                FROM users
                WHERE users_id = :user_id";
$stmt_name = $pdo->prepare($sql_name);
$stmt_name->execute(['user_id' => $user_id]);
$name = $stmt_name->fetch(PDO::FETCH_ASSOC);
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

<body>
    <!-- Messages d'erreur -->
    <?php if (isset($_GET['fail']) && $_GET['fail'] == 1): ?>
        <script>
            alert("Le numéro de téléphone n'est pas valide ❌");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['fail']) && $_GET['fail'] == 2): ?>
        <script>
            alert("Le numéro de sécurité sociale n'est pas valide ❌");
        </script>
    <?php endif; ?>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="dashboard.php">Mes rendez-vous</a></li>
                <li><a href="#" class="inactive">Ajouter un rendez-vous</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
        </nav>
        <br>
        <p class="doctor_name">
            Connecté en tant que : <?= htmlspecialchars($name['username']) ?>
            (<?= htmlspecialchars($name['speciality']) ?>)
        </p>
    </aside>
    <main>
        <div class="container">
            <form action="add_rdv_doc.php" method="POST">
                <input type="hidden" name="doctor_id" value="<?php echo $_SESSION['users_id']; ?>">



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
    <script>
        flatpickr("#date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S", // Format compatible TIMESTAMP
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr"
        });
    </script>
</body>

</html>