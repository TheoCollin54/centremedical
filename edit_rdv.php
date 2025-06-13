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

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

// Si on a reçu un rdv_id depuis le bouton "Modifier"
if (isset($_POST['rdv_id'])) {
    $rdv_id = $_POST['rdv_id'];
} elseif (isset($_GET['rdv_id'])) {
    $rdv_id = $_GET['rdv_id'];
} else {
    header("Location: dashboard.php");
    exit();
}

// Récupérer les infos du rendez-vous
$stmt = $pdo->prepare("SELECT * FROM rdv2 WHERE rdv_id = :rdv_id");
$stmt->execute(['rdv_id' => $rdv_id]);
$rdv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rdv) {
    echo "Rendez-vous introuvable.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rdv_id'], $_POST['date'])) {
    $rdv_id = intval($_POST['rdv_id']);
    $newDate = $_POST['date'];

    $stmt = $pdo->prepare("UPDATE rdv2 SET date = :date WHERE rdv_id = :rdv_id");
    $stmt->execute(['date' => $newDate, 'rdv_id' => $rdv_id]);

    // Redirection après modification pour éviter de resoumettre le formulaire au refresh
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
    <link rel="stylesheet" href="styles.css" />
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body data-message="<?= htmlspecialchars($message) ?>">
    <aside> <!-- Sidebar -->
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
            <table>
                <tr>
                    <th>Date du rendez-vous</th>
                    <td>
                        <input type="text" id="date" name="date" value="<?= htmlspecialchars($rdv['date']) ?>">
                    </td>
                </tr>
                <tr>
                    <th>Nom</th>
                    <th>
                        <span>
                            <?= htmlspecialchars($rdv['patient_nom']) ?>
                        </span>
                    </th>
                </tr>
                <th>Prénom</th>
                <th>
                    <span>
                        <?= htmlspecialchars($rdv['patient_prenom']) ?>
                    </span>
                </th>
                <tr>
                    <th>Téléphone</th>
                    <th>
                        <span>
                            <?= htmlspecialchars($rdv['patient_tel']) ?>
                        </span>
                    </th>
                </tr>
                <tr>
                    <th>Numéro de sécurité sociale</th>
                    <th>
                        <span>
                            <?= htmlspecialchars($rdv['num_secu']) ?>
                        </span>
                    </th>
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
    </main>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="./js/scriptCalendar.js"></script>
    <script src="./js/scriptMsg.js"></script>
</body>

</html>