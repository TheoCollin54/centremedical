<?php
session_start();
require_once('./db/connection.php');

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

$user_id = $_SESSION['users_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer le nom d'utilisateur pour redirection si admin
$sql_user = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($user && $user['username'] === 'admin') {
    header("Location: dashboard_admin.php");
    exit();
}

// Récupérer les rendez-vous du médecin connecté
$sql = "SELECT r.rdv_id, r.patient_nom, r.patient_prenom, r.patient_tel, r.num_secu, r.date
    FROM rdv2 r
    WHERE r.doctor_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nom et spécialité pour affichage
$sql_name = "SELECT username, speciality FROM users WHERE users_id = :user_id";
$stmt_name = $pdo->prepare($sql_name);
$stmt_name->execute(['user_id' => $user_id]);
$name = $stmt_name->fetch(PDO::FETCH_ASSOC);

// Message de succès
$message = '';
if (isset($_GET['success'])) {
    switch ((int) $_GET['success']) {
        case 1:
            $message = "✅ Le rendez-vous a bien été ajouté.";
            break;

        case 2:
            $message = "✅ Le rendez-vous a bien été modifié.";
            break;
        case 3:
            $message = "✅ Le rendez-vous a bien été supprimé.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mes rendez-vous</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="./css/stylesDashboard.css" />
    <link rel="stylesheet" href="./css/stylesIcons.css" />

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <!-- Logo -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        // Prépare les données des rendez-vous pour le JS
        const rdvData = <?php
        $events = [];
        foreach ($rendezvous as $rdv) {
            $events[] = [
                'title' => htmlspecialchars($rdv['patient_nom']),
                'start' => $rdv['date'],
                'extendedProps' => [
                    'prenom' => $rdv['patient_prenom'],
                    'nom' => $rdv['patient_nom'],
                    'tel' => $rdv['patient_tel'],
                    'num_secu' => $rdv['num_secu']
                ],
                'id' => $rdv['rdv_id']
            ];
        }
        echo json_encode($events);
        ?>;
    </script>
</head>

<body data-message="<?= htmlspecialchars($message) ?>">
    <aside>
        <nav>
            <ul>
                <li><a href="#" class="inactive"><i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
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
        <?php if (empty($rendezvous)): ?>
            <p>Vous n'avez aucun rendez-vous.</p>
        <?php else: ?>
            <div id='calendar'></div>
        <?php endif; ?>
    </main>

    <!-- Modale pour afficher infos RDV et boutons Modifier / Supprimer -->
    <div id="rdvModal">
        <div id="rdvModalContent">
            <span id="rdvModalClose">&times;</span>
            <h3>Détails du rendez-vous</h3>
            <p>Nom : <span id="modalNom"></span></p>
            <p>Prénom : <span id="modalPrenom"></span></p>
            <p>Téléphone : <span id="modalTel"></span></p>
            <p>Numéro de sécurité sociale : <span id="modalSecu"></span></p>
            <p>Date : <span id="modalDate"></span></p>

            <form id="editForm" method="POST" action="edit_rdv.php">
                <input type="hidden" name="rdv_id" id="modalRdvIdEdit" value="">
                <button type="submit">Modifier</button>
            </form>
            <br>
            <form id="deleteForm" method="POST" action="delete_rdv.php"
                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?');">
                <input type="hidden" name="rdv_id" id="modalRdvIdDel" value="">
                <button type="submit" class="delete-btn">Supprimer</button>
            </form>
        </div>
    </div>

    <!-- Regroupement de tous les scripts JS ici, à la fin du body -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="./js/scriptMsg.js"></script>
    <script src="./js/scriptDashboard.js"></script>
</body>

</html>