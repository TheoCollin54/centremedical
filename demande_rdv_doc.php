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

// Requête AJAX dynamique avec dates de semaine
if (isset($_GET['ajax_get_slots']) && isset($_GET['medecin_id'])) {
    $medecinId = intval($_GET['medecin_id']);
    $start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end = isset($_GET['end_date']) ? $_GET['end_date'] . ' 23:59:59' : date('Y-m-d 23:59:59');

    $stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE doctor_id = :medecinId AND date BETWEEN :start AND :end");
    $stmt->execute(['medecinId' => $medecinId, 'start' => $start, 'end' => $end]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $slots = array_map(fn($d) => (new DateTime($d))->format("Y-m-d H:i"), $dates);

    header('Content-Type: application/json');
    echo json_encode($slots);
    exit;
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
// Requête AJAX dynamique avec dates de semaine
if (isset($_GET['ajax_get_slots']) && isset($_GET['medecin_id'])) {
    $medecinId = intval($_GET['medecin_id']);
    $start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end = isset($_GET['end_date']) ? $_GET['end_date'] . ' 23:59:59' : date('Y-m-d 23:59:59');

    $stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE doctor_id = :medecinId AND date BETWEEN :start AND :end");
    $stmt->execute(['medecinId' => $medecinId, 'start' => $start, 'end' => $end]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $slots = array_map(fn($d) => (new DateTime($d))->format("Y-m-d H:i"), $dates);

    header('Content-Type: application/json');
    echo json_encode($slots);
    exit;
}
$sql_name = "SELECT username, speciality
                FROM users
                WHERE users_id = :user_id";
$stmt_name = $pdo->prepare($sql_name);
$stmt_name->execute(['user_id' => $user_id]);
$name = $stmt_name->fetch(PDO::FETCH_ASSOC);


$message = '';
if (isset($_GET['fail'])) {
    switch ((int) $_GET['fail']) {
        case 1:
            $message = "Le numéro de téléphone n'est pas valide ❌";
            break;
        case 2:
            $message = "Le numéro de sécurité sociale n'est pas valide ❌";
            break;
        case 3:
            $message = "Le créneau est déjà réservé ❌";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesIcons.css" />
    <link rel="stylesheet" href="./css/stylesCalendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body data-message="<?= htmlspecialchars($message) ?>">

    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="dashboard.php"> <i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
                <li><a href="#" class="inactive"><i class="fas fa-plus"></i> Ajouter un rendez-vous</a></li>
                <li><a href="edit_account_doc.php"> <i class="fas fa-edit"></i>Modifier mes informations</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
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
            <form action="add_rdv_doc.php" method="POST" class="index">
                <input type="hidden" id="users" value="<?php echo $_SESSION['users_id']; ?>">



                <label for="name"><strong>NOM :</strong></label>
                <input type="text" id="name" name="name" required>

                <label for="firstname"><strong>PRÉNOM :</strong></label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="tel" maxlength="10" minlength="10"><strong>TÉLÉPHONE :</strong></label>
                <input type="number" id="tel" name="tel" required>

                <label for="numsecu"><strong>NUMÉRO DE SÉCURITÉ SOCIALE :</strong></label>
                <input type="number" id="numsecu" name="numsecu" maxlength="15" minlength="15" required>

                <div id="calendar-container">
                    <div class="week-controls">
                        <button type="button" id="prev-week">Semaine précédente</button>
                        <span id="current-week-label"></span>
                        <button type="button" id="next-week">Semaine suivante</button>
                    </div>
                    <div id="week-grid"></div>
                </div>
                <input type="hidden" name="date" id="hidden-date" required>

                <button class="btn" type="submit" class="login-btn">Ajouter</button>
            </form>
        </div>
    </main>
    <script src="./js/scriptCalendar.js"></script>
    <script src="./js/scriptMsg.js"></script>
</body>

</html>