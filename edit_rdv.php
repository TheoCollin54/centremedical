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

// Fonction de nettoyage simple des données
function clean_input($data) {
    return htmlspecialchars(trim($data));
}

// Si formulaire soumis (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rdv_id = $_POST['rdv_id'] ?? null;
    $patient_nom = clean_input($_POST['patient_nom'] ?? '');
    $patient_prenom = clean_input($_POST['patient_prenom'] ?? '');
    $patient_tel = clean_input($_POST['patient_tel'] ?? '');
    $num_secu = clean_input($_POST['num_secu'] ?? '');
    $date = $_POST['date'] ?? '';

    if (!$rdv_id) {
        die("ID du rendez-vous manquant.");
    }

    // Vérifier que le rdv appartient bien au médecin connecté
    $sql_check = "SELECT rdv_id FROM rdv2 WHERE rdv_id = :rdv_id AND doctor_id = :user_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['rdv_id' => $rdv_id, 'user_id' => $user_id]);
    $exists = $stmt_check->fetchColumn();

    if (!$exists) {
        die("Ce rendez-vous n'existe pas ou vous n'avez pas les droits pour le modifier.");
    }

    // Validation basique (à compléter selon besoins)
    if (empty($patient_nom) || empty($patient_prenom) || empty($date)) {
        $error = "Les champs Nom, Prénom et Date sont obligatoires.";
    } else {
        // Met à jour le RDV
        $sql_update = "UPDATE rdv2 
                       SET patient_nom = :patient_nom,
                           patient_prenom = :patient_prenom,
                           patient_tel = :patient_tel,
                           num_secu = :num_secu,
                           date = :date
                       WHERE rdv_id = :rdv_id AND doctor_id = :user_id";

        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            'patient_nom' => $patient_nom,
            'patient_prenom' => $patient_prenom,
            'patient_tel' => $patient_tel,
            'num_secu' => $num_secu,
            'date' => $date,
            'rdv_id' => $rdv_id,
            'user_id' => $user_id
        ]);

        header("Location: mes_rdv.php?success=2"); // Redirige vers liste avec succès modification
        exit();
    }
} else {
    // GET : affichage formulaire avec données préremplies

    $rdv_id = $_GET['rdv_id'] ?? null;

    if (!$rdv_id) {
        die("ID du rendez-vous manquant.");
    }

    // Récupère données rdv uniquement si médecin connecté est propriétaire
    $sql_rdv = "SELECT patient_nom, patient_prenom, patient_tel, num_secu, date 
                FROM rdv2 
                WHERE rdv_id = :rdv_id AND doctor_id = :user_id";
    $stmt_rdv = $pdo->prepare($sql_rdv);
    $stmt_rdv->execute(['rdv_id' => $rdv_id, 'user_id' => $user_id]);
    $rdv = $stmt_rdv->fetch(PDO::FETCH_ASSOC);

    if (!$rdv) {
        die("Rendez-vous non trouvé ou accès refusé.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Modifier un rendez-vous</title>
<link rel="stylesheet" href="styles.css" />
</head>
<body>
<a href="mes_rdv.php">&larr; Retour à mes rendez-vous</a>

<h2>Modifier le rendez-vous</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="edit_rdv.php">
    <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($rdv_id) ?>" />

    <label for="patient_nom">Nom du patient *</label><br>
    <input type="text" id="patient_nom" name="patient_nom" required value="<?= htmlspecialchars($rdv['patient_nom'] ?? '') ?>" /><br><br>

    <label for="patient_prenom">Prénom du patient *</label><br>
    <input type="text" id="patient_prenom" name="patient_prenom" required value="<?= htmlspecialchars($rdv['patient_prenom'] ?? '') ?>" /><br><br>

    <label for="patient_tel">Téléphone</label><br>
    <input type="tel" id="patient_tel" name="patient_tel" value="<?= htmlspecialchars($rdv['patient_tel'] ?? '') ?>" /><br><br>

    <label for="num_secu">Numéro de sécurité sociale</label><br>
    <input type="text" id="num_secu" name="num_secu" value="<?= htmlspecialchars($rdv['num_secu'] ?? '') ?>" /><br><br>

    <label for="date">Date et heure du rendez-vous *</label><br>
    <input type="datetime-local" id="date" name="date" required
           value="<?= !empty($rdv['date']) ? date('Y-m-d\TH:i', strtotime($rdv['date'])) : '' ?>" /><br><br>

    <button type="submit">Enregistrer les modifications</button>
</form>

</body>
</html>