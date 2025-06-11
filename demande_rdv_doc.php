<?php
session_start();
require_once ('./db/connection.php'); 

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['users_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['users_id'];

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

$sql = "SELECT r.rdv_id, r.patient_nom, r.patient_prenom, r.patient_tel, r.num_secu, r.date
    FROM rdv2 r
    WHERE r.doctor_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_name = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_name = $pdo->prepare($sql_name);
$stmt_name->execute(['user_id' => $user_id]);
$name = $stmt_name->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mes rendez-vous</title>
<link rel="stylesheet" href="styles.css" />

<!-- FullCalendar CSS et JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<style>
  /* Style simple pour la modale */
  #rdvModal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 100px;
    left: 0; top: 0; width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
  }
  #rdvModalContent {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
  }
  #rdvModalClose {
    float: right;
    cursor: pointer;
    font-weight: bold;
    font-size: 18px;
  }
  button.delete-btn {
    background-color: red;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
  }
</style>

<script>
const rdvData = <?php
    $events = [];
    foreach ($rendezvous as $rdv) {
        $events[] = [
            'title' => htmlspecialchars($rdv['patient_nom'] . ' ' . $rdv['patient_prenom']),
            'start' => $rdv['date'],
            'extendedProps' => [
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
<body>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<script>alert("Le rendez-vous a bien été ajouté ✅");</script>
<?php endif; ?>

<aside>
    <nav>
        <ul>
            <li><a href="#" class="inactive">Mes rendez-vous</a></li>
            <li><a href="demande_rdv_doc.php">Ajouter un rendez-vous</a></li>
            <li><a href="logout.php">Se déconnecter</a></li>
        </ul>
    </nav>
    <br>
    <p class="doctor_name">Connecté en tant que : <?= htmlspecialchars($name['username']) ?></p>
</aside>

<main>
    <?php if (empty($rendezvous)): ?>
        <p>Vous n'avez aucun rendez-vous.</p>
    <?php else: ?>
        <div id='calendar'></div>
    <?php endif; ?>
</main>

<!-- Modale pour afficher infos RDV et bouton supprimer -->
<div id="rdvModal">
  <div id="rdvModalContent">
    <span id="rdvModalClose">&times;</span>
    <h3>Détails du rendez-vous</h3>
    <p><strong>Nom :</strong> <span id="modalNom"></span></p>
    <p><strong>Téléphone :</strong> <span id="modalTel"></span></p>
    <p><strong>Numéro de sécu :</strong> <span id="modalSecu"></span></p>
    <p><strong>Date :</strong> <span id="modalDate"></span></p>

    <form id="deleteForm" method="POST" action="delete_rdv.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?');">
      <input type="hidden" name="rdv_id" id="modalRdvId" value="">
      <button type="submit" class="delete-btn">Supprimer ce rendez-vous</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('rdvModal');
    const modalClose = document.getElementById('rdvModalClose');
    const modalNom = document.getElementById('modalNom');
    const modalTel = document.getElementById('modalTel');
    const modalSecu = document.getElementById('modalSecu');
    const modalDate = document.getElementById('modalDate');
    const modalRdvId = document.getElementById('modalRdvId');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        height: 'auto',
        events: rdvData,
        eventDidMount: function(info) {
            info.el.setAttribute('title', `Téléphone: ${info.event.extendedProps.tel}\nN° Sécu: ${info.event.extendedProps.num_secu}`);
        },
        eventClick: function(info) {
            // Affiche modale avec détails
            modalNom.textContent = info.event.title;
            modalTel.textContent = info.event.extendedProps.tel;
            modalSecu.textContent = info.event.extendedProps.num_secu;
            modalDate.textContent = info.event.startStr;
            modalRdvId.value = info.event.id;
            modal.style.display = "block";
        }
    });

    calendar.render();

    // Fermeture modale
    modalClose.onclick = function() {
        modal.style.display = "none";
    };
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});
</script>
</body>
</html>
