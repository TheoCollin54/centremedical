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

$stmt = $pdo->query("SELECT users_id, username, speciality, adress FROM users WHERE username != 'admin'");
$medecins = $stmt->fetchAll();

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
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesCalendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="no-scroll" data-message="<?= htmlspecialchars($message) ?>">
    <aside>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <div class="container">
            <form action="add_rdv.php" method="POST" class="index">
                <label for="users_id">Choisissez votre médecin :</label>
                <select name="users_id" id="users" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($medecins as $user): ?>
                        <option value="<?= htmlspecialchars($user['users_id']) ?>">
                            <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['speciality']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Zone d'affichage de l'adresse -->
                <div id="doctor-address">
                    <!-- Adresse du médecin sélectionné apparaîtra ici -->
                </div>

                <label for="name"><strong>NOM :</strong></label>
                <input type="text" id="name" name="name" required>

                <label for="firstname"><strong>PRÉNOM :</strong></label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="tel"><strong>TÉLÉPHONE :</strong></label>
                <input type="number" id="tel" name="tel" maxlength="10" minlength="10" required>

                <label for="numsecu"><strong>NUMÉRO DE SÉCURITÉ SOCIALE :</strong></label>
                <input type="number" id="numsecu" name="numsecu" maxlength="15" minlength="15" required>

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
                <input type="hidden" name="date" id="hidden-date" required>

                <button class="btn" type="submit">Ajouter</button>
            </form>
        </div>
    </main>

    <script src="./js/scriptCalendar.js"></script>
    <script src="./js/scriptMsg"></script>
    <script>
        const medecins = <?= json_encode($medecins, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="./js/scriptInfoDoc.js"></script>
</body>

</html>