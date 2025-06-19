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

// Vérifie session
if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

// Récupération des informations du médecin connecté
$stmt = $pdo->prepare("SELECT username, speciality FROM users WHERE users_id = :id");
$stmt->execute(['id' => $_SESSION['users_id']]);
$name = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération de l'ID du rdv
if (isset($_POST['rdv_id'])) {
    $rdv_id = $_POST['rdv_id'];
} elseif (isset($_GET['rdv_id'])) {
    $rdv_id = $_GET['rdv_id'];
} else {
    header("Location: dashboard.php");
    exit();
}

// Récupération des infos du rendez-vous
$stmt = $pdo->prepare("SELECT * FROM rdv2 WHERE rdv_id = :rdv_id");
$stmt->execute(['rdv_id' => $rdv_id]);
$rdv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rdv) {
    echo "Rendez-vous introuvable.";
    exit();
}



// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rdv_id'], $_POST['date'])) {
    $rdv_id = intval($_POST['rdv_id']);
    $newDate = $_POST['date'];

    $stmt = $pdo->prepare("UPDATE rdv2 SET date = :date WHERE rdv_id = :rdv_id");
    $stmt->execute(['date' => $newDate, 'rdv_id' => $rdv_id]);

    header("Location: dashboard.php?success=2");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier le rendez-vous</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesCalendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <aside>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
                <li><a href="demande_rdv_doc.php"><i class="fas fa-plus"></i> Ajouter un rendez-vous</a></li>
                <li><a href="edit_account_doc.php"> <i class="fas fa-edit"></i>Modifier mes informations</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
        <br>
        <p class="doctor_name">
            Connecté en tant que : <br> <?= htmlspecialchars($name['username']) ?>
            (<?= htmlspecialchars($name['speciality']) ?>)
        </p>
    </aside>

    <main>
        <form action="edit_rdv.php" method="POST">
            <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($rdv['rdv_id']) ?>">
            <input type="hidden" id="users" value="<?= htmlspecialchars($_SESSION['users_id']) ?>">

            <!-- Calendrier -->
            <label for="date"><strong>DATE ET HEURE DU RENDEZ-VOUS :</strong></label>
            <div id="calendar-container">
                <div class="week-controls">
                    <div id="week-year"></div>
                    <button type="button" id="prev-week">Semaine précédente</button>
                    <span id="current-week-label"></span>
                    <button type="button" id="next-week">Semaine suivante</button>
                </div>
                <div id="week-grid"></div>
            </div>
            <input type="hidden" name="date" id="hidden-date" value="<?= htmlspecialchars($rdv['date']) ?>" required>


            <div>
                <button type="submit" onclick="return confirm('Confirmer la modification du rendez-vous ?');">
                    Enregistrer les modifications
                </button>
                <a href="dashboard.php">
                    Annuler
                </a>
            </div>


            <table>
                <tr>
                    <th>Nom</th>
                    <td><?= htmlspecialchars($rdv['patient_nom']) ?></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><?= htmlspecialchars($rdv['patient_prenom']) ?></td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td><?= htmlspecialchars($rdv['patient_tel']) ?></td>
                </tr>
                <tr>
                    <th>Numéro de sécurité sociale</th>
                    <td><?= htmlspecialchars($rdv['num_secu']) ?></td>
                </tr>
            </table>
        </form>
    </main>

    <script src="./js/scriptCalendarEv.js"></script>
    <script src="./js/scriptMsg.js"></script>

</body>

</html>