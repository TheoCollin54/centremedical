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

// Gestion AJAX pour les créneaux indisponibles
if (isset($_GET['ajax_get_slots']) && $_GET['ajax_get_slots'] == 1) {
    header('Content-Type: application/json');

    $doctor_id = $_GET['doctor_id'] ?? null;
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    if (!$doctor_id || !$start_date || !$end_date) {
        echo json_encode(['error' => 'Paramètres manquants']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE doctor_id = :doctor_id AND date BETWEEN :start_date AND :end_date");
    $stmt->execute([
        'doctor_id' => $doctor_id,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($results);
    exit();
}

// Vérifie session
if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

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

    header("Location: edit_rdv.php?rdv_id=$rdv_id&success=1");
    exit();
}

$message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "✅ Le rendez-vous a été mis à jour.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier le rendez-vous</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <style>
        .highlighted-date {
            margin: 10px 0;
            font-size: 1.1em;
            font-weight: bold;
            color: #0a4f91;
        }

        #calendar-container {
            margin-bottom: 20px;
        }

        table {
            margin-top: 20px;
        }
    </style>
</head>

<body data-message="<?= htmlspecialchars($message) ?>">
    <aside>
        <nav>
            <ul>
                <li><a href="dashboard.php">Mes rendez-vous</a></li>
                <li><a href="demande_rdv_doc.php">Ajouter un rendez-vous</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <form action="edit_rdv.php" method="POST">
            <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($rdv['rdv_id']) ?>">
            <input type="hidden" id="users" value="<?= htmlspecialchars($_SESSION['users_id']) ?>">

            <!-- Calendrier -->
            <label for="date"><strong>DATE ET HEURE DU RENDEZ-VOUS :</strong></label>
            <div id="calendar-container">
                <div class="week-controls">
                    <button type="button" id="prev-week">Semaine précédente</button>
                    <span id="current-week-label"></span>
                    <button type="button" id="next-week">Semaine suivante</button>
                </div>
                <div id="week-grid"></div>
            </div>
            <input type="hidden" name="date" id="hidden-date" value="<?= htmlspecialchars($rdv['date']) ?>" required>



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
                <tr>
                    <td colspan="2">
                        <button type="submit"
                            onclick="return confirm('Confirmer la modification du rendez-vous ?');">Enregistrer les
                            modifications</button>
                        <a href="dashboard.php" style="margin-left:10px;">Annuler</a>
                    </td>
                </tr>
            </table>
        </form>
        <h6>
            📅 Date actuelle du rendez-vous : <span
                id="current-selected-date"><?= htmlspecialchars(date("d/m/Y à H:i", strtotime($rdv['date']))) ?></span>
        </h6>
    </main>

    <script src="./js/scriptCalendar.js"></script>
    <script src="./js/scriptMsg.js"></script>

</body>

</html>